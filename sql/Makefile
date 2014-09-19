#*******************************************************************************
#*
#* Gestion des offres de stage de M2 en Astro
#* (c) Raphaël Jacquot 2011
#* Fichier sous licence GPL-3
#*
#******************************************************************************/

PSQL := $(shell which psql )
DATABASE := $(shell php -r 'include ("../lib/db_config.php"); echo $$DB_NAME;')

upgrade:
	$(PSQL) -f build-db.sql $(DATABASE)

all:
	$(PSQL) template1 < 000-base.sql
	./gen-labs.php	


