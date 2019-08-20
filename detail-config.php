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
	stc_style_add("/css/details-config.css");
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

// get info on the variable
$key = stc_get_variable($_REQUEST, 'key');

if (!isset($action)) { 
	$sql = "select * from config_vars where key=$1;";
	$r = pg_query_params($db, $sql, array($key));
	$n = pg_num_rows($r);
	if ($n != 1) {
		// not a valid configuration variable
		top();
		echo "<p>Impossible de trouver la variable de configuration '".$key."'</p>";
		// not an integer
		stc_footer();
		exit (0);
	}
	$varinfo = pg_fetch_assoc($r);
	$var_type = $varinfo['var_type'];
	pg_free_result($r);

	// find the data about this variable.
	// if not found, add the variable
	$sql = "select * from config where key=$1 order by version_date desc;";
	$r = pg_query_params($db, $sql, array($key));
	$n = pg_num_rows($r);
	if (!isset($action)&&($n == 0)) {
		// can't find any record for this variable
		$action = 'new-config';
	}
}

if (isset($action)) {
	
	$errors = array();

	$description = stc_get_variable($_POST, "description");
	$type = stc_get_variable($_POST, "type");
	$value = stc_get_variable($_POST, 'value');
	
	$drop = FALSE;

	switch ($action) {
	case "create-new-config":
		error_log("create-new-config: got data '".$key."', '".$description."', '".$type."'");
		$sql = "insert into config_vars (key, description, var_type) values ($1, $2, $3);";
		$dba = db_connect_adm();
		pg_query($dba, "begin;");
		$drop = TRUE;
		$val = array($key, $description, $type);
		pg_send_query_params($dba, $sql, $val);
		$res = pg_get_result($dba);
		$err = pg_result_error_field($res, PGSQL_DIAG_SQLSTATE);
		if (!is_null($err)) {
			switch ($err) {
				case '23505':
					stc_form_add_error($errors, 'key', 'une variable existe déjà avec ce nom');
					break;
				case '22P02':
					stc_form_add_error($errors, 'type', 'invalid type not in enum');
					break;
				default:
					$errmsg = pg_result_error($res);
					error_log("PG_DIAG_SQL_STATE :".$err." - ".$errmsg);
			}
		} 
		# if we have errors, break here
		if (count($errors)>0) 
			break;
		# fall down to create the first entry
		error_log("create-new-config: fall down to create new value");
	case "create-config":
		error_log("create-config: got key value '".$key."', '".$value."'");
		$sql = "insert into config (key, value, version_date) values ($1, $2, now());";
		# create a new connection only if we're not dropping
		if (!$drop)
			$dba = db_connect_adm ();
		$val = array($key, $value);
		pg_send_query_params ($dba, $sql, $val);
		$res = pg_get_result ($dba);
		$err = pg_result_error_field ($res, PGSQL_DIAG_SQLSTATE);
		if (!is_null($err)) {
			if ($err=='23505') {
				stc_form_add_error ($errors, 'key', 'une clé/valeur identique existe déja');
			} else {
				$errmsg = pg_result_error($res);
				error_log ("PGSQL_DIAG_SQLSTATE :".$err." - ".$errmsg);
				// output some error message
			}
		} else {
			if ($drop)
				pg_query($dba, "commit;");
			header("Location: liste-configs.php");
			exit(0);
		}
		break;
	case "new-config":
		# stuff for form
		$caption = "Ajout d'une nouvelle variable";
		$new_button = "Créer la variable";
		$new_action = "create-new-config";
		break;
	case "edit-config":
		$row = pg_fetch_assoc($r);
		$value = $row['value'];
		# variables for form
		$caption = "Modification de la variable '".$key."' (".$var_type.")";
		$new_button = "Modifier la variable";
		// as we have history handling, use create here too
		$new_action = "create-config";
		break;
	}
	
	top ();
	echo "<h2>".$caption."</h2>\n";
	$form = stc_form ('post', 'detail-config.php', $errors);
	if ($action=="new-config") {
		stc_form_text($form, "Nom", "key", $key);
		stc_form_text($form, "Description", 'description', $description);
		# get the values for the enum	e
		#stc_form_text($form, "Type", "type", $type);
		stc_form_select ($form, "Type", "type", $type, "liste_config_var_type" ); 
	} else {
		stc_form_hidden($form, "key", $key);
	}
	switch($var_type) {
	default:
		stc_form_text ($form, "Valeur", "value", $value);
		break;
	}
	stc_form_button ($form, $new_button, $new_action);
	stc_form_end ();

} else {

	top();
	echo "<h1>Détails sur une variable de configuration</h1>\n";

	echo "<h2>".$key.' ('.$var_type.") ";
	echo "<a id=\"mod-config\" href=\"detail-config.php?action=edit-config&key=".$key."\">modifier</a>";
	echo "</h2>\n";


	echo "<div class=\"header\">";
	echo "<span class=\"version-date\">Version</span>";
	echo "<span class=\"value\">Valeur</span>";
	echo "</div>\n";

	// list values through time
	while ($row = pg_fetch_assoc($r)) {
		echo "<div class=\"list\">";
		echo "<span class=\"version-date\">".$row['version_date']."</span>";
		echo "<span class=\"value\">".$row['value']."</span>";
		echo "</div>\n";
	}	

}

stc_footer();

?>
