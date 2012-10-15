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
if (strlen($m2)==0) $m2 = null;
$type = trim(stc_get_variable ($_REQUEST, 'type'));

function _error_msg() {
  echo "<h1>Une erreur est survenue</h1>\n";
  echo "<h2>Génération de la liste des message impossible</h2>\n";
  echo "<div><a href=\"/\">retourner à l'accueil</a></div>\n";
}

function _error_exit () {
  _error_msg();
  stc_footer();
  exit(0);
}

function messages_error() {
  stc_style_add("/css/detail.css");
  stc_top();
  $menu = stc_default_menu();
  stc_menu($menu);
  _error_exit();
}

$m2name = '';

switch ($type) {
case 'admin':
  break;
case 'm2':
  if (!is_null($m2)) {
    $res = pg_query_params($db, "select (short_desc || ' (' || ville || ')') as m2name from m2 where id=$1;", array($m2));
    if (pg_num_rows($res)!=1) message_error();
    $row = pg_fetch_assoc($res);
    $m2name = $row['m2name'];
  }
  break;  
default:
  messages_error();
}

stc_style_add("/css/messages.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if (($user>0)&&($admin)) {
  /* afficher la liste des messages */
  if (($type=='admin')&&($admin===true)) {
    $rlist = pg_query($db, 
		      "select m.msgread, m.id, date_trunc('second', m.datepub) as datepub, ".
		      "(u.f_name || ' ' || u.l_name || ' (' || l.sigle || ')') as sender, m.subject ".
		      "from messages as m, users_view as u, laboratoires as l  where m.id_m2 is null and m.sender=u.id ".
		      "and u.id_laboratoire = l.id order by datepub desc;");
    $showm2 = false;
  } else {    
    $showm2name = true;
    if (is_null($m2)) {
      $showm2name = false;
      if ($admin===true) $showm2 = true;
      else _error_exit();
    } else {
      if (($admin===true)||($admin==$m2)) $showm2 = false;
      else _error_exit();
      if ($admin===true) $showm2name = false;
    }
    $sql = "select m.msgread, m.id, date_trunc('second', m.datepub) as datepub, ";
    if ($showm2) $sql.="(m2.short_desc || ' (' || m2.ville || ')') as m2, ";
    $sql.="(u.f_name || ' ' || u.l_name || ' (' || l.sigle || ')') as sender, m.subject ";
    $sql.="from messages as m, users_view as u, laboratoires as l ";
    if ($showm2) $sql.=", m2 ";
    $sql.=" where ";
    if ($showm2) $sql.="m.id_m2 is not null and m.id_m2 = m2.id ";
    else $sql.="m.id_m2=$1 ";
    $sql.="and m.sender=u.id and u.id_laboratoire = l.id order by ";
    if ($showm2) $sql.="m2.short_desc, ";
    $sql.="datepub desc;";
    error_log ($sql);
    if ($showm2) $rlist = pg_query ($db, $sql);
    else $rlist = pg_query_params($db, $sql, array($m2));
  }
  /* titre du m2 */
  switch ($type) {
  case 'admin':
    echo "<h1>Messages pour l'administrateur du site</h1>\n";
    break;
  case 'm2':
    echo "<h1>Messages pour les gestionnaires ";
    if ($showm2name)
      /* récupere le titre du M2 dans la bdd */
      echo "du M2R ".$m2name;
    else
      echo "des M2R";
    echo "</h1>\n";
    break;
  }
  
  if (pg_num_rows($rlist)==0) {
    echo "<div id=\"empty\">Aucun message</div>\n";
    stc_footer();
    exit(0);
  }

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
    if ($showm2) echo "<span class=\"m2\">".$row['m2']."</span>";
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