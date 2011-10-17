--
-- script de base de données pour l'appli de gestion des stages / thèses.
-- nécessite l'extension pgcrypto, disponible dans le package postgresql-contrib
--

-- premiere partie executée en tant que super-utilisateur du serveur de bdd

--
-- nettoyage
--
drop database if exists stcoll;
drop role if exists stcolladm;
drop role if exists stcollweb;

--
-- création de la base de données
--
create role stcolladm with login encrypted password 'testadm';
create role stcollweb with login noinherit encrypted password 'test';
create database stcoll with owner stcolladm;

\c stcoll 

create extension pgcrypto;

\c stcoll stcolladm

create sequence seq_version minvalue 0;

--
-- table des types de formations
--
create sequence seq__formation__id;
create table formation (
       id    	       bigint not null,
       description     text
);
alter sequence seq__formation__id owned by formation.id;
alter table formation alter column id set default nextval('seq__formation__id');
create unique index pk__formation__id on formation ( id );
alter table formation add primary key using index pk__formation__id;
create unique index idx__formation__desc on formation(description);
alter table formation add unique using index idx__formation__desc;

grant usage on sequence seq__formation__id to stcollweb;

insert into formation (description) values ('École Doctorale de Physique');

--
-- table des types d'offres
--
create sequence seq__type_offre__id;
create table type_offre (
       id    	       bigint not null,
       code	       char(2) not null,
       description     text not null,
       denom_prop      text not null,
       denom_dir       text not null,
       has_year        boolean

);
alter sequence seq__type_offre__id owned by type_offre.id;
alter table type_offre alter column id set default nextval('seq__type_offre__id');
create unique index pk__type_offre__id on type_offre ( id );
alter table type_offre add primary key using index pk__type_offre__id;
create unique index idx__type_offre__desc on type_offre ( description );
alter table type_offre add unique using index idx__type_offre__desc;

grant usage on sequence seq__type_offre__id to stcollweb;
grant select on type_offre to stcollweb;

--insert into type_offre (code, description, denom_prop, denom_dir, has_year) values 
--       ('TH', 'Thèse', 'Thèses', 'Directeur de Thèse', false);
insert into type_offre (code, description, denom_prop, denom_dir, has_year) values 
       ('MR', 'Master Recherche', 'Stages', 'Directeur de Stage', true);

--
-- table des laboratoires
--
-- description : nom long du laboratoire
-- from_value  : clé permettant de connaitre la provenance de l'utilisateur ( sha1sum(description)[1:8] )
--
create sequence seq__laboratoires__id;
create table laboratoires (
       id    		  bigint not null,
       sigle		  text not null,
       description	  text not null,
       from_value	  char (8) not null,
       post_addr      	  text,
       post_code      	  text,
       city	      	  text
);
alter sequence seq__laboratoires__id owned by laboratoires.id;
alter table laboratoires alter column id set default nextval('seq__laboratoires__id');
create unique index pk__laboratoires__id on laboratoires ( id );
alter table laboratoires add primary key using index pk__laboratoires__id;
create unique index idx__laboratoires__desc on laboratoires ( description );
alter table laboratoires add unique using index idx__laboratoires__desc;
create index idx__laboratoires__from_value on laboratoires ( from_value );

grant usage on sequence seq__laboratoires__id to stcollweb;
grant select on laboratoires to stcollweb;

--
-- trigger pour créer la from_value automatiquement
-- note: la valeur n'est calculée automatiquement qu'a l'insertion initiale. elle est conservée en cas de modifications
--

create function laboratoires_set_from_value() returns trigger as $$
       begin
		  NEW.from_value = substr(encode(digest(NEW.description, 'sha1'), 'hex'), 1, 8);
		  return NEW;
       end;
$$ language plpgsql;

create trigger trig_laboratoires_set_from_value before insert on laboratoires for each row execute procedure laboratoires_set_from_value ();

--
-- vue pour la liste des labos
--

create view liste_labos as select id as key, sigle || ' - ' || description as value from laboratoires;
grant select on liste_labos to stcollweb;

insert into laboratoires (sigle, description, post_addr, post_code, city) values 
       ('IPAG', 'Institut de Planétologie et d''Astrophysique de Grenoble', '414, rue de la Piscine', '38400', 'Saint Martin d''Hères');

--
-- table des M2
--

create sequence seq__m2__id;
create table m2 (
       id		bigint not null,
       description	text not null,
       from_value	char (8) not null
);
alter sequence seq__m2__id owned by m2.id;
alter table m2 alter column id set default nextval('seq__m2__id');
create unique index pk__m2__id on m2 ( id );
alter table m2 add primary key using index pk__m2__id;
create unique index idx__m2__desc on m2 ( description );
alter table m2 add unique using index idx__m2__desc;
create index idx__m2__from_value on m2 ( from_value );

grant usage on sequence seq__m2__id to stcollweb;
grant select on m2 to stcollweb;
 
--
-- trigger pour créer la from_value automatiquement
-- note: la valeur n'est calculée automatiquement qu'a l'insertion initiale. elle est conservée en cas de modifications
--

create function m2_set_from_value() returns trigger as $$
       begin
		  NEW.from_value = substr(encode(digest(NEW.description, 'sha1'), 'hex'), 1, 8);
		  return NEW;
       end;
$$ language plpgsql;

create trigger trig_m2_set_from_value before insert on m2 for each row execute procedure m2_set_from_value ();

insert into m2 (description) values ('M2 Université Joseph Fourrier');

--
-- table des catégories
-- (apparamment on peut avoir plusieurs catégories pour la meme offre)
--
create sequence seq__categories__id;
create table categories (
       id    		bigint not null,
       description	text
);
alter sequence seq__categories__id owned by categories.id;
alter table categories alter column id set default nextval('seq__categories__id');
create unique index pk__categories__id on categories ( id );
alter table categories add primary key using index pk__categories__id;
create unique index idx__categories__desc on categories ( description );
alter table categories add unique using index idx__categories__desc;

grant usage on sequence seq__categories__id to stcollweb;

--
-- Manager de projet
--
create sequence seq__users__id;
create table users (
       id    	      bigint not null,
       f_name	      text,
       l_name	      text,
       email	      text,
       phone	      text,
       id_laboratoire bigint not null,
       login	      text,
       passwd	      text,
       salt	      bytea,
       login_fails    smallint default 0,
       account_valid  boolean default false
);
alter sequence seq__users__id owned by users.id;
alter table users alter column id set default nextval('seq__users__id');
create unique index pk__users__id on users ( id );
alter table users add primary key using index pk__users__id;
create unique index idx__users__login on users ( login );
alter table users add unique using index idx__users__login;
alter table users add foreign key ( id_laboratoire ) references laboratoires ( id );

--
-- generates some salt for the password encryption functions
--
create function generate_salt() returns bytea as $$
        declare
		i      integer;
	        t_salt bytea;
	begin
       		t_salt := '';
		for i in 0..15 loop
		        t_salt := t_salt || set_byte('\x00', 0, cast(floor(random() * 256) as smallint));
		end loop;
		return t_salt;
        end;
$$ language plpgsql security definer;

--
-- hashes the given password with the given salt
--
create function hash_password(t_password text, t_salt bytea) returns text as $$
        declare
		t_temp  bytea;
	begin
		t_temp := cast(t_password as bytea) || t_salt;
		t_temp := digest(t_temp, 'SHA512') || t_salt;
		return '{SSHA512}' || encode(t_temp, 'base64');
	end;
$$ language plpgsql security definer;

--
-- hashes the email with the given salt
-- 
create or replace function hash_email(t_email text, t_salt bytea) returns text as $$
        declare
		t_emailh text;
        begin
		t_emailh := encode ( digest(cast(t_email as bytea) || t_salt, 'SHA512'), 'hex' );
		return substring (t_emailh from 1 for 16 );
	end;
$$ language plpgsql security definer;

--
-- creates a user account 
-- 
create function user_add(t_fname text, t_lname text, 
       	                 t_email text, t_phone text,
			 t_id_labo bigint, 
			 t_login text, t_password text) 
			 returns record as $$
        declare
		t_salt	  bytea;
		t_passwd  text;
		t_userid    bigint;
		t_emailhash text;
		t_rec	    record;
        begin
		t_userid := 0;
		-- check if we already have an account with this login
		perform id from users where login=t_login;
		if not found then
			t_salt := generate_salt();
			t_passwd := hash_password ( t_password, t_salt);
			t_emailhash := hash_email ( t_email, t_salt);
			-- insert the new account
		        insert into users (f_name, l_name, email, phone, id_laboratoire, 
			       login, passwd, salt)
			       values (t_fname, t_lname, t_email, t_phone, t_id_labo, 
			       t_login, t_passwd, t_salt)
			       returning id into t_userid;
			
		end if;
		t_rec := (t_userid, t_emailhash); 
		return t_rec;
	end;
$$ language plpgsql security definer;

--
-- check if login/password can log onto the system. returns
-- -1  is account is disabled
--  0  if login or password wrong
-- uid if login successful
-- account is locked out after 3 failures
--
create function user_login(t_login text, t_password text) returns bigint as $$
        declare
		t_account	record;
		t_temp  	bytea;
		t_passwd	text;
		t_lf		smallint;
        begin
		-- grab the account info for the login
		select * into t_account from users where login=t_login;
		if found then
		   	if (t_account.login_fails >= 3) then
			        return -1;
			end if;
			t_passwd := hash_password ( t_password, t_account.salt ); 
			if (t_passwd = t_account.passwd) then
			   	update users set login_fails = 0 where id=t_account.id;
			        return t_account.id;
			else
				t_lf := t_account.login_fails + 1;
				update users set login_fails = t_lf where id=t_account.id;
				return 0;
			end if;
		else
			return 0;
		end if;
	end;
$$ language plpgsql security definer;

--
-- validate account
-- 
--
create function user_validate_account(t_login text, t_password text, t_hash text) returns bigint as $$
       declare
               t_account       record;
	       t_passwd	       text;
	       t_emailh	       text;
       begin
               -- grab the account info
	       select * into t_account from users where login=t_login;
	       if found then
		       t_passwd := hash_password ( t_password, t_account.salt );
	       else
	               return 0;
	       end if;  
       end;
$$ language plpgsql security definer;

-- 
-- financeurs
--
create sequence seq__financeurs__id;
create table financeurs (
       id    		bigint not null,
       description	text
);
alter sequence seq__financeurs__id owned by financeurs.id;
alter table financeurs alter column id set default nextval('seq__financeurs__id');
create unique index pk__financeurs__id on financeurs ( id );
alter table financeurs add primary key using index pk__financeurs__id;
create unique index idx__financeurs__desc on financeurs ( description );
alter table financeurs add unique using index idx__financeurs__desc;

grant usage on sequence seq__financeurs__id to stcollweb;

--
-- table des offres
--
create sequence seq__offres__id;
create table offres (
       id		bigint not null,
       id_formation	bigint not null,
       id_type_offre	bigint not null,
       id_m2		bigint not null,
       year_value	integer,		
       sujet		text,
       short_desc	text,
       description	text,
       project_url	text,
       id_project_mgr	bigint not null,
       is_financed	boolean not null,
       id_financeur	bigint,
       commentaire	text,
       start_date	date not null,
       create_date	timestamp,
       last_update	timestamp
);
alter sequence seq__offres__id owned by offres.id;
alter table offres alter column id set default nextval('seq__offres__id');
create unique index pk__offres__id on offres ( id );
alter table offres add primary key using index pk__offres__id;
alter table offres add foreign key ( id_formation ) references formation ( id );
alter table offres add foreign key ( id_type_offre ) references type_offre ( id );
alter table offres add foreign key ( id_m2 ) references m2 ( id );
alter table offres add foreign key ( id_project_mgr ) references users ( id );
alter table offres add foreign key ( id_financeur ) references financeurs ( id );

grant usage on sequence seq__offres__id to stcollweb;
 
--
-- table de liaison offres <-> categories (n to n)
--
create table offres_categories (
       id_offre		       bigint not null,
       id_categorie	       bigint not null
);
create unique index pk__offres_categories on offres_categories ( id_offre, id_categorie );
alter table offres_categories add primary key using index pk__offres_categories;
alter table offres_categories add foreign key ( id_offre ) references offres ( id );
alter table offres_categories add foreign key ( id_categorie ) references categories ( id );
