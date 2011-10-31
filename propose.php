<?php
require_once ('lib/stc.php');

stc_must_be_logged();

$errors = array();

$formation = stc_get_variable($_POST, 'formation');
$category = stc_get_variable($_POST, 'category');
if (!is_array($category)) $category = null;
$sujet    = stc_get_variable($_POST, 'sujet');

stc_top();
$menu = stc_default_menu();
stc_menu($menu);

echo "<pre>".print_r($_POST,1)."</pre>";

$form = stc_form('post', 'propose.php', $errors);
stc_form_select ($form, "Formation", "formation", $formation, "liste_formations");
stc_form_select ($form, "Catégories", "category", $category, "liste_categories",
		 array("multi" => true));		 

stc_form_text ($form, "Sujet du stage", 'sujet', $sujet);
stc_form_button ($form, "Proposer un stage", "propose_task");
stc_form_end();
stc_footer();
?>
