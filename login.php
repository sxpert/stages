<?php

require_once('lib/stc.php');

/* nettoyage du referer */

$referer = $_SERVER['HTTP_REFERER'];

/* test des entrées */

$login = stc_get_variable ($_POST, 'user');
$passwd = stc_get_variable ($_POST, 'password');

// TODO: mettre loginerr dans la session instead ;-)


if (strcmp($_SERVER['REQUEST_METHOD'],'POST')==0) {
  $result = stc_user_login($login, $passwd);
  $options=array();
  if ($result==-1) $_SESSION['loginerr']="Compte bloqué";
  else if ($result==0) $_SESSION['loginerr']="Nom d'utilisateur ou mot de passse erroné";
  else {
    unset($_SESSION['loginerr']);
    $_SESSION['userid'] = $result;
  }
}
header('Location: '.$referer);

?>