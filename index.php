<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once ('lib/stc.php');

if (array_key_exists('from',$_REQUEST)) {
  $from = $_REQUEST['from'];
  stc_set_m2_provenance ($from);
}

stc_top();

// menu
$menu = stc_default_menu();
stc_menu($menu);

// contenu
?>
introduction au site
<?php

stc_footer();


?>
