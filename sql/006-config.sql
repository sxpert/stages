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

drop table if exists config;

create table config (
	key    		text,
	value		text,
	version_date 	timestamp,
	primary key (key, version_date)
);
grant select on config to stagesweb;
grant select, insert, delete, update on config to stagesadm;
