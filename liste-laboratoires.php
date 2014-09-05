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

require_once('lib/stc.php');

$user = stc_user_id();

stc_style_add("/css/detail.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if ($user>0) {
	echo "<h2>Liste des laboratoires</h2>\n";
	/* boucler dans les laboratoires */
	$sql = "select type_unite, id, sigle, description, city from laboratoires order by id;";
	$labos = pg_query($db, $sql);
	while (True) {
		$labo = pg_fetch_assoc ($labos);
		if ($labo) {
			echo "<div><a href=\"detail-laboratoire.php?id=".$labo['id']."\">";
			echo $labo['type_unite'].' '.$labo['id'];$labo['sigle'].' '.$labo['description'];
			echo "</a> ".$labo['sigle'].' '.$labo['description']."</div>\n";
		} else break;
	}
} else {
	echo "Affichage interdit";
}

stc_footer();

?>
