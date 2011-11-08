<?php
require_once ('lib/stc.php');

stc_must_be_logged();

$errors = array();

$category     = stc_get_variable($_POST, 'category');
if (!is_array($category)) $category = null;
$sujet        = stc_get_variable($_POST, 'sujet');
$description  = stc_get_variable($_POST, 'description'); 
$url          = stc_get_variable($_POST, 'url');
$nature_stage = stc_get_variable($_POST, 'nature_stage');
$prerequis    = stc_get_variable($_POST, 'prerequis');

$lieu         = stc_get_variable($_POST, 'lieu');
$infoscmpl    = stc_get_variable($_POST, 'infoscmpl');

$start_date   = stc_get_variable($_POST, 'start_date');
$length       = stc_get_variable($_POST, 'length');

$co_encadrant = stc_get_variable($_POST, 'co_encadrant');
$co_enc_email = stc_get_variable($_POST, 'co_enc_email');

$pay_state    = stc_get_variable($_POST, 'pay_state');
$thesis       = stc_get_variable($_POST, 'thesis');

// vérification de la validité du formulaire



// génération du formulaire

stc_top();
$menu = stc_default_menu();
stc_menu($menu);

//echo "<pre>".print_r($_POST,1)."</pre>";

$width="400pt";

$form = stc_form('post', 'propose.php', $errors);
stc_form_select ($form, "Catégories", "category", $category, "liste_categories",
		 array("multi" => true, "width" => $width));		 
echo "<br/>\n";

stc_form_text ($form, "Sujet du stage", "sujet", $sujet, $width);
stc_form_textarea ($form, "Description", "description", $description, $width, "200pt");
echo "<br/>\n";

stc_form_text ($form, "Page web du projet", "url", $url, $width);
echo "<br/>\n";
stc_form_select ($form, "Nature du travail", "nature_stage", $nature_stage, "liste_nature_stage",
		 array("multi" => true, "width" => $width));
echo "<br/>\n";

stc_form_text ($form, "Prérequis", "prerequis", $prerequis, $width);
echo "<br/>\n";

stc_form_text ($form, "Lieu du stage", "lieu", $lieu, $width);
stc_form_text ($form, "Informations complémentaires", "infoscmpl", $infoscmpl, $width);
echo "<br/>\n";

stc_form_date ($form, "Date indicative de début du stage", "start_date", $start_date);
stc_form_text ($form, "Durée du stage", "length", $length, $width);
echo "<br/>\n";

stc_form_text ($form, "Nom du co-encadrant", "co_encadrant", $co_encadrant, $width);
stc_form_text ($form, "Email du co-encadrant", "co_enc_email", $co_enc_email, $width);
echo "<br/>\n";

stc_form_select ($form, "Gratification du stage", "pay_state", $pay_state, "liste_pay_states");
stc_form_checkbox ($form, "Poursuite en thèse possible", "thesis", $thesis);

stc_form_button ($form, "Proposer un stage", "propose_task");
stc_form_end();
stc_footer();
?>
