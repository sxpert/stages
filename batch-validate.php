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
$admin = stc_is_admin();
$action = stc_get_variable($_REQUEST, 'action');

/*
 * error if we are neither admin, nor we are here to batch-validate
 */
if ((!$admin)||($action!='batch-validate')) {
    stc_top();
    $menu = stc_default_menu();
    stc_menu($menu);
    echo "<h1>Accès interdit</h1>\n";
    stc_footer();    
    exit(0);
}

$multisel = stc_get_variable ($_REQUEST,'multisel');

stc_top();
$menu = stc_default_menu();
stc_menu($menu);
echo "<pre>";
echo "user\n";
print_r($user);
echo "\nadmin\n";
if ($admin==TRUE) echo "true";
else print_r($admin);
echo "\n_REQUEST\n";
print_r($_REQUEST);
echo "\nmultisel\n";
print_r($multisel);
echo "</pre>";
stc_footer();

?>