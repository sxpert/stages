<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');


$errors = array();

$action = stc_get_variable ($_POST, 'action');

$valid_login = stc_get_variable ($_POST, 'valid_login');
$valid_pass = stc_get_variable ($_POST, 'valid_pass');
$lost_login = stc_get_variable ($_POST, 'lost_login');
$lost_email = stc_get_variable ($_POST, 'lost_email');

switch ($action) {
case 'send_validation_email':
  if (($valid_login!='')and($valid_pass!='')) {
    $res = stc_user_resend_email($valid_login, $valid_pass);
    if ($res>0) {
      stc_top();
      stc_menu(stc_default_menu(array()));
      echo "Le mail de validation a été renvoyé";
      stc_footer();
      exit(0);
    } else {
      /* probleme */
    }
  }
  break;
case 'lost_password':
  $login = '';
  $email = '';
  $message = '';
  if ($lost_login!='') {
    // find if login exists
    $sql = 'select email from users_view where login=$1;';
    $res = pg_query_params ($db, $sql, array(trim($lost_login)));
    if (pg_num_rows($res)==1) {
      $login = trim($lost_login);
      $row = pg_fetch_assoc($res);
      $email = $row['email'];
      $message = 'Un mail a été envoyé à l\'adresse enregistrée pour l\'utilisateur '.$lost_login;
    }
    pg_free_result ($res);
  }
  if (($email=='')&&($lost_email!='')) {
    // find if email exists
    $sql = 'select login from users_view where lower(email)=lower($1);';
    $res = pg_query_params ($db, $sql, array(trim($lost_email)));
    if (pg_num_rows($res) == 1) {
      $email = trim($lost_email);
      $row = pg_fetch_assoc($res);
      $login = $row['login'];
      $message = 'Un mail a été envoyé à l\'adresse '.$lost_email;
    }
  }
  if ($email=='') {
    stc_form_add_error($errors, 'lost', "Il faut spécifier un nom d'utilisateur ou une adresse mail");
  } else {
    stc_send_lost_password_email($login, $email);
    stc_top();
    stc_menu(stc_default_menu(array()));
    echo $message;
    stc_footer();
    exit(0);
  } 
  break;
default :
  
}


stc_top();

$options=array();
$options['login']=false;
$options['register']=false;
$options['access']=false;
$options['home']=false;
$menu = stc_default_menu($options);
stc_menu($menu);

// generic error

//
//
//

echo "<h2>Probleme de mail de confirmation</h2>";
echo "<p>Si vous n'avez pas reçu le mail de confirmation, remplissez le formulaire ci-dessous</p>\n";

$form = stc_form("post", "account-access.php", $errors, 'valid');
stc_form_text($form, "Nom d'utilisateur", "valid_login", $valid_login);
stc_form_password($form, "Mot de passe", "valid_pass", $valid_pass);
stc_form_button($form, "Envoyer le mail de validation", "send_validation_email");
stc_form_end();
echo "<hr/>\n";

echo "<h2>Perte de mot de passe</h2>\n";

echo "<p>Indiquer soit le nom d'utilisateur, soit l'email</p>\n";

$form = stc_form('post', 'account-access.php', $errors, 'lost');
stc_form_text($form, 'Nom d\'utilisateur', 'lost_login', $lost_login);
stc_form_text($form, 'Adresse mail', 'lost_email', $lost_email);
stc_form_button($form, 'J\'ai perdu mon mot de passe', 'lost_password');
stc_form_end();
echo "<hr/>\n";

//
//
//

echo "<p>Pour tout autre probleme, envoyez un mail à stages-masters-astro [@] osug [point] fr</p>\n";

stc_footer();

?>
