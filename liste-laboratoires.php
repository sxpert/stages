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
stc_script_add("/js/liste-laboratoires.js",-1);
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

$admin=stc_is_admin();
if (($admin===true)||(is_numeric($admin))) {
	echo "<h2>Liste des laboratoires ";
	echo '<a id="add-labo" href="detail-laboratoire.php?action=new-labo">+</a>';
	echo "</h2>\n";
	/* boucler dans les laboratoires */
	$sql = 'select type_unite, id, sigle, description, '.
		'( case when laboratoires.univ_city is null then laboratoires.city else laboratoires.univ_city end) as ville, '.
		'name as country, visible '.
		'from laboratoires left join countries on laboratoires.country=countries.iso2 ';
	if (is_numeric($admin)) $sql.='where visible=true ';
	$sql.= 'order by id;';
	$labos = pg_query($db, $sql);
	echo '<div class="header">';
	echo '<span class="unit-id">numéro</span>';
	echo '<span class="sigle">sigle</span>';
	echo '<span class="desc">nom</span>';
	echo '<span class="city">ville universitaire</span>';
	echo '<span class="country">pays</span>';
	echo '<span class="type-unite">type</span>';
	if ($admin===true) echo '<span class="visible">visible</span>';
	echo "</div>\n";
	$ctr = 0;
	while (True) {
		$labo = pg_fetch_assoc ($labos);
		if ($labo) {
			echo '<div class="labo'.($ctr++%2==1?' odd':'').'">';
			if ($admin===true) echo '<a href="detail-laboratoire.php?id='.$labo['id'].'">';
			echo '<span class="unit-id">'.$labo['id'].'</span>';
			if ($admin===true) echo '</a>';
			echo '<span class="sigle">'.$labo['sigle'].'</span>';
			echo '<span class="desc">'.$labo['description'].'</span>';
			echo '<span class="city">'.$labo['ville'].'</span>';
			echo '<span class="country">'.$labo['country'].'</span>';
			echo '<span class="type-unite">'.$labo['type_unite'].'</span>';
			if ($admin===true) {
				echo '<span class="visible"><input class="visible-checkbox" type="checkbox" value="'.$labo['id'].'" '.
					((array_key_exists('visible',$labo)&&(strcmp($labo['visible'],'t')==0))?'checked':'').'/></span>';
			}
			echo "</div>\n";
		} else break;
	}
} else {
	echo "Affichage interdit";
}

stc_footer();

?>
