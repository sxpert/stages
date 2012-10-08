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
$super = is_bool($admin);

$offre_id = intval(stc_get_variable ($_POST,'offreid'));
$m2 = intval(stc_get_variable ($_POST,'m2'));

if (!$admin) stc_fail(403, "Opération non autorisée");

if (!$super) {
  /* l'utilisateur n'est pas super admin */
  $m2 = $admin;
} else {
  /* l'utilisateur est super admin. $m2 doit etre un entier non nul */
  if (!($m2 > 0)) /* probleme */
    stc_fail (500, "variable m2 (".$m2.") invalide"); 
}

/****
 * Vérifier que l'offre est pas encore validée pour la M2
 */

pg_free_result(pg_query($db,"begin;"));
$r = pg_query_params($db, "select * from offres_m2 where id_offre=$1 and id_m2=$2;",
		     array($offre_id, $m2));
if (pg_num_rows($r)==0) {
  pg_free_result($r);


  $r = pg_send_query_params($db, "insert into offres_m2 (id_offre, id_m2) values ($1, $2);",
			    array($offre_id, $m2));
  $r = pg_get_result($db);
  if (pg_result_status($r)!=PGSQL_COMMAND_OK) {
    stc_rollback('validate offre =>'.pg_result_error_field($r,PGSQL_DIAG_SQLSTATE).
		 ' - '.pg_last_error($db));
    stc_fail(500,"Impossible de valider l'offre");
  }
  pg_free_result($r);
  stc_append_log('validate_offer','user validated offer '.$offreid);
  pg_free_result(pg_query($db,"commit;"));
}

if ($super) 
  header ('Location: /detail.php?offreid='.$offre_id);
else {

  $r=pg_query_params($db,
		     'select code from offres, type_offres where offres.id_type_offre=type_offre.id and offres.id=$1',
		     array($offre_id));
  $row = pg_fetch_assoc($r);
  pg_free_result($r);
  
  header('Location: /search.php?type='.$row['code'].'&notvalid=1');
}

?>