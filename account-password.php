<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

# connection a la base en tant qu'administrateur
# /!\ faire tres attention au code !
$dba = db_connect_adm();

$errors = array();

$action = stc_get_variable ($_POST, 'action');

$user_id = stc_user_id();

if ($user_id == 0) {
  # fail
  stc_fail(403, "Accès interdit");
}

$login_sql = "select login from users where id=$1";
$login_r = pg_query_params($dba, $login_sql, array($user_id));
if ($login_r === FALSE) {
	# fail
	error_log("request failed");
}
$login_row = pg_fetch_object($login_r);
pg_free_result($login_r);
$login = $login_row->login;

switch ($action) {
case 'change_password':
  $passwd1 = stc_get_variable ($_POST, 'passwd1');
  $passwd2 = stc_get_variable ($_POST, 'passwd2');
  if (strcmp($passwd1, $passwd2) != 0)
    stc_form_add_error($errors, 'passwd1', "Les mots de passe entrés ne sont pas identiques");

  if (count($errors) == 0) {
    # on peut procéder au changement de mot de passe
    $sql = "update users set passwd = hash_password($1, generate_salt()), login_fails = 0 where id = $2;";
    $r = pg_query_params($dba, $sql, array($passwd1, $user_id));
    if (is_bool($r) and $r == FALSE)
      stc_fail(500, "Erreur lors du changement de mot de passe");
    # all is well...
    stc_fail(200, "Mot de passe changé avec succes");
  }

  break;
default:
  $passwd1 = '';
  $passwd2 = '';
}

stc_top();

$options=array();
$options['login']=false;
$options['register']=false;
$options['access']=false;
$options['home']=false;
$menu = stc_default_menu($options);
stc_menu($menu);

echo "<h2>Changer le mot de passe du compte '".$login."'</h2>";

$form = stc_form("post", "account-password.php", $errors, 'update');
stc_form_text($form, "Mot de passe", 'passwd1', $passwd1);
stc_form_text($form, "Mod de passe (de nouveau)", 'passwd2', $passwd2);
stc_form_button($form, "Changer le mot de passe", "change_password");
stc_form_end();

stc_footer();

?>
