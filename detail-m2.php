<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once ('lib/stc.php');

$admin = stc_is_admin();

stc_style_add("/css/details-m2.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if ($admin!==true) {
  echo "Affichage interdit";
  stc_footer();
  exit(0);
}  

$action = stc_get_variable($_GET, 'action');
if ($action=='new-m2') {


} else {
	$m2 = stc_get_variable($_GET, 'id');
	if (!(is_numeric($m2)&&(intval($m2)==floatval($m2)))) {
		// not an integer
		stc_footer();
		exit (0);
	}
	echo "<h1>Détails sur un Master 2 Recherche</h1>\n";

	// aller pêcher les infos dans la bdd
	$m2 = intval($m2);
	
	$sql = "select * from m2 where id=$1 order by ville, short_desc;";
	$res = pg_query_params ($db, $sql, array($m2)); 
	
	$row = pg_fetch_assoc($res);
	pg_free_result ($res);

	echo "<h2>".$row['description'].' ('.$row['short_desc'].")</h2>\n";
	echo "<div id=\"ville\"><label>Ville :</label><span>".$row['ville']."</span></div>\n";
	echo "<div id=\"from\"><label>From :</label><span>".$row['from_value']."</span></div>\n";
	echo "<div id=\"logo\"><label>Logo :</label><div><div>".$row['url_logo']."</div>\n";
	echo "<div><img src=\"".$row['url_logo']."\"/></div></div></div>\n";
	echo "<div id=\"active\"><label>Activé :</label><span>".($row['active']=='t'?'oui':'non')."</span></div>\n";

}

stc_footer();

?>
