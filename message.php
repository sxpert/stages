<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

$user = stc_user_id();
$admin = stc_is_admin();
$msg = 0+trim(stc_get_variable ($_REQUEST, 'id'));

stc_style_add("/css/messages.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

/* véfifier si on peut afficher le contenu du message */

function can_display () {
  global $db, $msg, $user, $admin;

  if ($msg==0) return false;
  if (!$admin) return false;
  $res = pg_query_params($db, "select m.id_m2, (u.f_name || ' ' || u.l_name) as user, u.email, m.subject, m.message ".
			 "from messages m, users_view u where m.id=$1 and m.sender=u.id;", array($msg));
  if (pg_num_rows($res)!=1) return false;
  $row = pg_fetch_assoc($res);
  if ($admin===true) return $row;
  if ($row['id_m2'] == $admin) return $row;
  return false;
}

if ($row = can_display()) {
  
  echo "<div>Envoyeur: <a href=\"mailto:".$row['user']."&lt;".$row['email']."&gt;\" id=\"mail\">".$row['user']."</a></div>\n";
  echo "<div id=\"subject\">Sujet: ".$row['subject']."</div>\n";
  $lines = explode("\n", $row['message']);
  $message = implode("<br/>\n",$lines);
  echo "<div id=\"message\">".$message."</div>\n";
  pg_query_params($db, "update messages set msgread=true where id=$1;", array($msg));
} else
  echo 'Affichage interdit';

stc_footer();
?>