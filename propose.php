<?php
require_once ('lib/stc.php');

$errors = array();

$category = stc_get_variable($_POST, 'category');
$sujet    = stc_get_variable($_POST, 'sujet');

stc_top();
$menu = stc_default_menu();
stc_menu($menu);

$form = stc_form('post', 'propose.php', $errors);
stc_form_select ($form, "Catégories", "category", $category, "liste_categories",
		 array("multi" => true));		      
stc_form_text ($form, "Sujet du stage", $sujet);
stc_form_end();
stc_footer();
?>
