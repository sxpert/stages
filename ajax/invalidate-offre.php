<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('../lib/stc.php');

$user = stc_user_id();
$admin = stc_is_admin();
$super = ($admin === true);

$offre_id = stc_get_variable($_POST, 'offre_id');
$m2_id = stc_get_variable($_POST, 'm2_id');

error_log($user.' '.$admin.' '.$super.' '.$offre_id.' '.$m2_id);

function error ($message) {
	header('Content-Type: application/json');
	echo json_encode(array ('ok'=>false,'error'=>$message));
	exit();
}

function ok () {
	header('Content-Type: application/json');
	echo json_encode(array ('ok'=>true));
	exit();
}

if ($user==0) error('Votre session a expirée');

if ($super||($admin==$m2_id)) {
	error_log ('remove');
	// enlever la validation
	$sql = 'delete from offres_m2 where id_offre=$1 and id_m2=$2;';
	$res = pg_query_params($db, $sql, array($offre_id, $m2_id));
	$n = pg_affected_rows($res);
	switch ($n) {
		case 0: error('L\'offre n\'était pas validée pour cette M2R'); break;
		case 1: ok (); break;
		default:
			// should not happen
	}
	
}

error('Vous n\'avez pas le droit d\'effectuer cette opération');
?>
