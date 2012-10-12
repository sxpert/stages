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

$m2 =  trim(stc_get_variable ($_REQUEST, 'id'));
$type = trim(stc_get_variable ($_REQUEST, 'type'));

function _error_msg() {
  echo "<h1>Une erreur est survenue</h1>\n";
  echo "<h2>Génération de la liste des message impossible</h2>\n";
  echo "<div><a href=\"/\">retourner à l'accueil</a></div>\n";
}

function messages_error() {
  stc_style_add("/css/detail.css");
  stc_top();
  $menu = stc_default_menu();
  stc_menu($menu);
  _error_msg();
  stc_footer();
  exit(0);
}

switch ($type) {
case 'admin':
  break;
case 'm2':
  if (strlen($m2)>0) 
    break;  
default:
  messages_error();
}

stc_style_add("/css/messages.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if (($user>0)&&($admin)) {
  /* titre du m2 */
  switch ($type) {
  case 'admin':
    echo "<h1>Messages pour l'administrateur du site</h1>\n";
    break;
  case 'm2':
    echo "<h1>Messages pour les gestionnaires du M2R";
    echo "</h1>\n";
    break;
  }
  /* afficher la liste des messages */
  if ($admin===true) 
    $rlist = pg_query($db, 
		      "select m.msgread, m.id, date_trunc('minute', m.datepub) as datepub, ".
		      "(u.f_name || ' ' || u.l_name || ' (' || l.sigle || ')') as sender, m.subject ".
		      "from messages as m, users_view as u, laboratoires as l  where m.id_m2 is null and m.sender=u.id ".
		      "and u.id_laboratoire = l.id order by datepub desc;");
  else 
    $rlist = pg_query_params($db, "select id, subject from messages where id_m2=$1 order by datepub desc;", array($admin));
  
  /* en-têtes */
  echo "<div id=\"list\">\n";

  /* messages */
  $odd = 1;
  while ($row = pg_fetch_assoc($rlist)) {
    echo "<div class=\"message";
    if ($row['msgread']=='f') echo " new";
    if ($odd) echo " odd";
    echo "\">";
    echo "<a href=\"message.php?id=".$row['id']."\">";
    echo "<span class=\"date\">".$row['datepub']."</span>";
    echo "<span class=\"sender\">".$row['sender']."</span>";
    echo "<span class=\"subject\">".$row['subject']."</span>";
    echo "</a>";
    echo "</div>";
    $odd = ($odd+1)%2;
  }
  echo "</div>\n";
  
} else
  echo 'Affichage interdit';


stc_footer();
?>