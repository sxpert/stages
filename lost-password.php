<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

$user = trim(stc_get_variable ($_POST, 'user'));
$ticket = trim(stc_get_variable ($_REQUEST, 'hash'));
$pass1 = trim(stc_get_variable ($_REQUEST, 'pass1'));
$pass2 = trim(stc_get_variable ($_REQUEST, 'pass2'));

if (strlen($user)>0) {
} else {
  if (strlen($ticket)>0) {
    if 
  } else {
    /* neither user nor ticket... */
  }
}

?>