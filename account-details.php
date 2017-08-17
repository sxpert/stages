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

$user_id = stc_user_id();

$sql = 'select login, f_name, l_name, email, phone, id_laboratoire from users_view where id=$1';
$r = pg_query_params($db, $sql, array($user_id)); 
$row = pg_fetch_assoc($r);
pg_free_result($r);
$login = $row['login'];

switch ($action) {
case 'modify_account':
  $f_name = stc_get_variable ($_POST, 'f_name');
  $l_name = stc_get_variable ($_POST, 'l_name');
  $email = stc_get_variable ($_POST, 'email');
  $phone = stc_get_variable ($_POST, 'phone');
  $id_laboratoire = stc_get_variable ($_POST, 'id_laboratoire');
	
  if (!stc_form_check_phone($phone))
    stc_form_add_error($errors, 'phone', "Le numéro de téléphone contient des caractères invalides");
  if (!stc_form_check_select($id_laboratoire, 'liste_labos'))
    stc_form_add_error($errors, 'id_laboradoire', "Le laboratoire n'existe pas");

  if (count($errors)==0) {
    $t_phone = stc_form_clean_phone ($phone);

    $sql = 'select * from user_modify ($1, $2, $3, $4, $5, $6) as result;';
    $r = pg_query_params($db, $sql, array($user_id, $f_name, $l_name, $email, $phone, $id_laboratoire));
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

  break;
default:
  $f_name = $row['f_name'];
  $l_name = $row['l_name'];
  $email = $row['email'];
  $phone = $row['phone'];
  $id_laboratoire = $row['id_laboratoire'];
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
stc_form_text($form, "Prénom", 'f_name', $f_name);
stc_form_text($form, "Nom de Famille", 'l_name', $l_name);
stc_form_text($form, "Adresse email", 'email', $email);
stc_form_text($form, "Téléphone", 'phone', $phone);
stc_form_select ($form, "Laboratoire", "id_laboratoire", $id_laboratoire, "liste_labos");
stc_form_button($form, "modifier le compte", "modify_account");
stc_form_end();

stc_footer();

?>
