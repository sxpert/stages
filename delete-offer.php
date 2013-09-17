<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

$user = stc_user_id();
$admin = stc_is_admin();
$super = ($admin===true);

$offre_id = intval(stc_get_variable ($_REQUEST,'offreid'));
$action = stc_get_variable ($_REQUEST, 'action');

if (strcmp($action,"NO_DELETE")==0) {
  header("Location: /detail.php?offreid=".$offre_id);
  exit;
}

$errors=array();
if (strcmp($action,"YES_DELETE")==0) {

	$sql = "update offres set deleted=true where id=$1 and id_project_mgr=$2;";
	$res = pg_query_params($db, $sql, array($offre_id,$user));
	$nb = pg_affected_rows($res);
	if ($nb==1) {
		header("Location: search.php?type=MR&projmgr=".$user);
		exit;
	}
	stc_form_add_error($errors,'offreid','Vous n\'avez pas les droits pour supprimer cette entrée');
}

/*
  
 */
stc_style_add("/css/delete-offer.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

echo "<h2>Suppression d'une offre</h2>\n";
$form = stc_form("POST", "delete-offer.php", $errors);
echo "<input type=\"hidden\" name=\"offreid\" value=\"".$offre_id."\"/>";
echo "<div>Êtes vous sûr(e) de vouloir supprimer l'offre :<br/>\n";

/* injecter les détails de la proposition */


echo "<div><button name=\"action\" value=\"NO_DELETE\">NON ce n'était pas<br/>\nce que je voulais faire</button>";
echo " <button name=\"action\" value=\"YES_DELETE\">Oui, oui,<br/>\nje suis sûr(e) de moi</button></div>";
echo "</div>\n";
stc_form_end();

stc_footer();

?>
