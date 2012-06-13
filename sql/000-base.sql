--******************************************************************************
--
-- Gestion des offres de stage de M2 en Astro
-- (c) Raphaël Jacquot 2011
-- Fichier sous licence GPL-3
--
--*****************************************************************************/

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
-- sections cnrs
-- 
create table sections_cnrs (
       id    		   bigint not null,
       description	   text,
       primary key (id)     
);

--
-- table des laboratoires
-- id          : numéro d'UMR
-- description : nom long du laboratoire
-- from_value  : clé permettant de connaitre la provenance de l'utilisateur ( sha1sum(description)[1:8] )
--
create table laboratoires (
       type_unite	  text not null,
       id    		  bigint not null,
       id_section	  bigint references sections_cnrs(id),
       sigle		  text not null,
       description	  text not null,
       from_value	  char (8) not null,
       post_addr      	  text,
       post_code      	  text,
       city	      	  text
);
create unique index pk__laboratoires__id on laboratoires ( id );
alter table laboratoires add primary key using index pk__laboratoires__id;
create index idx__laboratoires__from_value on laboratoires ( from_value );

grant select on laboratoires to stcollweb;

--
-- trigger pour créer la from_value automatiquement
-- note: la valeur n'est calculée automatiquement qu'a l'insertion initiale. elle est conservée en cas de modifications
--

create function laboratoires_set_from_value() returns trigger as $$
       begin
		  NEW.from_value = substr(encode(digest(NEW.description, 'sha512'), 'hex'), 1, 8);
		  return NEW;
       end;
$$ language plpgsql;

create trigger trig_laboratoires_set_from_value before insert on laboratoires for each row execute procedure laboratoires_set_from_value ();

--
-- vue pour la liste des labos
--

create view liste_labos as select id as key, 
       case when ((sigle is not null) and (char_length(sigle)>0)) then sigle || ' - ' else '' end || description as value from laboratoires
       order by value;
grant select on liste_labos to stcollweb;

create view liste_villes as select distinct city as key, city as value from laboratoires order by city;
grant select on liste_villes to stcollweb;

--
-- table des M2
--

create sequence seq__m2__id;
create table m2 (
       id		bigint not null,
       short_desc	text not null,
       description	text not null,
       ville		text not null,
       from_value	char (8) not null,
       url_logo		text
);
alter sequence seq__m2__id owned by m2.id;
alter table m2 alter column id set default nextval('seq__m2__id');
create unique index pk__m2__id on m2 ( id );
alter table m2 add primary key using index pk__m2__id;
create index idx__m2__from_value on m2 ( from_value );

grant usage on sequence seq__m2__id to stcollweb;
grant select on m2 to stcollweb;
 
--
-- trigger pour créer la from_value automatiquement
-- note: la valeur n'est calculée automatiquement qu'a l'insertion initiale. elle est conservée en cas de modifications
--

create or replace function m2_set_from_value() returns trigger as $$
       begin
		  NEW.from_value = substr(encode(digest(NEW.short_desc || NEW.description || NEW.ville, 'sha512'), 'hex'), 1, 8);
		  return NEW;
       end;
$$ language plpgsql;

create trigger trig_m2_set_from_value before insert on m2 for each row execute procedure m2_set_from_value ();

insert into m2 (short_desc, description, ville) values ('A2P', 'Astrophysique, Plasmas et Planètes', 'Grenoble');
insert into m2 (short_desc, description, ville) values ('Astro75', 'Astrophysique', 'Paris');
insert into m2 (short_desc, description, ville) values ('DSG', 'Dynamique des Systèmes Gravitationnels', 'Paris');
insert into m2 (short_desc, description, ville) values ('OSAE', 'Outils et Systèmes de l''Astronomie de de l''Espace', 'Paris');
insert into m2 (short_desc, description, ville) values ('Astro67', 'Astrophysique UNISTRA', 'Strasbourg');
insert into m2 (short_desc, description, ville) values ('ASEP', 'Astrophysique, Sciences de l''Espace, Planétologie', 'Toulouse');
insert into m2 (short_desc, description, ville) values ('TSI', 'Techniques Spatiales et Instrumentation', 'Toulouse');
insert into m2 (short_desc, description, ville) values ('OMEGA', 'Optique, Dynamique, Images, Astrophysique', 'Nice');
insert into m2 (short_desc, description, ville) values ('AER', 'Astrophysique, Energie, Rayonnement', 'Marseille');
insert into m2 (short_desc, description, ville) values ('APC', 'Astrophysique et Physique Corpusculaire', 'Bordeaux');
insert into m2 (short_desc, description, ville) values ('PF', 'Physique Fondamentale', 'Lyon');

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
grant select on categories to stcollweb;

create view liste_categories as select id as key, description as value from categories;
grant select on liste_categories to stcollweb;

insert into categories(description) values 
       ('Cosmologie, Univers primordial, origine et évolution des grandes structures de l''Univers et des galaxies'),
       ('Astrophysique des hautes énergies, objets compacts, astroparticules, ondes gravitationnelles'),
       ('Physique et chimie des milieux interstellaires et circumstellaires'),
       ('Formation, structure et évolution des étoiles'),
       ('Exoplanetes: origine, structure et évolution des systèmes planétaires, planétologie comparée'),
       ('Système solaire: origine, composition, évolution, structure physico-chimique et dynamique de ses objets et des astromateriaux; cosmochimie'),
       ('Physique du soleil et de l''héliosphere'),
       ('Planétologie : physique, dynamique et chimie des atmosphères planétaires'),
       ('Géophysique : Terre, atmosphère, ionosphère, magnétosphère'),
       ('Processus physiques en astrophysique'),
       ('Systèmes de référence spatio-temporels'),
       ('Instrumentation pour les grands observatoires au sol et dans l''espace'),
       ('Autres');

--
-- Statuts des utilisateurs
--

create table statuts (
       id    	     bigserial,
       description   text,
       primary key (id)
);

grant select on statuts to stcollweb;

create view liste_statuts as select id as key, description as value from statuts;
grant select on liste_statuts to stcollweb;

insert into statuts (description) values
       ('Chercheur ou enseignant-chercheur en poste'),
       ('Ingénieur de recherche'),
       ('Chercheur en contrat post-doctoral');

--
-- Manager de projet
--
create table users (
       id    	      bigserial,
       f_name	      text,
       l_name	      text,
       email	      text,
       phone	      text,
       statut	      bigint not null references statuts ( id ),
       id_laboratoire bigint not null references laboratoires ( id ),
       login	      text not null unique,
       passwd	      text,
       salt	      bytea,
       login_fails    smallint default 0,
       account_valid  boolean default false,
       m2_admin	      bigint default NULL references m2 ( id ),
       super	      boolean default False,
       primary key (id)
);

create or replace view users_view as select id, f_name, l_name, email, phone, statut, id_laboratoire, m2_admin, super from users;
grant select on users_view to stcollweb;

--
-- table des logs
--

create table logs (
	date			timestamp not null,
	ipaddr			inet,
	function		text not null,
	login			text,
	message			text
);

grant select, insert on logs to stcollweb;

create or replace function append_log_login(t_function text, t_login text, t_message text, t_ipaddr inet) returns void as $$
	begin
		
		insert into logs values (CURRENT_TIMESTAMP, t_ipaddr, t_function, t_login, t_message);
        end;
$$ language plpgsql security definer;

create or replace function append_log(t_function text, t_uid bigint, t_message text, t_ipaddr inet default null) returns void as $$
        declare
		t_account record;
		t_login   text;
	begin
		select * into t_account from users where id=t_uid;
		if found then
		        t_login := t_account.login;
		else
			t_login := 'Unknown User';
		end if;
		perform append_log_login (t_function, t_login, t_message, t_ipaddr);
	end;
$$ language plpgsql security definer;

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
create or replace function user_add(t_fname text, t_lname text, 
       	                 t_email text, t_phone text,
			 t_statut bigint, t_id_labo bigint, 
			 t_login text, t_password text,
			 t_ipaddr inet) 
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
		        insert into users (f_name, l_name, email, phone, statut, id_laboratoire, 
			       login, passwd, salt)
			       values (t_fname, t_lname, t_email, t_phone, t_statut, t_id_labo, 
			       t_login, t_passwd, t_salt)
			       returning id into t_userid;
			perform append_log_login('register', t_login, 'User successfully registered',t_ipaddr);
		else
			-- should log ip address
			perform append_log_login('register',t_login,'Attempt to register user multiple times',t_ipaddr);
		end if;
		t_rec := (t_userid, t_emailhash); 
		return t_rec;
	end;
$$ language plpgsql security definer;

--
-- check if login/password can log onto the system. returns
-- -2  account not validated yet
-- -1  is account is disabled
--  0  if login or password wrong
-- uid if login successful
-- account is locked out after 3 failures
--
create or replace function user_login(t_login text, t_password text, t_ipaddr inet) returns record as $$
        declare
		t_account	record;
		t_temp  	bytea;
		t_passwd	text;
		t_lf		smallint;
		t_rec		record;
        begin
		-- grab the account info for the login
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
			t_passwd := hash_password ( t_password, t_account.salt ); 
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
-- récupère le hash pour envoyer l'email de nouveau
--
create or replace function user_get_email_hash(t_login text, t_password text, t_ipaddr inet) returns record as $$
        declare
		t_account	record;
		t_passwd	text;
		t_emailh	text;
		t_rec		record;
        begin
	        select * into t_account from users where login=t_login;
		if found then
		        if (t_account.login_fails >= 3) then
			        t_rec := (-1::bigint, null::text, null::text);
				return t_rec;
                        end if;
			t_passwd := hash_password ( t_password, t_account.salt );
			if (t_passwd = t_account.passwd) then
			        perform append_log_login ('validation_email',t_login,'User requested validation email sent',t_ipaddr);
			        t_emailh := hash_email(t_account.email, t_account.salt);
				t_rec := ( t_account.id, t_account.email, t_emailh);
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
-- validate account
-- 
--
create or replace function user_validate_account(t_login text, t_password text, t_hash text, t_ipaddr inet) returns bigint as $$
       declare
               t_account       record;
	       t_passwd	       text;
	       t_emailh	       text;
       begin
               -- grab the account info
	       select * into t_account from users where login=t_login;
	       if found then
	       	       if (t_account.login_fails >= 3) then
		       	       perform append_log_login ('account_validation',t_login,'User attempted validating locked account',t_ipaddr); 	
		               return -1;
		       end if;
		       t_passwd := hash_password ( t_password, t_account.salt );
		       if (t_passwd = t_account.passwd) then
		               -- check if t_hash est ok
			       t_emailh := hash_email(t_account.email, t_account.salt);
			       if (t_emailh = t_hash) then
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
-- Nature des travaux a accomplir dans le stage
-- 
create table nature_stage (
       id    		  bigint primary key,
       description	  text unique
);

grant select on nature_stage to stcollweb;

create view liste_nature_stage as select id as key, description as value from nature_stage;
grant select on liste_nature_stage to stcollweb;

insert into nature_stage values 
       (1, 'Instrumentation'),
       (2, 'Observations'),
       (3, 'Simulations numériques'),
       (4, 'Traitement de données'),
       (5, 'Modélisation'),
       (6, 'Expérimentation en laboratoire');

--
-- État de la demande de rémunération
--
create table pay_states (
       id    		bigint primary key,
       description      text unique
);
grant select on pay_states to stcollweb;

create view liste_pay_states as select id as key, description as value from pay_states;
grant select on liste_pay_states to stcollweb;

insert into pay_states values
       ( 1, 'Acquise'),
       ( 2, 'En cours de négociation');

--
-- table des offres
--
create sequence seq__offres__id;
create table offres (
       id		bigint not null primary key,
       id_type_offre	bigint not null references type_offre ( id ),
       id_project_mgr	bigint not null references users ( id ),
       year_value	integer,	
	
       sujet		text,
       short_desc	text,
       description	text,
       project_url	text,
       prerequis	text,
       infoscmpl	text,
       start_date	date not null,
       duree		text,
       co_encadrant	text,
       co_enc_email	text,
       pay_state	bigint not null references pay_states (id),
       pers_found	boolean,

       deleted		boolean,       

       create_date	timestamp,
       last_update	timestamp,
       fulltext         tsvector
);
alter sequence seq__offres__id owned by offres.id;
alter table offres alter column id set default nextval('seq__offres__id');
create index fulltext on offres using gin(fulltext);

create or replace function offre_generate_fulltext() returns trigger as $$
        declare
                tv	tsvector;
		sujet	text;
		short	text;
		descr	text;
        begin
		if (NEW.sujet IS NULL) then
		        sujet := '';
		else
			sujet := NEW.sujet;
		end if;
		if (NEW.short_desc IS NULL) then
		        short := '';
		else
			short := NEW.short_desc;
		end if;
		if (NEW.description IS NULL) then
		        descr := '';
		else
			descr := NEW.description;
		end if;
		tv := to_tsvector('french', sujet || ' ' || short || ' ' || descr);
		NEW.fulltext := tv;
		return NEW;
        end;
$$ language plpgsql;

create trigger trig_generate_fulltext before insert or update on offres for each row execute procedure offre_generate_fulltext ();

grant select, insert, update on offres to stcollweb;
grant usage on sequence seq__offres__id to stcollweb;
 
--
-- table de liaison offres <-> categories (n to n)
--
create table offres_categories (
       id_offre		       bigint not null references offres ( id ),
       id_categorie	       bigint not null references categories ( id ),
       primary key (id_offre, id_categorie)
);
grant select, insert, delete, update on offres_categories to stcollweb;
--
-- table de liaison offres <-> nature_stage (n to n)
--

create table offres_nature_stage (
       id_offre			 bigint not null references offres(id),
       id_nature_stage		 bigint not null references nature_stage(id),
       primary key (id_offre, id_nature_stage)
);
grant select, insert, delete, update on offres_nature_stage to stcollweb;

--
-- table des validations par les M2
--
create table offres_m2 (
       id_offre		    bigint not null references offres (id),
       id_m2 		    bigint not null references m2 (id),
       primary key (id_offre, id_m2)
);
    
grant select, insert, delete, update on offres_m2 to stcollweb;
