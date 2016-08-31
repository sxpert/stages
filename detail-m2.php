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

function top() {
	stc_style_add("/css/details-m2.css");
	stc_top();
	$menu = stc_default_menu();
	stc_menu($menu);
}

if ($admin!==true) {
  top();
  echo "Affichage interdit";
  stc_footer();
  exit(0);
}  

if (array_key_exists ('action', $_REQUEST))
        $action = stc_get_variable ($_REQUEST, 'action');

if (isset($action)) {
	
	$errors = array();
	
	if ($action=='new-m2') {
	}

	$short_desc = stc_get_variable($_POST, 'short_desc');
	$description = stc_get_variable($_POST, 'description');
	$ville = stc_get_variable($_POST, 'ville');

	switch ($action) {
	case "create-m2":
		$val = array ($short_desc, $description, $ville);
		$sql = "insert into m2 (short_desc, description, ville) values ($1,$2,$3);";
		$dba = db_connect_adm ();
		pg_send_query_params ($dba, $sql, $val);
		$res = pg_get_result ($dba);
		$err = pg_result_error_field ($res, PGSQL_DIAG_SQLSTATE);
		if (!is_null($err)) {
			if ($err=='23505') {
				stc_form_add_error ($errors, 'short_desc', 'un m2 existe déja');
			} else {
				error_log ("PGSQL_DIAG_SQLSTATE :".$err);
			}
		} else {
			header("Location: liste-m2.php");
			exit(0);
		}
		break;
	}
	
	switch ($action) {
	case 'new-m2':
		$new_button = "Créer le M2";
		$new_action = "create-m2";
		break;
	}

	top ();
	echo "<h2>Ajout d'un nouveau M2</h2>\n";
	$form = stc_form ('post', 'detail-m2.php', $errors);
	stc_form_text ($form, "Sigle", "short_desc", $short_desc);
	stc_form_text ($form, "Nom complet", "description", $description);
	stc_form_text ($form, "Ville", "ville", $ville);
	stc_form_button ($form, $new_button, $new_action);
	stc_form_end ();

} else {
	$m2 = stc_get_variable($_GET, 'id');
	if (!(is_numeric($m2)&&(intval($m2)==floatval($m2)))) {
		// not an integer
		stc_footer();
		exit (0);
	}
	top();
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

	$url_logo = $row['url_logo'];
	$show_logo = True;
	if (strlen($url_logo)==0) {
		$url_logo = "<a href=\"m2-update-logo.php?id=".$m2."\"><em>Aucun logo, cliquer pour en ajouter un</em></a>";
		$show_logo = False;
	}
	echo "<div id=\"logo\"><label>Logo :</label><div><div>".$url_logo."</div>\n";
	if ($show_logo) {
		echo "<div><img src=\"".$url_logo."\"/></div>";
	}

	echo "</div></div>\n";
	echo "<div id=\"active\"><label>Activé :</label><span>".($row['active']=='t'?'oui':'non')."</span></div>\n";

}

stc_footer();

?>
