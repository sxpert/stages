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
	
	# header
	echo '<div class="header">';
	echo '<span class="login">Login</span>';
	echo '<span class="admin">Admin ?</span>';
	echo '<span class="lname">Nom de Famille</span>';
	echo '<span class="fname">Prénom</span>';
	echo '<span class="email">Email</span>';
	echo '<span class="labo">Laboratoire</span>';
	echo '<span class="status">État du compte</span>';
	echo "</div>\n";

	# list of users
	while (True) {
		$user = pg_fetch_object ($users);
		if ($user) {
			#echo '<a class="user-row" href="detail-user.php?id='.$user['id'].'">';
			echo '<a class="user-row" href="account-details.php?id='.$user->id.'">';
			echo '<span class="login">'.$user->login.'</span>';
			echo '<span class="admin">';
			if (strcmp($user->super, 't')==0) echo "Super";
			elseif ($user->m2_admin) echo $user->m2_admin;
			else echo '-';
			echo '</span>';
			echo '<span class="lname">'.$user->l_name.'</span>';
			echo '<span class="fname">'.$user->f_name.'</span>';
			echo '<span class="email">'.$user->email.'</span>';
			echo '<span class="labo">'.$user->id_laboratoire.'</span>';
			switch ($user->login_fails) {
                        case 1: echo '<span class="alert">Oubli ?</span>'; break;
                        case 2: echo '<span class="warn">Attention</span>'; break;
                        case 3: echo '<span class="locked">Bloqué</span>'; break;
			}
			echo "</a>\n";
		} else break;
	}
} else {
	echo "Affichage interdit";
}

stc_footer();

?>
