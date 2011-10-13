<?php

require_once('lib/stc.php');

/****
 *
 * test des entrées
 *
 */

$login = stc_get_variable ($_POST, 'user');
$passwd = stc_get_variable ($_POST, 'password');

if (strcmp($_SERVER['REQUEST_METHOD'],'POST')==0) {
  $result = stc_user_login($login, $passwd);
  $options=array();
  if ($result==-1) $options['loginerr']="Compte bloqué";
  else if ($result==0) $options['loginerr']="Nom d'utilisateur ou mot de passse erroné";
  else {
    $_SESSION['userid'] = $result;
    header('Location: /');
    exit();
  }
} else $options=null;
stc_top();
$menu = stc_default_menu($options);
stc_menu($menu);
stc_footer();

?>