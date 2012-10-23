<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

$user = stc_user_id();

stc_style_add("/css/detail.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if ($user>0) {
  echo "<h2>Contacter l'administrateur de la base</h2>\n";
  echo "<div><a href=\"contact.php?type=admin\">Cliquez ici</a></div>\n";
  echo "<h2>Contacter les responsables des formations</h2>\n";
  /* boucler dans les M2 */
  $sql = "select id, short_desc, description, ville from m2 where active=true order by ville, short_desc, description;";
  $m2s = pg_query($db, $sql);
  while (True) {
    $m2 = pg_fetch_assoc ($m2s);
    if ($m2) {
      echo "<div><a href=\"contact.php?type=m2&id=".$m2['id']."\">";
      echo $m2['short_desc']." - ".$m2['description']." ( ".$m2['ville']." )";
      echo "</a></div>\n";
    } else break;
  }

} else {
  echo "Affichage interdit";
}

stc_footer();

?>