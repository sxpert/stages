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

$id = stc_get_variable ($_REQUEST, 'id');

$id = intval($id);

$error = null;
$dest = null;

if (($admin===True) and ($id>0)) {
	# obtain the logo filename from the database
	$dba = db_connect_adm ();	
	$res = pg_query_params ($dba, "select url_logo from m2 where id=$1", array($id));
	$err = pg_result_error_field ($res, PGSQL_DIAG_SQLSTATE);
	
	$row = pg_fetch_assoc ($res);
	pg_free_result ($res);
	$current_url = $row['url_logo'];	

	# check if we need to replace the logo with a new one
	if (array_key_exists('action', $_REQUEST)) {
		if ($_REQUEST['action'] == "update-logo") {
			$nb_files = count($_FILES);
			switch ($nb_files) {
			case 0: $error = "Aucun document fourni, essayez de nouveau"; break;
			case 1: 
				$file = $_FILES['new-logo'];
				if (array_key_exists('error', $file) and ($file['error']!=0)) {
					$error = "Erreur lors de l'envoi de fichier";
				} else {
					$tmp_name = $file['tmp_name'];
					
					# should test image dimensions here
					
				
					$doc_name = $file['name'];
					$dest_url = $LOGO_DIR.'/'.$doc_name;
					$dest = $_SERVER['DOCUMENT_ROOT'].$dest_url;
					if (!move_uploaded_file($tmp_name, $dest)) {
						$error = "Erreur lors du déplacement du fichier";
					} else {
						# move is successful, time to write in database
						$res = pg_query_params ($dba, "update m2 set url_logo=$1 where id=$2;", array($dest_url,$id));
						$err = pg_result_error_field ($res, PGSQL_DIAG_SQLSTATE); 
						
						$current_url = $dest_url;
					}
				}
				break;
			default : $error = "Trop de documents fournis, essayez de nouveau";
			}
		}
	}
}

stc_style_add("/css/details-m2.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

#echo "<pre>".$LOGO_DIR."\n".$_SERVER['DOCUMENT_ROOT']."\n".$dest."\n".print_r($_REQUEST,1)."\n".$nb_files." files\n".print_r($_FILES,1)."</pre>";

if ($admin!==True) {
	echo "<h2>403 Forbidden</h2>";
	stc_footer();
	exit (0);
}

if ($id == 0) {
	# error, id can't be 0
	echo "<h2>404 not found</h2>";
	stc_footer();
	exit (0);
}

if (strlen($current_url) == 0) {
	echo "<div><i>pas de logo défini</i></div>\n";
} else {
	echo "<div>".$current_url."</div>\n";
	echo "<div><img src=\"".$current_url."\"/></div>\n";
}

if (!is_null($error)) {
	echo "<div class=\"error\">".$error."</div>\n";
}

echo "<form method=\"post\" enctype=\"multipart/form-data\">\n";
echo "<p>Envoyer un autre logo.<br/>Les images doivent avoir une hauteur de <strong>86 pixels</strong> pour s'intégrer correctement dans le site</p>\n";
echo "<p><input name=\"new-logo\" type=\"file\"/></p>\n";
echo "<p><button name=\"action\" value=\"update-logo\">envoyer le logo</button></p>\n";
echo "</form>\n";

stc_footer();
?>
