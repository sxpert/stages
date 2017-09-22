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

stc_style_add("https://fonts.googleapis.com/icon?family=Material+Icons");
stc_style_add("/css/liste-users.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

$admin=stc_is_admin();
if ($admin===true) {
	$dba = db_connect_adm();
	echo "<h2>Liste des utilisateurs ";
	//echo '<a id="add-user" href="detail-user.php?action=new-user">+</a>';
	echo "</h2>\n";

	// obtenir les nombres de gens
	$sql = 'select count(id) as nombre, account_valid from users group by account_valid order by account_valid desc;';
	$nombres = pg_query($dba, $sql);
	$active = pg_fetch_object($nombres)->nombre;
 	$inactive = pg_fetch_object($nombres)->nombre;
	pg_free_result($nombres);		

	// sélectionner tous les utilisateurs
	$sql = 'select * from users order by lower(l_name), lower(f_name);';
	$users = pg_query($dba, $sql);

	// nombre de personnes
	// indique le nombre total, 

	echo '<div class="header">';
	echo $active." utilisateurs actifs<br/>\n".$inactive." utilisateurs inactifs";
	echo '</div>';
	
	# header
	echo '<div class="header">';
	echo '<span class="lname">Nom de Famille</span>';
	echo '<span class="fname">Prénom</span>';
	echo '<span class="state">État</span>';
	echo '<span class="login">Login</span>';
	echo '<span class="admin">Admin ?</span>';
	echo '<span class="email">Email</span>';
	echo '<span class="labo">Laboratoire</span>';
	echo '<span class="last-access">Dernier login</span>';
	echo '<span class="status">État du compte</span>';
	echo "</div>\n";

	# list of users
	while (True) {
		$user = pg_fetch_object ($users);
		if ($user) {
			#echo '<a class="user-row" href="detail-user.php?id='.$user['id'].'">';
			echo '<a class="user-row ';
			if ($user->account_valid=='f') 
				echo "inactive";
			echo '" href="account-details.php?id='.$user->id.'">';
			echo '<span class="lname">'.$user->l_name.'</span>';
			echo '<span class="fname">'.$user->f_name.'</span>';
			echo '<span class="state material-icons">';
			if ($user->account_valid=='f')
				echo 'thumb_down';
			echo '</span>';
			echo '<span class="login">'.$user->login.'</span>';
			echo '<span class="admin">';
			if (strcmp($user->super, 't')==0) echo "Super";
			elseif ($user->m2_admin) {
				$sql_m2 = "select * from liste_m2 where key=$1;";
			 	$r_m2 = pg_query_params($dba, $sql_m2, array($user->m2_admin));
				$m2 = pg_fetch_object($r_m2);
				pg_free_result($r_m2);
				echo $m2->value;
			} else echo '-';
			echo '</span>';
			echo '<span class="email">'.$user->email.'</span>';
			/* obtenir le nom du laboratoire */
			$sql_labo = "select * from liste_labos where key=$1;";
			$r_labo = pg_query_params($dba, $sql_labo, array($user->id_laboratoire));
			$labo = pg_fetch_object($r_labo);
			pg_free_result($r_labo);
			echo '<span class="labo">'.$labo->value.'</span>';
			# last access
			echo '<span class="last-access">';
			$sql_last_access = "select date_trunc('second', max(date)) as last_login, login from logs where function='login' and login=$1 group by login order by last_login desc;";
			$r_last_access = pg_query_params($dba, $sql_last_access, array($user->login));
			if (pg_num_rows($r_last_access) > 0) {
				$last_access = pg_fetch_object($r_last_access);
				echo $last_access->last_login;
			} else echo '-';
			pg_free_result($r_last_access);
			echo "</span>";
			# account locking information
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
