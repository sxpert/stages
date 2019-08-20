--******************************************************************************
--
-- Gestion des offres de stage de M2 en Astro
-- (c) Raphaël Jacquot 2011-2018
-- Fichier sous licence GPL-3
--
--*****************************************************************************/

--
-- Ajout de variables de configuration
-- 

--\c stages stagesadm

drop type if exists config_var_type cascade;
drop table if exists config_vars;


create type config_var_type as enum (
	'date', 
	'email',
	'fqdn',
	'int',
	'locale',
	'path',
	'text',
	'timezone');

create or replace view liste_config_var_type as
	SELECT pg_enum.enumlabel as key ,  pg_enum.enumlabel AS value FROM pg_type, pg_enum where pg_type.typname = 'config_var_type' and pg_enum.enumtypid = pg_type.oid;
grant select on liste_config_var_type to stagesweb;


create table config_vars (
	key    		text primary key,
	description	text,
	var_type	config_var_type
);
grant select, insert, delete, update on config_vars to stagesweb;
grant select, insert, delete, update on config_vars to stagesweb;

insert into config_vars values
	('DATE_OUVERTURE',	'Date d''ouverture des stages pour les étudiants', 	'date'),
	('LOCALE',	  	'langue du site',		    			'locale'),
	('LOGO_DIR',		'Chemin ou sont stockés les logos de M2',		'path'),
	('MAIL_SRV',		'Serveur pour l''envoi des mails',			'fqdn'),
	('MAX_CHARS',		'Longueur maxi d''une proposition',			'int' ),
	('SERVER_EMAIL',	'Adresse source des mails envoyés',			'email'),
	('TZ',			'Fuseau horaire du serveur',				'timezone');

