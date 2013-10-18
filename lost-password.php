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
$token = trim(stc_get_variable ($_REQUEST, 'token'));
$pass1 = stc_get_variable ($_REQUEST, 'pass1');
$pass2 = stc_get_variable ($_REQUEST, 'pass2');
$action = trim(stc_get_variable ($_REQUEST, 'action'));

$errors = array();
if ($action=='change-password') {
	if (strcmp($pass1,$pass2)!=0)
		stc_form_add_error($errors, 'pass2', "Les deux mots de passe ne correspondent pas");
	else
	  if (!stc_form_check_password($pass1))
	    stc_form_add_error($errors, 'pass1', "Le mot de passe est trop simple");

	if (count($errors)==0) {
		$r = stc_user_change_lost_password($login, $pass1, $token);
		switch ($r) {
			// we know which case it is, but do not advertise to the user
			case -1:
			case 0:
				stc_form_add_error($errors, 'token', 'Une erreur est survenue lors de la tentative de changement de mot de passe');
				break;
			case 1:
				// retour a la home page
				header('Location: /');
				exit (0);
		}
	}
}

//

stc_top();

$options=array();
$options['login']=false;
$options['register']=false;
$options['access']=false;
$options['home']=false;
$menu = stc_default_menu($options);
stc_menu($menu);
$form = stc_form("post", "lost-password.php", $errors, 'change-pass');
stc_form_hidden($form, 'token', $token);
stc_form_text($form, "Nom d'utilisateur", "login", $login);
stc_form_password($form, "Nouveau mot de passe", "pass1", $pass1);
stc_form_password($form, "Nouveau mot de passe (controle)", "pass2", $pass2);
stc_form_button($form, 'Changer mon mot de passe', 'change-password');
stc_form_end();
stc_footer();

?>
