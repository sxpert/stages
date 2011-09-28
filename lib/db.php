<?php

require_once('db_config.php');

function db_connect() {
  GLOBAL $DB_HOST, $DB_PORT, $DB_NAME, $DB_USER, $DB_PASS;

  $connstr = "host=$DB_HOST port=$DB_PORT dbname=$DB_NAME user=$DB_USER password=$DB_PASS";
  error_log("connecting to database '".$connstr."'");
  $db = pg_connect($connstr);
  return $db;
}

?>