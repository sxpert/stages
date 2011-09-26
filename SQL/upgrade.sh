#!/bin/bash
# get version number
VERSION=`echo '\\pset t on \\\\ select last_value from seq_version;' | psql -q stcoll`
echo $VERSION
# generate filename
VERSION="00$VERSION"
echo $VERSION
