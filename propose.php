<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once ('lib/stc.php');

stc_must_be_logged();

$errors = array();

$category     = stc_get_variable($_POST, 'category');
if (!is_array($category)) $category = null;
$sujet        = stc_get_variable($_POST, 'sujet');
$description  = stc_get_variable($_POST, 'description'); 
$url          = stc_get_variable($_POST, 'url');
$nature_stage = stc_get_variable($_POST, 'nature_stage');
if (!is_array($nature_stage)) $nature_stage = null;
$prerequis    = stc_get_variable($_POST, 'prerequis');

$infoscmpl    = stc_get_variable($_POST, 'infoscmpl');

$start_date   = stc_get_variable($_POST, 'start_date');
$length       = stc_get_variable($_POST, 'length');

$co_encadrant = stc_get_variable($_POST, 'co_encadrant');
$co_enc_email = stc_get_variable($_POST, 'co_enc_email');

$pay_state    = stc_get_variable($_POST, 'pay_state');
$thesis       = stc_get_variable($_POST, 'thesis');

// vérification de la validité du formulaire
$errors = array();
if (strcmp($_SERVER['REQUEST_METHOD'],"POST")==0) {
  /****
   * vérifications
   */
  
  /* category */
  $category = stc_form_clean_multi($category);
  if (count($category)==0) 
    stc_form_add_error($errors, 'category', 'Il faut au moins une catégorie');
  elseif (!stc_form_check_multi($category, 'liste_categories'))
    stc_form_add_error($errors, 'category', 'Problème de cohérence dans les catégories');
  
  /* url */
  

  /* nature_stage */
  $nature_stage = stc_form_clean_multi($nature_stage);
  if (count($nature_stage)==0)
    stc_form_add_error($errors, 'nature_stage', 'Il faut au moins une nature pour le stage');
  elseif (!stc_form_check_multi($nature_stage, 'liste_nature_stage'))
    stc_form_add_error($errors, 'nature_stage', 'Problème de cohérence dans la nature du stage');

  /* start_date */
  if (!stc_form_clean_date($start_date))
    stc_form_add_error($errors, 'start_date', 'Format de date invalide, \'yyyy-mm-dd\' attendu');
  elseif (!stc_form_check_date($start_date)) 
    stc_form_add_error($errors, 'start_date', 'Date invalide');

  
}

/* génération du formulaire */

stc_top();
$menu = stc_default_menu();
stc_menu($menu);

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
