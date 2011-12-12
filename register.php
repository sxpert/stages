<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

stc_user_logout();

/******************************************************************************
 *
 * test des entrées (si on en a)
 *
 */
$f_name    = stc_get_variable ($_POST, 'f_name');
$l_name    = stc_get_variable ($_POST, 'l_name');
$email     = stc_get_variable ($_POST, 'email');
$phone     = stc_get_variable ($_POST, 'phone');
$status    = stc_get_variable ($_POST, 'status');
$umr       = stc_get_variable ($_POST, 'umr');
$labo      = stc_get_variable ($_POST, 'labo');
$login     = stc_get_variable ($_POST, 'login');
$pass1     = stc_get_variable ($_POST, 'pass1');
$pass2     = stc_get_variable ($_POST, 'pass2');

$errors = array();

if (strcmp($_SERVER['REQUEST_METHOD'],"POST")==0) {
  $referer = stc_check_referer();
  if ($referer == False) stc_reject();
  if (strcmp($referer, $_SERVER['PHP_SELF'])!=0) stc_reject();

  if (array_key_exists('action', $_POST)) {
    if (strcmp($_POST['action'],'create_account')==0) {
      /* vérifie la cohérence des données entrées */
      if (!stc_form_check_phone($phone))
	stc_form_add_error($errors, 'phone', "Le numéro de téléphone contient des caractères invalides");
      if (!stc_form_check_select($status, 'liste_statuts')) 
	stc_form_add_error($errors, 'status', "le statut n'existe pas");
      if (intval($umr)!=intval($labo))
	stc_form_add_error($errors, 'labo', "Incohérence entre numéro d'UMR et laboratoire");
      if (!stc_form_check_select($labo, 'liste_labos'))
	stc_form_add_error($errors, 'labo', "Le laboratoire n'existe pas");
      if (strcmp($pass1,$pass2)!=0)
	stc_form_add_error($errors, 'pass2', "Les deux mots de passe ne correspondent pas");

      if (count($errors)==0) {
	$t_phone = stc_form_clean_phone ($phone);
	// no errors detected, attempt account creation 
	// what can happen here is the login name is not available...
	$res = stc_user_account_create ($f_name, $l_name, $email, $t_phone, $status, $labo, $login, $pass1);
	error_log("[".pg_result_status($res)."] ".pg_result_status($res,PGSQL_STATUS_STRING)." logging user in");
	if (pg_result_status($res)==PGSQL_TUPLES_OK) {
	  // logger l'utilisateur
	  $row = pg_fetch_assoc($res);
	  pg_free_result($res);
	  $id = $row['id'];
	  if ($id>0) {
	    stc_send_email ($email, $row['hash']);
	    // do not log the user, show page that tells to go check his email
	    stc_top();
	    $options = array();
	    $options['register']=False;
	    $menu = stc_default_menu($options);
	    stc_menu($menu);
	    echo "Le compte a été créé avec succès.<br/>";
	    echo "Un courrier a été envoyé a l'adresse que vous avez indiqué, veuillez cliquer sur le lien pour activer le compte.";
	    stc_footer();
	    exit();
	  } else stc_form_add_error($errors, 'login', "Le nom d'utilisateur n'est pas disponible");
	}
      }
    } else
      error_log ("'action' should be 'create_account'"); 
  } else
    error_log ("no 'action' field...");
}


/******************************************************************************
 *
 * Formulaire
 *
 */

stc_top(array("/css/register.css"));

$options = array();
$options['login']=false;
$options['register']=false;
$options['home']=true;
$menu = stc_default_menu($options);

stc_menu($menu);

// formulaire d'enregistrement

$form = stc_form ("post", "register.php", $errors);
stc_form_text ($form, "Prénom", "f_name", $f_name);
stc_form_text ($form, "Nom de Famille", "l_name", $l_name);
stc_form_text ($form, "Adresse email", "email", $email);
stc_form_text ($form, "Téléphone", "phone", $phone);
stc_form_select ($form, "Statut", "status", $status, "liste_statuts");
stc_form_text ($form, "Numéro d'unité", "umr", $umr);
stc_form_select ($form, "Laboratoire", "labo", $labo, "liste_labos", 
		 array("onchange"=>"javascript:update_adresse_labo('labo')"));
echo "<br/>\n";
stc_form_text ($form, "Nom d'utilisateur", "login", $login);
stc_form_password ($form, "Mot de passe", "pass1", $pass1);
stc_form_password ($form, "Mot de passe", "pass2", $pass2);
stc_form_button ($form, "Créer mon compte", "create_account");
stc_form_end ();

stc_footer(array('/js/register.js'));

?>