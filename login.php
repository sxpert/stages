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
    $admin_mail = stc_config_get ('ADMIN_EMAIL', $default=null);
    $dba = db_connect_adm();
    $res = pg_query_params($dba, "select f_name, l_name, email from users where login=$1;", array($login));
    $row = pg_fetch_assoc($res);
    pg_close($dba);
    $f_name = $row['f_name'];
    $l_name = $row['l_name'];
    $email = $row['email'];
    error_log("ACCOUNT LOCKED ".print_r($row));
    error_log("sending mail to ".$f_name.' '.$l_name.' <'.$email.'>');
    $subject = "[Stages Masters M2] Votre compte est bloqué";
    $message  = "Bonjour ".$f_name.' '.$l_name.",\r\n\r\n";
    $message .= "Votre compte '".$login."' est bloqué suite a de multiples tentatives de connections infructueuses\r\n\r\n";
    $message .= "Si vous n'êtes pas a l'origine de ces connections, contactez nous à ".$admin_mail.".\r\n"; 
    error_log($subject);
    error_log($message);
    stc_send_email ($email, $subject, $message);

    $subject = "Le compte ".$login." est bloqué";
    $message = "Mail de contact : ".$email."\r\n\r\nMerci de le débloquer.\r\n";
    stc_send_email ($admin_mail, $subject, $message);

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
