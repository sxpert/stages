<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

$login = trim(stc_get_variable ($_POST, 'login'));
$pass = trim(stc_get_variable ($_POST, 'pass'));

$errors = array();

if (($login!='')and($pass!='')) {
  $res = stc_user_resend_email($login, $pass);
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
stc_top();

$options=array();
$options['login']=false;
$options['register']=false;
$options['access']=false;
$options['home']=true;
$menu = stc_default_menu($options);
stc_menu($menu);
echo "<h2>Probleme de mail de confirmation</h2>";
echo "<p>Si vous n'avez pas reçu le mail de confirmation, remplissez le formulaire ci-dessous</p>\n";

$form = stc_form("post", "account-access.php", $errors);
stc_form_text($form, "Nom d'utilisateur", "login", $login);
stc_form_password($form, "Mot de passe", "pass", $pass);
stc_form_button($form, "Envoyer le mail de validation", "send_validation_email");
stc_form_end();

echo "<hr/>\n";
echo "<h2>Changement de mot de passe</h2>\n";

echo "<hr/>\n";
echo "<h2>Perte de mot de passe</h2>\n";
echo 

echo "<hr/>\n
echo "<p>Pour tout autre probleme, envoyez un mail à raphael [point] jacquot [@] obs [point] ujf-grenoble [point] fr</p>\n";



stc_footer();

?>