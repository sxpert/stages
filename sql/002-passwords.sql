--******************************************************************************
--
-- Gestion des offres de stage de M2 en Astro
-- (c) Raphaël Jacquot 2011
-- Fichier sous licence GPL-3
--
--******************************************************************************

--
-- Modification de la gestion des utilisateurs.
-- on ne stocke plus le salt séparément, mais on l'extrait du mot de passe chiffré 
-- vu qu'il est stocké a la fin (mais pourquoi j'ai pas fait comme ca dès le début ?)
--

\c stages stagesadm
\set VERBOSITY terse

--
-- changer la vue pour ajouter le login, pas utile de le cacher
--
drop view if exists users_view;
create or replace view users_view as select id, f_name, l_name, login, email, phone, statut, id_laboratoire, m2_admin, super from users;
grant select on users_view to stagesweb;

--
-- modifier la table users au besoin
-- 

do $$
    begin
        -- vérifier si la table a un champ email_token
	perform pa.attname from pg_type as pt, pg_attribute as pa where pt.typrelid = pa.attrelid and pt.typname='users' and pa.attname='email_token';
	if found then
	    raise notice 'table has email_token field';
	else
	    raise notice 'need to add email_token field';
	    alter table users add column email_token text;
	end if;
    end;
$$;

drop function if exists extract_salt (text);
create or replace function extract_salt (l_password text) returns bytea as $$
    declare
        t_pos      integer;
        t_hashtype text;
	t_length   integer;
	t_salt     text;
    begin
        t_pos = position('}' in l_password);
	if t_pos = 0 then
	    raise exception 'can''t find hastype';
	end if;
        t_hashtype = substring(l_password for t_pos);
        if t_hashtype = '{SSHA512}' then
            t_length = 64;
        end if;
	if t_length is null then
	    raise exception 'unknown hashtype %', t_hashtype;
	end if;
        select substring(decode( substring(l_password from (t_pos + 1) ), 'base64') from (t_length + 1)) into t_salt;
	return t_salt;
    end;
$$ language plpgsql;

--
-- génère un nouveau token d'email
-- 
create or replace function gen_email_token (t_email text) returns text as $$
    declare
        t_salt bytea;
	t_token text;
    begin
	t_salt := generate_salt();
	t_token := hash_email (t_email, t_salt);
	return t_token;
    end;
$$ language plpgsql;

--
-- no need to store salt anymore
--

create or replace function user_add(t_fname text, t_lname text, 
       	                 t_email text, t_phone text,
			 t_statut bigint, t_id_labo bigint, 
			 t_login text, t_password text,
			 t_ipaddr inet) returns record as $$
        declare
		t_salt	      bytea;
		t_passwd      text;
		t_userid      bigint;
		t_email_token text;
		t_rec	      record;
        begin
		t_userid := 0;
		-- check if we already have an account with this login
		perform id from users where login=t_login;
		if not found then
			t_salt := generate_salt();
			t_passwd := hash_password ( t_password, t_salt);
			t_email_token := gen_email_token (t_email);
			-- insert the new account
		        insert into users (f_name, l_name, email, phone, statut, id_laboratoire, login, passwd, email_token)
			       values (t_fname, t_lname, t_email, t_phone, t_statut, t_id_labo, t_login, t_passwd, t_email_token)
			       returning id into t_userid;
			perform append_log_login('register', t_login, 'User successfully registered',t_ipaddr);
		else
			-- should log ip address
			perform append_log_login('register',t_login,'Attempt to register user multiple times',t_ipaddr);
		end if;
		t_rec := ( t_userid, t_email_token);
		return t_rec;
	end;
$$ language plpgsql security definer;

--
-- modification pour utiliser extract_salt
--
drop function if exists user_login (text, text, inet);
create or replace function user_login(t_login text, t_password text, t_ipaddr inet) returns record as $$
        declare
		t_account	record;
		t_temp  	bytea;
		t_salt		bytea;
		t_passwd	text;
		t_lf		smallint;
		t_rec		record;
        begin
		-- grab the account info for the login
		raise notice 'user login % % %',t_login, t_password, t_ipaddr;
		select * into t_account from users where login=t_login;
		if found then
		   	if (t_account.login_fails >= 3) then
				perform append_log_login ('login',t_login,'User attempted to login on locked account',t_ipaddr);
			        t_rec := ( -1::bigint, 0::bigint );
				return t_rec;
			end if;
			if (t_account.account_valid = false) then
				perform append_log_login ('login',t_login,'User attempted to login on not yet validated account',t_ipaddr);
			        t_rec := ( -2::bigint, 0::bigint );
			        return t_rec;
			end if;
			t_salt := extract_salt (t_account.passwd);
			t_passwd := hash_password ( t_password, t_salt ); 
			if (t_passwd = t_account.passwd) then
				perform append_log_login ('login',t_login,'User successfully logged in',t_ipaddr);
			   	update users set login_fails = 0 where id=t_account.id;
			        t_rec := ( t_account.id, t_account.m2_admin );
				return t_rec;
			else
				perform append_log_login ('login',t_login,'Login failed, invalid password',t_ipaddr);
				t_lf := t_account.login_fails + 1;
				update users set login_fails = t_lf where id=t_account.id;
			end if;
		else
			perform append_log_login ('login',t_login,'Login failed, user unknown',t_ipaddr);
		end if;
		t_rec := ( 0::bigint, 0::bigint );
		return t_rec;
	end;
$$ language plpgsql security definer;

--
-- user validation
--
create or replace function user_validate_account(t_login text, t_password text, t_hash text, t_ipaddr inet) returns bigint as $$
       declare
               t_account       record;
	       t_passwd	       text;
       begin
               -- grab the account info
	       select * into t_account from users where login=t_login;
	       if found then
	       	       if (t_account.login_fails >= 3) then
		       	       perform append_log_login ('account_validation',t_login,'User attempted validating locked account',t_ipaddr); 	
		               return -1;
		       end if;
		       t_passwd := hash_password ( t_password, extract_salt (t_account.passwd));
		       if (t_passwd = t_account.passwd) then
		               -- check if t_hash est ok
			       
			       if (t_account.email_token = t_hash) then
			       	       	update users set login_fails=0, account_valid=true where id = t_account.id;
					perform append_log_login ('account_validation',t_login,'Validation succeeded',t_ipaddr);

			               return t_account.id;
				else
	                                perform append_log_login ('account_validation',t_login,'Validation failed, invalid hash',t_ipaddr);
			       end if;
			else
				perform append_log_login ('account_validation',t_login,'Validation failed, invalid password',t_ipaddr);
			end if;
		        -- fail
		        update users set login_fails = (t_account.login_fails + 1) where id = t_account.id;
		else
			perform append_log_login ('account_validation',t_login,'Validation failed, user unknown',t_ipaddr);
		end if;  
	       return 0;
       end;
$$ language plpgsql security definer;

--
-- generates a new user email token if user requested one
--
create or replace function user_get_email_hash(t_login text, t_password text, t_ipaddr inet) returns record as $$
        declare
		t_account	record;
		t_passwd	text;
		t_email_token	text;
		t_rec		record;
        begin
	        select * into t_account from users where login=t_login;
		if found then
		        if (t_account.login_fails >= 3) then
			        t_rec := (-1::bigint, null::text, null::text);
				return t_rec;
                        end if;
			t_passwd := hash_password ( t_password, extract_salt (t_account.passwd));
			if (t_passwd = t_account.passwd) then
			        perform append_log_login ('validation_email',t_login,'User requested validation email sent',t_ipaddr);
				-- generate new token
				t_email_token := gen_email_token (t_account.email);
				-- store new token
				update users set email_token = t_email_token where id=t_account.id;
				t_rec := ( t_account.id, t_account.email, t_email_token);
				return t_rec;
			end if;
			-- fail
			perform append_log_login ('validation_email',t_login,'User failed password check',t_ipaddr);
			update users set login_fails = (t_account.login_fails + 1) where id = t_account.id;		
		else
			perform append_log_login ('validation_email',t_login,'User does not exist',t_ipaddr);
		end if;
		t_rec := (0::bigint, null::text, null::text);
		return t_rec;
        end;
$$ language plpgsql security definer;


--
-- test procedure !
--
--

do $$
    declare 
        t_password text;
        t_salt     bytea;
	t_login    record;
	t_user 	   record;
	t_user_id  bigint;
	t_token    record;
    begin	   
    	-- salt extraction test
	raise notice 'test salt extraction';
        select passwd into t_password from users where login='sxpert';
	raise notice 'password %', t_password;
	select extract_salt (t_password) into t_salt;
	raise notice 'salt %', t_salt;
	raise notice 'stored %', (select salt from users where login='sxpert');

	-- test user add
	delete from users where login='_user';
	raise notice 'creating user';
	select * into t_user from user_add('test', 'user', 'test.user@example.com', '+33 1 23 45 67 89', 1, 7293,  '_user', '_pass', '127.0.0.1') as ss (id bigint, email_token text);
	raise notice 'user id % email_token %', t_user.id,t_user.email_token;
	
	-- test account validation	
	select user_validate_account('_user', '_pass', t_user.email_token, '127.0.0.1') into t_user_id;
	raise notice 'user account validated %', t_user_id;
	
	-- set m2_admin
	update users set m2_admin=1 where id=t_user.id;

	-- test user login
	select * into t_login from user_login ('_user', '_pass', '127.0.0.1') as ss (id bigint, m2_admin bigint);
	raise notice 'login id % m2 %', t_login.id, t_login.m2_admin;

	-- test generating new email token
	select * into t_token from user_get_email_hash('_user', '_pass', '127.0.0.1') as ss (id bigint, email text, token text);
	raise notice 'retrieve new token id % email % token %', t_token.id, t_token.email, t_token.token;
	
	delete from users where login='_user';
    end;
$$;

\set VERBOSITY default