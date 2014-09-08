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

stc_style_add("/css/liste-laboratoires.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

$admin=stc_is_admin();
if ($admin===true) {
	echo "<h2>Liste des laboratoires ";
	echo '<a id="add-labo" href="detail-laboratoire.php?action=new-labo">+</a>';
	echo "</h2>\n";
	/* boucler dans les laboratoires */
	$sql = "select type_unite, id, sigle, description, city from laboratoires order by id;";
	$labos = pg_query($db, $sql);
	while (True) {
		$labo = pg_fetch_assoc ($labos);
		if ($labo) {
			echo "<div><a href=\"detail-laboratoire.php?id=".$labo['id']."\">";
			echo '<span class="type-unite">'.$labo['type_unite'].'</span>';
			echo '<span class="unit-id">'.$labo['id'].'</span>';
			echo "</a> ";
			echo '<span class="sigle">'.$labo['sigle'].'</span>';
			echo '<span class="desc">'.$labo['description']."</span></div>\n";
		} else break;
	}
} else {
	echo "Affichage interdit";
}

stc_footer();

?>
