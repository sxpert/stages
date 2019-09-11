<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

// le referer c'est pas fiable, trouver autre chose
/* nettoyage du referer */

$referer = stc_check_referer('login');
//if ($referer == False) stc_reject();
error_log ('login - ref='.$referer);

/* test des entrées */

$login = stc_get_variable ($_POST, 'user');
$login = trim($login);
$passwd = stc_get_variable ($_POST, 'password');

if (strcmp($_SERVER['REQUEST_METHOD'],'POST')==0) {
  $user = stc_user_login($login, $passwd);
  switch ($user) {
  case -2: 
    unset($_SESSION['loginerr']); 
    break;
  case -1: 
    $_SESSION['loginerr']="Compte bloqué"; 
    # envoi de mails
    $dba = db_connect_adm();
    $res = pg_query_params($dba, "select f_name, l_name, email from users where login=$1;", array($login));
    $row = pg_fetch_assoc($res);
    pg_close($dba);
    error_log("ACCOUNT LOCKED ".print_r($row));

    break;
  case  0: 
    $_SESSION['loginerr']="Nom d'utilisateur ou mot de passse erroné";
    break;
  default: 
    unset($_SESSION['loginerr']); 
    $_SESSION['userid'] = $user; 
  }
}
if ($referer===false)
	header ('Location: /index.php');
else
	header('Location: '.$referer);

?>
