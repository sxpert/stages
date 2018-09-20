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

stc_style_add("/css/liste-configs.css");
stc_style_add("https://fonts.googleapis.com/icon?family=Material+Icons");
stc_script_add('/js/liste-configs.js',-1);
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if ($admin!==true) {
  echo "Affichage interdit";
  stc_footer();
  exit(0);
}  

$sql = "select * from config_vars order by key asc;";
$res = pg_query ($db, $sql); 

echo "<h2>Liste des variables de configuration";
echo '<a id="add-config" href="detail-config.php?action=new-config">+</a>';
echo "</h2>\n";

// liste des configs

echo "<div class=\"header\">";
echo "<span class=\"key\">Nom config</span>";
echo "<span class=\"type\">Type Var</span>";
echo "<span class=\"desc\">Description</span>";
echo "<span class=\"value\">Valeur</span>";
echo "<span class=\"last-mod\">date mod</span>";
echo "</div>\n";

while ($row = pg_fetch_assoc($res)) {
	echo "<a class=\"list";
	echo "\" href=\"detail-config.php?key=".$row['key']."\">";
	echo "<span class=\"key\">".$row['key']."</span>";
	echo "<span class=\"type\">".$row['var_type']."</span>";
	echo "<span class=\"desc\">".$row['description']."</span>";

	$sql = "select * from config where key=$1 order by version_date desc;";
	$r = pg_query_params($db, $sql, array($row['key']));
	$conf_row = pg_fetch_assoc($r);
	pg_free_result($r);

	

	echo "<span class=\"value\">".$conf_row['value']."</span>";
	echo "<span class=\"last-mod\">".$conf_row['version_date']."</span>";
	echo "</a>";
}

// bouton "ajouter"

stc_footer();

?>
