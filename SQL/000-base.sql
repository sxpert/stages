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

\c stcoll stcolladm

--
-- table des types de formations
--
create sequence id_formation_seq;
create table formation (
       id    	       bigint not null,
       description     text
);
alter sequence id_formation_seq owned by formation.id;
alter table formation alter column id set default nextval('id_formation_seq');
create unique index pk__formation__id on formation ( id );
alter table formation add primary key using index pk__formation__id;
create unique index idx__formation__desc on formation(description);
alter table formation add unique using index idx__formation__desc;

--
-- table des types d'offres
--
create sequence id_type_offre_seq;
create table type_offre (
       id    	       bigint not null,
       description     text
);
alter sequence id_type_offre_seq owned by type_offre.id;
alter table type_offre alter column id set default nextval('id_type_offre_seq');
create unique index pk__type_offre__id on type_offre ( id );
alter table type_offre add primary key using index pk__type_offre__id;
create unique index idx__type_offre__desc on type_offre ( description );
alter table type_offre add unique using index idx__type_offre__desc;

create table offres (
       id		bigint,
       id_formation	integer references formation(id),
       id_type_offre	integer references type_offre(id)         
);