<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

/****
 * listage des laboratoires existants
 */

require_once('../lib/stc.php');

function fail () {
	echo json_encode(['toggle'=>'NOK']);
	exit (1);
}

$admin=stc_is_admin();
if ($admin===true) {
	$id = stc_get_variable($_REQUEST,'id');
	$visible = stc_get_variable($_REQUEST,'visible');
	
	if (!is_numeric($id)) fail();
	$id = intval($id);
	if ($id<=0) fail();
	if ((strcmp($visible,'true')!=0)&&(strcmp($visible,'false')!=0)) fail();
	
	$dba=db_connect_adm();
	pg_query_params ($dba, 'update laboratoires set visible=$1 where id=$2;', array($visible,$id));
	
	echo json_encode(['toggle'=>'OK']);	
} else {
	header('Location: /404-error.php');
}
