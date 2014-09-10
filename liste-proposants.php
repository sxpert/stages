<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

/****
 * liste des proposants de stages 
 */

require_once('lib/stc.php');

$user = stc_user_id();

stc_style_add("/css/liste-laboratoires.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

$admin=stc_is_admin();
if ($admin===true||is_numeric($admin)) {
	echo "<h2>Liste des emails des proposants dont un stage au moins a été validé pour votre filière</h2>\n";
	/* boucler dans les proposants */
	$sql = "select distinct o.id, u.email from users as u, offres as o, offres_m2 as om2 where u.id = o.id_project_mgr ";
	if (is_numeric($admin)) 
		$sql.='and o.id = om2.id_offre and om2.id_m2='.$admin;
	$sql.=';';
	$proposants = pg_query($db, $sql);
	$nb = pg_num_rows ($proposants);
	if ($nb>0) {
		while (True) {
			$prop = pg_fetch_object ($proposants);
			if ($prop !== false) {
				echo "<div>".$prop->email."</div>\n";
			} else break;
		}
	} else echo "<p><i>liste vide</i></p>";
} else {
	echo "Affichage interdit";
}

stc_footer();

?>
