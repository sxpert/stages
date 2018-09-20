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


if (isset($action)) {
	
	$errors = array();

	$value = stc_get_variable($_POST, 'value');
	
	switch ($action) {
	case "create-config":
		$sql = "insert into config (key, value, version_date) values ($1, $2, now());";
		$dba = db_connect_adm ();
		$val = array($key, $value);
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
			header("Location: liste-configs.php");
			exit(0);
		}
	case "new-config":
		# stuff for form
		$caption = "Ajout d'une nouvelle variable '".$key."' (".$var_type.")";
		$new_button = "Créer la variable";
		$new_action = "create-config";
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
	switch($var_type) {
	default:
		stc_form_text ($form, "Valeur", "value", $value);
		break;
	}
	stc_form_hidden($form, "key", $key);
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
