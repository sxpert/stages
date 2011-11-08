<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('../lib/stc.php');

// check session, method et présence de id=<numero>

$id = intval($_GET['id']);

$sql = "select * from laboratoires where id=$1";
pg_send_query_params($db, $sql, array($id));
$r = pg_get_result($db);
$row = pg_fetch_assoc($r);
pg_free_result ($r);
echo json_encode($row);
?>