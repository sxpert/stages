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

/* tableau des erreurs */
$errors = array();

$type         = stc_get_variable($_REQUEST, 'type');
$offreid      = intval(stc_get_variable($_REQUEST, 'offreid'));
$action       = stc_get_variable($_REQUEST, 'action');

define ('NONE',   0);
define ('INSERT', 1);
define ('UPDATE', 2);
define ('EDIT',   3);
$mode=NONE;
if ($action=='propose_task') $mode=INSERT;
if ($action=='update_task') $mode=UPDATE;
if (($action=='edit')&&($offreid>0)) $mode=EDIT;

if ($mode==EDIT) {
  error_log ('edit mode, fetching offre_id '.$offreid);
  /* TODO: aller chercher le contenu de l'offre dans la base de données */
  
  /* récupérer le contenu de l'offre */
  $r=pg_query_params($db, 'select * from offres where id=$1', array($offreid));
  $nb = pg_num_rows($r);
  if ($nb==0) stc_fail(404, 'Impossible de trouver l\'offre numéro '.$offreid);
  $offre = pg_fetch_assoc($r);
  pg_free_result($r);
  
  $sujet        = $offre['sujet'];
  $description  = $offre['description'];
  $url          = $offre['project_url'];
  $prerequis    = $offre['prerequis'];
  $infoscmpl    = $offre['infoscmpl'];
  $start_date   = $offre['start_date'];
  $length       = $offre['duree'];
  $co_encadrant = $offre['co_encadrant'];
  $co_enc_email = $offre['co_enc_email'];
  $pay_state    = $offre['pay_state'];
  //$thesis       = ($offre['thesis']=='t'?"true":"false");

  /* type offre */
  $r = pg_query_params($db, 'select code from type_offre where id=$1', array(intval($offre['id_type_offre'])));
  $row = pg_fetch_assoc($r);
  $type = $row['code'];
  pg_free_result($r);

  /* categories */
  $r = pg_query_params($db, 
		       "select id_categorie from offres_categories where id_offre=$1 order by id_categorie", 
		       array($offreid));
  $categories = array();
  while ($row=pg_fetch_assoc($r)) 
    array_push($categories, intval($row['id_categorie']));
  pg_free_result ($r);

  /* nature_stage */
  $r = pg_query_params($db, 
		       "select id_nature_stage from offres_nature_stage where id_offre=$1 order by id_nature_stage", 
		       array($offreid));
  $nature_stage = array();
  while ($row=pg_fetch_assoc($r)) 
    array_push($nature_stage, intval($row['id_nature_stage']));
  pg_free_result ($r);

} else {
  $categories     = stc_get_variable($_POST, 'categories');
  if (!is_array($categories)) $categories = null;
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
  //$thesis       = stc_get_variable($_POST, 'thesis');
}

if (($mode==INSERT)||($mode==UPDATE)) {
  // vérification de la validité du formulaire
  if (strcmp($_SERVER['REQUEST_METHOD'],"POST")==0) {
    /****
     * vérifications
     */
    /* TODO: à changer si on inclue autre chose que des stages de M2 */
    if ($type!='MR')
      stc_form_add_error($errors, 'type', 'Mauvais type d\'offre');
    
    /* category */
    $categories = stc_form_clean_multi($categories);
    if (count($categories)==0) 
      stc_form_add_error($errors, 'categories', 'Il faut au moins une catégorie');
    elseif (!stc_form_check_multi($categories, 'liste_categories'))
      stc_form_add_error($errors, 'categories', 'Problème de cohérence dans les catégories');
    
    if (mb_strlen($description, 'UTF-8')>($MAX_CHARS*1.03)) {
      stc_form_add_error($errors, 'description', 'Texte trop long, la taille maximale autorisée est '.$MAX_CHARS.' signes');
    }
    
    /* url */
    $url = stc_form_clean_url($url);
    if ((strlen($url)>0)&&(!stc_form_check_url($url, $e)))
      stc_form_add_error($errors, 'url', 'Adresse de document invalide.<br/>'.$e);
    
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
    
    /* pay_state */
    if (!stc_form_check_select($pay_state, 'liste_pay_states'))
      stc_form_add_error($errors, 'pay_state', 'Option de gratification invalide');  
    
    /* thesis */
    //if (strcmp($thesis,"true")==0) $thesis=true;
    //else $thesis=false;
   
    /****
     * Si on arrive la et que $errors est vide c'est que tout va bien
     */
    if (count($errors)==0) {
      if ($mode==INSERT) {
	/* insertion dans la base de données */
	$offre = stc_offre_add($type, $categories, $sujet, $description, $url, $nature_stage, $prerequis,
			       $infoscmpl, $start_date, $length, $co_encadrant, $co_enc_email, $pay_state/*, $thesis*/);
	if (is_bool($offre)&&(!$offre))
	  stc_form_add_error($errors, 'type', 'Erreur lors de l\'ajout de l\'offre');
	else 
	  stc_redirect("/detail.php?mode=new&offreid=".$offre);
      }
      if ($mode==UPDATE) {
	stc_form_add_error($errors, 'type', 'mise à jour des offres en cours d\'implémentation');
	$offre = stc_offre_update($offreid, $categories, $sujet, $description, $url, $nature_stage, $prerequis,
				  $infoscmpl, $start_date, $length, $co_encadrant, $co_enc_email, $pay_state/*, $thesis*/);
	if (is_bool($offre)&&(!$offre))
	  stc_form_add_error($errors, 'type', 'Erreur lors de la mise à jour de l\'offre');
	else 
	  stc_redirect("/detail.php?mode=update&offreid=".$offre);
	
      }
    }
  }
}
  
/* génération du formulaire */

stc_style_add("/css/propose.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

$width="550px";

echo "<div class=\"important\">Le déposant s'engage à informer sa hiérarchie de la diffusion de cette proposition.</div>\n";

echo "<p><span class=\"symbol dot\">●</span> Champs obligatoires</p>\n";


$form = stc_form('post', 'propose.php', $errors);
stc_form_hidden ($form, "type", $type);
if (($mode==EDIT)||($mode==UPDATE)) stc_form_hidden($form, "offreid", $offreid);

stc_form_select ($form, "Catégories <span class=\"symbol dot\">●</span>", "categories", $categories, "liste_categories",
		 array("multi" => true, "width" => $width, "help" => "vous pouvez en ajouter autant que nécessaire"));		 
echo "<br/>\n";

stc_form_text ($form, "Sujet du stage <span class=\"symbol dot\">●</span>", "sujet", $sujet, $width);
stc_form_textarea ($form, "Description <span class=\"symbol dot\">●</span><br/><span id=\"counter\"></span>", 
		   "description", $description, $width, "200pt",
		   "$MAX_CHARS signes maximum<br/>Si nécessaire, plus d'informations peuvent être données dans une page ".
		   "web dédiée (champ suivant)");
stc_script_add('/js/propose.js.php',-1);
echo "<br/>\n";

stc_form_text ($form, "Page web du projet", "url", $url, $width);
echo "<br/>\n";
stc_form_select ($form, "Nature du travail <span class=\"symbol dot\">●</span>", "nature_stage", $nature_stage, "liste_nature_stage",
		 array("multi" => true, "width" => $width));
echo "<br/>\n";

stc_form_text ($form, "Prérequis", "prerequis", $prerequis, $width, null,
	       "Compétences ou connaissances spécifiques nécessaires");
echo "<br/>\n";

stc_form_text ($form, "Informations complémentaires", "infoscmpl", $infoscmpl, $width, null,
	       "Déplacement prévu en conférence ou dans d'autres laboratoires, stage en co-direction (...)");
echo "<br/>\n";

stc_form_date ($form, "Date indicative de début", "start_date", $start_date);
stc_form_text ($form, "Durée du stage", "length", $length, $width);
echo "<br/>\n";

stc_form_text ($form, "Nom du co-encadrant", "co_encadrant", $co_encadrant, $width);
stc_form_text ($form, "Email du co-encadrant", "co_enc_email", $co_enc_email, $width);
echo "<br/>\n";

stc_form_select ($form, "Gratification du stage <span class=\"symbol dot\">●</span>", "pay_state", $pay_state, "liste_pay_states");
//stc_form_select ($form, "Poursuite en thèse possible <span class=\"symbol dot\">●</span>", "thesis", $thesis, array("true" => "oui", "false" => "non"));

echo "<br/>\n";

if (($mode==EDIT)||($mode==UPDATE)) stc_form_button ($form, "Mettre à jour", "update_task");
else stc_form_button ($form, "Enregistrer la proposition", "propose_task");
stc_form_end();
stc_footer();
?>
