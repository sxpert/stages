<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

if ($_SERVER['REQUEST_METHOD']!='POST') stc_fail(405, "Requête invalide");

$user = stc_user_id();
$admin = stc_is_admin();
$from = stc_from();

$offre_id = intval(stc_get_variable ($_POST,'offreid'));

if (!$admin) stc_fail(403, "Opération non autorisée");

/****
 * Vérifier que l'offre est pas encore validée pour la M2
 */

pg_free_result(pg_query($db,"begin;"));
$r = pg_query_params($db, "select * from offres_m2 where id_offre=$1 and id_m2=$2;",
		     array($offre_id, $admin));
if (pg_num_rows($r)==0) {
  pg_free_result($r);
  $r = pg_send_query_params($db, "insert into offres_m2 (id_offre, id_m2) values ($1, $2);",
			    array($offre_id, $admin));
  $r = pg_get_result($db);
  if (pg_result_status($r)!=PGSQL_COMMAND_OK) {
    stc_rollback('validate offre =>'.pg_result_error_field($r,PGSQL_DIAG_SQLSTATE).
		 ' - '.pg_last_error($db));
    stc_fail(500,"Impossible de valider l'offre");
  }
  pg_free_result($r);
  pg_free_result(pg_query($db,"commit;"));
}

$r=pg_query_params($db,
		   'select code from offres, type_offres where offres.id_type_offre=type_offre.id and offres.id=$1',
		   array($offre_id));
$row = pg_fetch_assoc($r);
pg_free_result($r);

header('Location: /search.php?type='.$row['code'].'&notvalid=1');

?>