<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2017
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once ('lib/stc.php');

$admin = stc_is_admin();

if ($admin !== true) {
  # error 500
  stc_fail(403, 'Absence de droits d\'admin');
} 

$id = stc_get_variable($_POST, 'id');

function json_error($code, $message) {
  http_response_code($code);
  header('Content-type: application/json');
  $response = array('ok' => false, 'error' => $message);
  echo json_encode($response);  
  exit;
}


if (strlen($id) == 0) {
  json_error(412, 'Invalid id value');
}

if (!is_numeric($id)) {
  json_error(412, 'id value must be a number');
}

$dba = db_connect_adm();
$sql = "update m2 set active=false where id=$1;";
$res = pg_query_params($dba, $sql, array($id));

if ($res === false) {
  json_error(500, 'error during the query');
}

$stat = pg_result_status($res);

if ($stat != PGSQL_COMMAND_OK) {
  json_error(404, 'm2 with id '.$id.' does not exist');
}

header('Content-type: application/json');
$response = array('ok' => true);
echo json_encode($response);
?>
