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
$errors = array();
$width="550px";

$m2 =  trim(stc_get_variable ($_REQUEST, 'id'));
$type = trim(stc_get_variable ($_REQUEST, 'type'));

function _error_msg() {
  echo "<h1>Une erreur est survenue</h1>\n";
  echo "<h2>Dépot de message impossible</h2>\n";
  echo "<div><a href=\"/liste-contacts.php\">retourner à la liste des entités</a></div>\n";
}

function contact_error() {
  stc_style_add("/css/detail.css");
  stc_top();
  $menu = stc_default_menu();
  stc_menu($menu);
  _error_msg();
  stc_footer();
  exit(0);
}

switch ($type) {
case 'admin':
  break;
case 'm2':
  if (strlen($m2)>0) 
    break;
default:
  contact_error();
}

stc_style_add("/css/detail.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if ($user>0) {
  if ($type=='m2') {
    $sql = "select id, short_desc, description, ville from m2 where id=$1;";
    $m2s = pg_query_params($db, $sql, array($m2));
  }
  if (($type=='admin')||(($type=='m2')&&(pg_num_rows($m2s)==1))) {
    $subject = stc_get_variable($_POST, 'subject');
    $message = stc_get_variable($_POST, 'message');
    
    $action = stc_get_variable($_REQUEST, 'action');
    
    if ($action == 'send_message') {
      /* check if $m2 exists */
      if ($type=='admin') 
	      $m2 = null;
      if ($type=='m2') {
        $res = pg_query_params($db,"select id from m2 where id=$1",array($m2));
        if (pg_num_rows($res)!=1) {
          pg_free_result ($res);
          _error_msg();
          stc_footer();
          exit(0);
        }
        pg_free_result ($res);
      }

      $subject = trim($subject);
      if (strlen($subject)==0)
      	stc_form_add_error($errors, 'subject', 'Il faut un contenu au sujet');

      if (count($errors)==0) {

        #
        # post message to database
        #

        $sql = "insert into messages (id_m2, sender, subject, message) values ($1,$2,$3,$4);";
        $res = pg_query_params($db, $sql, array($m2, $user, $subject, $message));
        pg_free_result ($res);
        echo "<h1>Le message a été envoyé</h1>\n";
        echo "<div><a href=\"/\">retour à l'accueil</a></div>\n";
        stc_footer();

        #
        # send emails
        #

        

        exit(0);
      }
    }
    
    if ($type=='admin') 
      echo "<h1>Contacter l'administrateur du site</h1>\n";      
    if ($type=='m2') {
      $m2 = pg_fetch_assoc($m2s);
      echo "<h1>Contacter un Master 2 Recherche</h1>\n";
      /* récupérer la dénomination du master 2 */
      echo "<h2>".$m2['short_desc']." - ".$m2['description']." (".$m2["ville"].")</h2>\n";
    } 
    $form = stc_form ("post", "contact.php", $errors);
    stc_form_hidden ($form, 'type', $type);
    if ($type=='m2')
      stc_form_hidden ($form, "id", $m2['id']);
    stc_form_text ($form, "Sujet", "subject", $subject);   
    stc_form_textarea ($form, "Message", "message", $message, $width, "200pt");
    stc_form_button ($form, "Envoyer le message", "send_message");
    stc_form_end ();
  } else
    _error_msg();
} else 
  echo "Affichage interdit";

stc_footer();

?>