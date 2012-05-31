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
$offre_id = intval(stc_get_variable ($_GET,'offreid'));
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

echo "Non encore implémenté";

stc_footer();

?>