<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

/* nettoyage du referer */

$referer = stc_check_referer();
if ($referer == False) stc_reject();

/* test des entrées */

$login = stc_get_variable ($_POST, 'user');
$passwd = stc_get_variable ($_POST, 'password');

if (strcmp($_SERVER['REQUEST_METHOD'],'POST')==0) {
  $r = stc_user_login($login, $passwd);
  $options=array();
  switch ($r[0]) {
  case -2: 
    unset($_SESSION['loginerr']); 
    break;
  case -1: 
    $_SESSION['loginerr']="Compte bloqué"; 
    break;
  case  0: 
    $_SESSION['loginerr']="Nom d'utilisateur ou mot de passse erroné";
    break;
  default: 
    unset($_SESSION['loginerr']); 
    error_log(print_r($r,1));
    $_SESSION['userid'] = $r[0]; 
    $_SESSION['admin'] = $r[1];
  }
}
header('Location: '.$referer);

?>