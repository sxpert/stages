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

stc_style_add("/css/liste-users.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

$admin=stc_is_admin();
if ($admin===true) {
	$dba = db_connect_adm();
	echo "<h2>Liste des utilisateurs ";
	echo '<a id="add-user" href="detail-user.php?action=new-user">+</a>';
	echo "</h2>\n";
	/* boucler dans les laboratoires */
	$sql = 'select * from users order by l_name, f_name;';
	$users = pg_query($dba, $sql);
	while (True) {
		$user = pg_fetch_assoc ($users);
		if ($user) {
			echo '<div><a href="detail-user.php?id='.$user['id'].'">';
			echo '<span class="login">'.$user['login'].'</span>';
			echo "</div>\n";
		} else break;
	}
} else {
	echo "Affichage interdit";
}

stc_footer();

?>
