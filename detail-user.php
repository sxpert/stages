<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

/****
 * création / modification d'un laboratoire
 */

require_once('lib/stc.php');

$user = stc_user_id();

stc_style_add("/css/detail-user.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

$admin = stc_is_admin();
if ($admin===true) {

	$dba = db_connect_adm();
	
	if (array_key_exists ('action', $_REQUEST))
		$action = stc_get_variable ($_REQUEST, 'action');

	if (isset($action) and strcmp($action, 'new-user')==0) {
		$id = '';
	} else {
		$id = stc_get_variable ($_REQUEST, 'id');
		if (!(is_numeric($id)&&(intval($id)==floatval($id)))) {
			// not an integer, get out
			stc_footer();
			exit (0);
		}
		$id = intval($id);
	} 
	
	$errors = array();

	if (isset($action)) {
		/******** 
		 * actions de modification et de création 
		 */
		echo '<b>'.$action." ".$id.'</b>';

		switch ($action) {
		case "edit-user" :
			$usr = pg_query_params ($dba, 'select * from users where id=$1', [$oldid]);
			$row = pg_fetch_object ($usr);
			$super = (strcmp($row->super,'t')!=0);
			$m2_admin = $row->m2_admin;
			break;
		case "create-user" :
		case "modify-user" :
			// presque pareil, seule la requete a la fin change
			// id est déjà disponible
			$super =	 	stc_form_clean_checkbox(stc_get_variable ($_POST, 'super'));
			$m2_admin = 	stc_get_variable ($_POST, 'm2_admin');
			if (!is_numeric($m2_admin)) $m2_admin='null';
			else $m2_admin=intval($m2_admin);

			// error check
			
			$val = array(
				$m2_admin,
				$super,
				$id);

			if (strcmp($action, 'create-user')==0) {
				// we get an insult if we attempt to create a lab with duplicate id
				// not implemented
			} else {
				$sql = 'update users set ';
				$sql .= 'm2_admin=$1, ';
				$sql .= 'super=$2 ';
				$sql .= 'where id=$3;';
			}
			error_log('modifying user');
			$dba = db_connect_adm ();
			pg_send_query_params ($dba, $sql, $val); 
			$res = pg_get_result ($dba);
			$err = pg_result_error_field ($res, PGSQL_DIAG_SQLSTATE);
			// should not happen
			if ($err=='23505') {
				stc_form_add_error ($errors, 'id', 'Un laboratoire avec un numéro identique existe déjà');
			} else {
				error_log ("PSQL_DIAG_SQLSTATE :".$err);		
			}
		
			break;
		}

		switch ($action) {
		case 'new-user' :
		case 'create-user' :
			$new_button = "Créer l'utilisateur";
			$new_action = "create-user";
			break;
		case 'edit-user' :	
		case 'modify-user' :
			$new_button = "Modifier l'utilisateur";
			$new_action = "modify-user";
			break;
		}

		// formulaire de modification / creation
		$form = stc_form ('post', 'detail-user.php', $errors) ;
		stc_form_hidden ($form, 'id', $id);
		stc_form_checkbox ($form, "SuperAdmin", 'super', $super);	
		stc_form_select ($form, "Administrateur M2", 'm2_admin', $m2_admin, 'liste_m2');
		stc_form_button ($form, $new_button, $new_action);
		stc_form_end ();
	
		stc_footer();
		exit (0);
	}

	/********
	 * affichage simple des données 
	 */
	
	// obtention des données sur le laboratoire
	$usr = pg_query_params ($dba, 'select * from users where id=$1', [$id] );
	$row = pg_fetch_object ($usr);
	
	echo "<h2>".$row->f_name." ".$row->l_name."</h2>\n";

	// type d'administrateur
	if (strcmp($row->super,'t')==0) $admin = '<span class="super">SuperAdmin</span>';
	elseif (!is_null($row->m2_admin)) $admin = '<span>'.$row->m2_admin.'</span>';
	else  $admin = '<i>not admin</i>';
	echo '<div><label>admin</label><span class="admin">'.$admin."</span></div>\n";
		 
	
	echo "<br/>";

	// bouton de modification
	$form = stc_form ('post', 'detail-user.php', $errors);
	stc_form_hidden ($form, "id", $id);
	stc_form_button ($form, "Modifier l'utilisateur", "edit-user");
	stc_form_end();
} else {
	echo "Affichage interdit";
}

stc_footer();

?>
