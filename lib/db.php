<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('db_config.php');

function _db_connect($user, $pass) {
  GLOBAL $DB_HOST, $DB_PORT, $DB_NAME;

  $connstr = "host=$DB_HOST port=$DB_PORT dbname=$DB_NAME user=$user password=$pass";
  $db = pg_connect($connstr);

  if (!$db) error_log("connecting to database '".$connstr."'");
  else error_log("Connection to database '".$DB_NAME."' successful");
      
  return $db;
}

function db_connect() {
  GLOBAL $DB_USER, $DB_PASS;
  return _db_connect($DB_USER, $DB_PASS);
}

function db_connect_adm() {
  GLOBAL $DB_ADMIN_USER, $DB_ADMIN_PASS;
  return _db_connect($DB_ADMIN_USER, $DB_ADMIN_PASS);
}

?>