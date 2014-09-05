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

stc_style_add("/css/details-labo.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if ($user>0) {
	$id = stc_get_variable ($_REQUEST, 'id');
	if (!(is_numeric($id)&&(intval($id)==floatval($id)))) {
		// not an integer, get out
		stc_footer();
		exit (0);
	}
	$id = intval($id);
	
	$errors = array();

	if (array_key_exists ('action', $_POST)) {
		/******** 
		 * actions de modification et de création 
		 */
		$action = stc_get_variable ($_POST, 'action');
		echo $action." ".$id;

		switch ($action) {
		case "edit-labo" :
			$lab = pg_query_params ($db, 'select * from laboratoires where id=$1', [$id]);
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
			$new_button = "Modifier le laboratoire";
			$new_action = "modify-labo";
			break;
		case "new-labo" :
			// variables vides...
			$new_button = "Créer le laboratoire";
			$new_action = "create-labo";
			break;
		case "create-labo" :
		case "modify-labo" :
			// presque pareil, seule la requete a la fin change
			break;
		}

		// formulaire de modification / creation
		$form = stc_form ('post', 'detail-laboratoire.php', $errors) ;
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
