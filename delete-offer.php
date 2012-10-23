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

if (strcmp($action,"YES_DELETE")==0) {
  
}

/*
  
 */
stc_style_add("/css/delete-offer.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

echo "<h2>Suppression d'une offre</h2>\n";
$form = stc_form("POST", "delete-offer.php", null);
echo "<input type=\"hidden\" name=\"offreid\" value=\"".$offre_id."\"/>";
echo "<div>Êtes vous sûr(e) de vouloir supprimer l'offre :<br/>\n";

/* injecter les détails de la proposition */


echo "<div><button name=\"action\" value=\"NO_DELETE\">NON ce n'était pas<br/>\nce que je voulais faire</button>";
echo " <button name=\"action\" values=\"YES_DELETE\">Oui, oui,<br/>\nje suis sûr(e) de moi</button></div>";
echo "</div>\n";
stc_form_end();

stc_footer();

?>