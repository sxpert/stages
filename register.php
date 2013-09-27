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
$token     = stc_get_variable ($_GET,  'token');
$f_name    = stc_get_variable ($_POST, 'f_name');
$l_name    = stc_get_variable ($_POST, 'l_name');
$email     = stc_get_variable ($_POST, 'email');
$phone     = stc_get_variable ($_POST, 'phone');
$status    = stc_get_variable ($_POST, 'status');
$umr       = stc_get_variable ($_POST, 'umr');
$labo      = stc_get_variable ($_POST, 'labo');
$login     = stc_get_variable ($_POST, 'login');
$login     = trim($login);
$pass1     = stc_get_variable ($_POST, 'pass1');
$pass2     = stc_get_variable ($_POST, 'pass2');

$errors = array();

function stc_clean_umr ($umr) {
  // suppression de tout ce qui n'est pas un chiffre
  $u = '';
  for($i=0;$i<strlen($umr);$i++) {
    $c = $umr[$i];
    if (preg_match('/\d/',$c))
      $u.=$c;
  }
  return intval($u);
}

if (strcmp($_SERVER['REQUEST_METHOD'],"POST")==0) {
	error_log(print_r($_SESSION,1));
	if ($_SESSION['token']!=$token) {
		error_log ('session token invalid');
		stc_reject('tentative de vol de session');
	}

  if (array_key_exists('action', $_POST)) {
    if (strcmp($_POST['action'],'create_account')==0) {
      /* vérifie la cohérence des données entrées */
      /* 2012-10-10 vérification du login vide */
      if (!stc_form_check_phone($phone))
	stc_form_add_error($errors, 'phone', "Le numéro de téléphone contient des caractères invalides");
      if (!stc_form_check_select($status, 'liste_statuts')) 
	stc_form_add_error($errors, 'status', "le statut n'existe pas");
      $umr = stc_clean_umr($umr);
      if (intval($umr)!=intval($labo))
	stc_form_add_error($errors, 'labo', "Incohérence entre numéro d'UMR et laboratoire");
      if (!stc_form_check_select($labo, 'liste_labos'))
	stc_form_add_error($errors, 'labo', "Le laboratoire n'existe pas");
      if (strlen($login)==0) 
	stc_form_add_error($errors, 'login', "le nom d'utilisateur ne doit pas être vide et ne peut contenir d'espaces");
      if (strcmp($pass1,$pass2)!=0)
	stc_form_add_error($errors, 'pass2', "Les deux mots de passe ne correspondent pas");
      else 
	if (!stc_form_check_password($pass1))
	  stc_form_add_error($errors, 'pass1', "Le mot de passe est trop simple");

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
	    stc_send_check_email ($email, $row['hash']);
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
stc_style_add("/css/register.css");
stc_top();

$options = array();
$options['login']=false;
$options['register']=false;
$options['home']=true;
$menu = stc_default_menu($options);

// crées un token de sécurité
$token = bin2hex(openssl_random_pseudo_bytes (32, $strong));
if (!$strong) 
	error_log('register: warning, unable to generate crypto strong random token');
$_SESSION['token'] = $token;

stc_menu($menu);

// formulaire d'enregistrement

$form = stc_form ("post", "register.php?token=".$token, $errors);
stc_form_text ($form, "Prénom", "f_name", $f_name);
stc_form_text ($form, "Nom de Famille", "l_name", $l_name);
stc_form_text ($form, "Adresse email", "email", $email);
stc_form_text ($form, "Téléphone", "phone", $phone, null, null, "exemple: '+33 1 23 45 67 89'");
stc_form_select ($form, "Statut", "status", $status, "liste_statuts");
stc_form_text ($form, "Numéro d'unité", "umr", $umr);
stc_form_select ($form, "Laboratoire", "labo", $labo, "liste_labos", 
		 array("onchange"=>"javascript:update_adresse_labo('labo')", "width" => "400pt",
		       "help" => "Si votre laboratoire n'apparaît pas ou si vous ne connaissez pas votre numéro d'unité, contactez l'assistance"));
echo "<br/>\n";
stc_form_text ($form, "Nom d'utilisateur", "login", $login);
stc_form_password ($form, "Mot de passe", "pass1", $pass1, 
		   "le mot de passe doit faire au moins 8 caractères de long, et ne pas contenir d'espaces");
stc_form_password ($form, "Mot de passe", "pass2", $pass2);
stc_form_button ($form, "Créer mon compte", "create_account");
stc_form_end ();
stc_script_add('/js/register.js',-1);
stc_script_add("register_init();",'window.onload');

stc_footer();

?>
