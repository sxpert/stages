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

stc_style_add("/css/liste-m2.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if ($admin!==true) {
  echo "Affichage interdit";
  stc_footer();
  exit(0);
}  

$sql = "select * from m2 order by ville, short_desc;";
$res = pg_query ($db, $sql); 

echo "<p>Les formations actives sont en gras</p>\n";

// liste des m2

echo "<div class=\"header\">";
echo "<span class=\"short\">Sigle</span>";
echo "<span class=\"desc\">Nom complet</span>";
echo "<span class=\"ville\">Ville</span>";
echo "</div>\n";

$odd = 1;
while ($row = pg_fetch_assoc($res)) {
  echo "<div class=\"list";
  if ($odd) echo " odd";
  if ($row['active']=='t') echo " active";
  echo "\">";
  echo "<a href=\"details-m2.php?id=".$row['id']."\">";
  echo "<span class=\"short\">".$row['short_desc']."</span>";
  echo "<span class=\"desc\">".$row['description']."</span>";
  echo "<span class=\"ville\">".$row['ville']."</span>";
  echo "</a>";
  echo "</div>\n";
  $odd = ($odd+1)%2;
}

// bouton "ajouter"




stc_footer();

?>