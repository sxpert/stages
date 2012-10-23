#!/bin/bash
#*******************************************************************************
#*
#* Gestion des offres de stage de M2 en Astro
#* (c) Raphaël Jacquot 2011
#* Fichier sous licence GPL-3
#*
#******************************************************************************/

# get version number
VERSION=`echo '\\pset t on \\\\ select last_value from seq_version;' | psql -q stcoll`
echo $VERSION
# generate filename
VERSION="00$VERSION"
echo $VERSION
