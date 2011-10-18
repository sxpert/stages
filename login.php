<?php

require_once('lib/stc.php');

/* nettoyage du referer */

$referer = stc_check_referer();
if ($referer == False) stc_reject();

/* test des entrées */

$login = stc_get_variable ($_POST, 'user');
$passwd = stc_get_variable ($_POST, 'password');

if (strcmp($_SERVER['REQUEST_METHOD'],'POST')==0) {
  $result = stc_user_login($login, $passwd);
  $options=array();
  switch ($result) {
  case -2: unset($_SESSION['loginerr']); break;
  case -1: $_SESSION['loginerr']="Compte bloqué"; break;
  case  0: $_SESSION['loginerr']="Nom d'utilisateur ou mot de passse erroné";break;
  default: unset($_SESSION['loginerr']); $_SESSION['userid'] = $result;
  }
}
header('Location: '.$referer);

?>