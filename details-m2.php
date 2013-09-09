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

echo "<h1>Détails sur un Master 2 Recherche</h1>\n";

// aller pêcher les infos dans la bdd


stc_footer();

?>
