#*******************************************************************************
#*
#* Gestion des offres de stage de M2 en Astro
#* (c) Raphaël Jacquot 2011
#* Fichier sous licence GPL-3
#*
#******************************************************************************/

PSQL = `which psql`

default:
	@echo "select all or upgrade"

upgrade:
	@echo "upgrade not implemented yet"
	upgrade_one.sh

all:
	$(PSQL) template1 < 000-base.sql
	./gen-labs.php	


