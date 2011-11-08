<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once ('lib/stc.php');

unset ($_SESSION['userid']);
// on doit avoir une variable "hash" composée d'un nombre en hexa de 16 caracteres de long

$errors = array();

switch ($_SERVER['REQUEST_METHOD']) {
case 'GET':
  $hash = stc_get_variable ($_GET, 'hash');
  $login = '';
  $pass = '';
  break;
case 'POST':
  $hash = stc_get_variable ($_POST, 'hash');
  $login = stc_get_variable ($_POST, 'login');
  $pass = stc_get_variable ($_POST, 'pass');
  $id = stc_user_validate_account($login,$pass,$hash);
  switch ($id) {
  case -1: 
    stc_form_add_error($errors, 'login', 'compte bloqué');
    break;
  case 0:
    stc_form_add_error($errors, 'login', 'Nom d\'utilisateur ou mot de passe erroné');
    break;
  default:
    // on a un id utilisateur. tout s'est donc bien passé
    // on loggue l'utilisateur dans la session
    $_SESSION['userid'] = $id;
    // et on retourne a l'accueil
    header('Location: /');
    exit();
  }
  break;
default :
  // error... (should not happen)
  break;
}

stc_top();
$options = array();
$options['login'] = false;
$options['register']=false;
$options['home'] = true;
$menu = stc_default_menu($options);
stc_menu($menu);

// formulaire 
$form = stc_form("post", "validate-account.php", $errors);
stc_form_hidden($form, "hash", $hash);
stc_form_text($form, "Nom d'utilisateur", "login", $login);
stc_form_password($form, "Mot de passe", "pass", $pass);
stc_form_button($form, "Valider mon compte", "validate_account");
stc_form_end();

stc_footer();

?>
