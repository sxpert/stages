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

insert into type_offre (code, description, denom_dir, has_year) values ('TH', 'Thèse', 'Directeur de Thèse', false);
insert into type_offre (code, description, denom_dir, has_year) values ('MR', 'Master Recherche', 'Directeur de Stage', true);

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
       from_value	  char (8) not null
);
alter sequence seq__laboratoires__id owned by laboratoires.id;
alter table laboratoires alter column id set default nextval('seq__laboratoires__id');
create unique index pk__laboratoires__id on laboratoires ( id );
alter table laboratoires add primary key using index pk__laboratoires__id;
create unique index idx__laboratoires__desc on laboratoires ( description );
alter table laboratoires add unique using index idx__laboratoires__desc;

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

insert into laboratoires (sigle, description) values ('IPAG', 'Institut de Planétologie et d''Astrophysique de Grenoble');

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
create sequence seq__managers__id;
create table managers (
       id    	      bigint not null,
       f_name	      text,
       l_name	      text,
       email	      text,
       phone	      text,
       post_addr      text,
       post_code      text,
       city	      text,
       login	      text,
       passwd	      text
);
alter sequence seq__managers__id owned by managers.id;
alter table managers alter column id set default nextval('seq__managers__id');
create unique index pk__managers__id on managers ( id );
alter table managers add primary key using index pk__managers__id;
create unique index idx__managers__login on managers ( login );
alter table managers add unique using index idx__managers__login;

grant usage on sequence seq__managers__id to stcollweb;
grant select, insert, update on table managers to stcollweb;

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
       id_laboratoire	bigint not null,
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
alter table offres add foreign key ( id_laboratoire ) references laboratoires ( id );
alter table offres add foreign key ( id_project_mgr ) references managers ( id );
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
