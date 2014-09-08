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

stc_style_add("/css/details-labo.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

$admin = stc_is_admin();
if ($admin===true) {
	
	if (array_key_exists ('action', $_REQUEST))
		$action = stc_get_variable ($_REQUEST, 'action');

	if (isset($action) and strcmp($action, 'new-labo')==0) {
		$id = '';
		$oldid = '';
	} else {
		$id = stc_get_variable ($_REQUEST, 'id');
		if (!(is_numeric($id)&&(intval($id)==floatval($id)))) {
			// not an integer, get out
			stc_footer();
			exit (0);
		}
		$id = intval($id);
		$oldid = stc_get_variable ($_POST, 'oldid');
		if ($oldid=='') $oldid=$id;
		else $oldid = intval($oldid);
	} 
	
	$errors = array();

	if (isset($action)) {
		/******** 
		 * actions de modification et de création 
		 */
		echo '<b>'.$action." ".$id.'</b>';

		switch ($action) {
		case "edit-labo" :
			$lab = pg_query_params ($db, 'select * from laboratoires where id=$1', [$oldid]);
			$row = pg_fetch_object ($lab);
			$type_unite = $row->type_unite;
			// id est déjà disponible
			$id_section = $row->id_section;
			$sigle = $row->sigle;
			$description = $row->description;
			$univ_city = $row->univ_city;
			$post_addr = $row->post_addr;
			$post_code = $row->post_code;
			$city = $row->city;
			break;
		case "create-labo" :
		case "modify-labo" :
			// presque pareil, seule la requete a la fin change
			// id est déjà disponible
			$type_unite = 	stc_get_variable ($_POST, 'type_unite');
			$id_section = 	stc_get_variable ($_POST, 'id_section');
			$sigle = 		stc_get_variable ($_POST, 'sigle');
			$description = 	stc_get_variable ($_POST, 'description');
			$univ_city = 	stc_get_variable ($_POST, 'univ_city');
			$post_addr = 	stc_get_variable ($_POST, 'post_addr');
			$post_code = 	stc_get_variable ($_POST, 'post_code');
			$city =			stc_get_variable ($_POST, 'city');

			// error check
			
			if (strlen($univ_city)==0) $univ_city=null;

			$val = array(
				$type_unite, 
				$id,
				$id_section, 
				$sigle, 
				$description, 
				$univ_city, 
				$post_addr, 
				$post_code, 
				$city, 
				$oldid);

			if (strcmp($action, 'create-labo')==0) {
				// we get an insult if we attempt to create a lab with duplicate id
				$sql = 'insert into laboratoires (';
				$sql .= 'type_unite, ';
				$sql .= 'id, ';
				$sql .= 'id_section, ';
				$sql .= 'sigle, ';
				$sql .= 'description, ';
				$sql .= 'univ_city, ';
				$sql .= 'post_addr, ';
				$sql .= 'post_code, ';
				$sql .= 'city) ';
				$sql .= 'values ($1,$2,$3,$4,$5,$6,$7,$8,$9);';
			} else {
				$sql = 'update laboratoires set ';
				$sql .= 'type_unite=$1, ';
				$sql .= 'id=$2, ';
				$sql .= 'id_section=$3, ';
				$sql .= 'sigle=$4, ';
				$sql .= 'description=$5, ';
				$sql .= 'univ_city=$6, ';
				$sql .= 'post_addr=$7, ';
				$sql .= 'post_code=$8, ';
				$sql .= 'city=$9 ';
				$sql .= 'where id=$10;';
			}
			$dba = db_connect_adm ();
			pg_send_query_params ($dba, $sql, $val); 
			$res = pg_get_result ($dba);
			$err = pg_result_error_field ($res, PGSQL_DIAG_SQLSTATE);
			if ($err=='23505') {
				stc_form_add_error ($errors, 'id', 'Un laboratoire avec un numéro identique existe déjà');
			} else {
				error_log ("PSQL_DIAG_SQLSTATE :".$err);		
			}
		
			break;
		}

		switch ($action) {
		case 'new-labo' :
		case 'create-labo' :
			$new_button = "Créer le laboratoire";
			$new_action = "create-labo";
			break;
		case 'edit-labo' :	
		case 'modify-labo' :
			$new_button = "Modifier le laboratoire";
			$new_action = "modify-labo";
			break;
		}

		// formulaire de modification / creation
		$form = stc_form ('post', 'detail-laboratoire.php', $errors) ;
		stc_form_hidden ($form, 'oldid', $oldid);
		stc_form_text ($form, "Type d'unité", "type_unite", $type_unite);
		stc_form_text ($form, "Numero d'unité", "id", $id);
		stc_form_text ($form, "Section CNRS", "id_section", $id_section);
		stc_form_text ($form, "Sigle", "sigle", $sigle);
		stc_form_text ($form, "Nom du laboratoire", "description", $description);
		stc_form_text ($form, "Ville universitaire", "univ_city", $univ_city);
		stc_form_text ($form, "Adresse postale", "post_addr", $post_addr);
		stc_form_text ($form, "Code postal", "post_code", $post_code);
		stc_form_text ($form, "Ville", "city", $city);
		stc_form_button ($form, $new_button, $new_action);
		stc_form_end ();
	
		stc_footer();
		exit (0);
	}

	/********
	 * affichage simple des données 
	 */
	
	// obtention des données sur le laboratoire
	$lab = pg_query_params ($db, 'select * from laboratoires where id=$1', [$id] );
	$row = pg_fetch_object ($lab);
	
	$caption=$row->description;
	if (strlen($row->sigle)>0) 
		$caption.=" (".$row->sigle.")";
	echo "<h2>".$caption."</h2>\n";
	echo "<div id=\"labid\"><label>Numéro d'unité</label><span>".$row->type_unite." ".$row->id."</span></div>\n";
	echo "<div id=\"univ-city\"><label>Ville universitaire</label><span>".$row->univ_city."</span></div>\n";		
	
	// prépares l'adresse postale pour affichage
	$pa = trim($row->post_addr)."<br/>".trim($row->post_code)." ".trim($row->city);
	echo "<div id=\"address\"><label>Adresse postale</label><span>".$pa."</span></div>\n";		
	echo "<br/>";

	// bouton de modification
	$form = stc_form ('post', 'detail-laboratoire.php', $errors);
	stc_form_hidden ($form, "id", $id);
	stc_form_button ($form, "Modifier le laboratoire", "edit-labo");
	stc_form_end();
} else {
	echo "Affichage interdit";
}

stc_footer();

?>
