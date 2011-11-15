<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

$logged = stc_is_logged();
$admin = stc_is_admin();

stc_top();
$menu = stc_default_menu();
stc_menu($menu);

echo "<pre>".print_r($_REQUEST,1)."</pre>";

stc_footer();

?>