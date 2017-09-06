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
$admin = stc_is_admin();
$super = is_bool($admin);

if ($super and $admin) {
  $id = stc_get_variable($_GET, 'id');
  # did we get something ?
  if (strlen($id)==0) 
    $id = stc_get_variable($_POST, 'id');
  # test again with the variable from POST
  if (strlen($id)==0)
    # select the admin's account then...
    $id = $user_id;
  if (!is_numeric($id))
    stc_fail(412, "Numéro d'utilisateur '".$id."' invalide");
} else {
  $id = $user_id;
}

$sql = 'select login, f_name, l_name, email, phone, id_laboratoire, super, m2_admin, login_fails from users where id=$1';
$r = pg_query_params($dba, $sql, array($id)); 
$row = pg_fetch_object($r);
pg_free_result($r);
$login = $row->login;

switch ($action) {
case 'unlock_account':
  if ($super and $admin) {
    $sql = "update users set login_fails = 0 where id = $1;";
    $vars = array($id);
    $r = pg_query_params($dba, $sql, $vars);
    if ($r !== false) {
      header('Location: /liste-users.php');
      exit;
    } else 
      stc_fail(500, "Erreur lors du déblocage du compte<br/>".pg_last_error($dba).
	       "<br/><pre>".$sql."\n".var_export($vars, true)."</pre>");
  } else 
    stc_fail(403, "Fonction réservée aux administrateurs");
case 'modify_account':
  $f_name = stc_get_variable ($_POST, 'f_name');
  $l_name = stc_get_variable ($_POST, 'l_name');
  $email = stc_get_variable ($_POST, 'email');
  $phone = stc_get_variable ($_POST, 'phone');
  $id_laboratoire = stc_get_variable ($_POST, 'id_laboratoire');
	
  if (!stc_form_check_phone($phone))
    stc_form_add_error($errors, 'phone', "Le numéro de téléphone contient des caractères invalides");
  if (!stc_form_check_select($id_laboratoire, 'liste_labos'))
    stc_form_add_error($errors, 'id_laboratoire', "Le laboratoire n'existe pas");

  if (count($errors)==0) {
    $t_phone = stc_form_clean_phone ($phone);

    if ($super and $admin) {
      $super_admin = stc_form_clean_checkbox(stc_get_variable($_POST, 'super_admin'))?'t':'f';
      $m2_admin = stc_get_variable($_POST, 'm2_admin');
      $m2_admin = is_numeric($m2_admin)?intval($m2_admin):NULL;
      $sql = 'update users set f_name = $1, l_name = $2, email = $3, phone = $4, id_laboratoire = $5, super = $6, m2_admin = $7 where id = $8;';
      $vars = array($f_name, $l_name, $email, $phone, $id_laboratoire, $super_admin, $m2_admin, $id);
      $r = pg_query_params($dba, $sql, $vars);
      if ($r !== false) {
	header('Location: /liste-users.php');
	exit;
      } else 
	stc_fail(500, "Erreur lors de la modification<br/>".pg_last_error($dba).
		 "<br/><pre>".$sql."\n".var_export($vars, true)."</pre>");
    } else {
      $sql = 'select * from user_modify ($1, $2, $3, $4, $5, $6) as result;';
      $r = pg_query_params($db, $sql, array($id, $f_name, $l_name, $email, $phone, $id_laboratoire));
      $row = pg_fetch_assoc($r);
      pg_free_result($r);
      if ($row['result'] == 't') {
      	stc_top();
    	$menu = stc_default_menu();
    	stc_menu($menu);
        echo "<p>Les données pour le compte '".$login."' ont été modifiées avec succès</p>\n";
    	stc_footer();
    	exit;
      }
    }
  }

  break;
default:
  $f_name = $row->f_name;
  $l_name = $row->l_name;
  $email = $row->email;
  $phone = $row->phone;
  $id_laboratoire = $row->id_laboratoire;
  $super_admin = (strcmp($row->super,'t')==0);
  $m2_admin = $row->m2_admin;
}

stc_top();

$options=array();
$options['login']=false;
$options['register']=false;
$options['access']=false;
$options['home']=false;
$menu = stc_default_menu($options);
stc_menu($menu);

echo "<h2>Détails du compte '".$login."'</h2>";

$form = stc_form("post", "account-details.php", $errors, 'update');
if ($admin and $super and $row->login_fails==3){
  stc_form_button($form, '<span class="locked-account">Débloquer le compte</span>', "unlock_account");
}
stc_form_text($form, "Prénom", 'f_name', $f_name);
stc_form_text($form, "Nom de Famille", 'l_name', $l_name);
stc_form_text($form, "Adresse email", 'email', $email);
stc_form_text($form, "Téléphone", 'phone', $phone);
stc_form_select ($form, "Laboratoire", "id_laboratoire", $id_laboratoire, "liste_labos");
if ($super and $admin) {
  stc_form_hidden ($form, 'id', $id);
  stc_form_checkbox ($form, "SuperAdmin", 'super_admin', $super_admin);
  stc_form_select ($form, "Administrateur M2", 'm2_admin', $m2_admin, 'liste_m2');
}
stc_form_button($form, "modifier le compte", "modify_account");
stc_form_end();

stc_footer();

?>
