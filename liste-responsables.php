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
  echo "<h1>Liste des responsables de M2R</h1>\n";
  /* boucler dans les M2 */
  $sql = "select id, short_desc, description, ville from m2 order by ville;";
  $m2s = pg_query($db, $sql);
  while (True) {
    $m2 = pg_fetch_assoc ($m2s);
    if ($m2) {
      echo "<h2>".$m2['short_desc']." - ".$m2['description']." (".$m2['ville'].")</h2>\n";
      /* boucler sur les responsables */
      
    } else break;
  }

} else {
  echo "Affichage interdit";
}



stc_footer();

?>