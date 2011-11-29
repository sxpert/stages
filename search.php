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
$from = stc_from();
$type = stc_get_variable ($_GET,'type');

stc_top(array("/css/liste.css"));
$menu = stc_default_menu();
stc_menu($menu);

if ($user==0) {
  /* utilisateur non loggué */
  if ($from==0) { 
    /* utilisateur ne provenant pas d'une M2 */
    echo "Affichage non autorisé";
    stc_footer();
    exit(0);
  } else {
    $sql = "select offres.id, offres.sujet, laboratoires.sigle as labo, laboratoires.city as ville ".
      "from offres, offres_m2, users_view, laboratoires ".
      "where offres.year_value=$1 and offres.id = offres_m2.id_offre and id_m2=$2 and ".
      "offres.id_project_mgr = users_view.id and users_view.id_laboratoire = laboratoires.id";
    $arr = array(stc_calc_year(), $from);
  }
} else {
  if ($admin) {
    $sql = "select offres.id, offres.sujet, laboratoires.sigle as labo, laboratoires.city as ville ".
      "from offres, users_view, laboratoires ".
      "where offres.year_value=$1 and ".
      "offres.id_project_mgr = users_view.id and users_view.id_laboratoire = laboratoires.id";
    $arr = array(stc_calc_year());
  } else {
    $sql = "select offres.id, offres.sujet ".
      "from  offres ".
      "where offres.year_value=$1 and offres.id_project_mgr = $2";
    $arr = array(stc_calc_year(), $user);
  }
}
 
/****
 * formulaires de filtrage
 */

/****
 * entêtes
 */
$m2 = array();
echo "<div>";
echo "<span class=\"sujet\">Sujet du stage</span>";
if (($user==0)||($admin)) {
  echo "<span class=\"labo\">Labo</span>";
  echo "<span class=\"ville\">Ville</span>";
}
if ($user!=0) {
  /* lister les m2 */
  $r=pg_query($db, "select id, short_desc from m2 order by id;");
  while ($row = pg_fetch_assoc($r)) {
    array_push($m2, intval($row['id']));
    echo "<span class=\"m2\">".$row['short_desc']."</span>";
  }
  pg_free_result($r);
}
echo "</div>";

/****
 * lignes
 */
$odd = 1;
$r = pg_query_params($db, $sql, $arr);
while ($row = pg_fetch_assoc($r)) {
  echo "<a href=\"/detail.php?offreid=".$row['id']."\"";
  if ($odd) echo " class=\"odd\"";
  echo ">";
  echo "<span class=\"sujet\">".$row['sujet']."</span>";
  if (array_key_exists('labo', $row))
    echo "<span class=\"labo\">".$row['labo']."</span>";
  if (array_key_exists('ville', $row))
    echo "<span class=\"ville\">".$row['ville']."</span>";
  /* si on a un utilisateur loggué, on montre les M2 */
  if ($user!=0) {
    $rm2 = pg_query_params($db, "select id_m2 from offres_m2 where id_offre=$1", array($row['id']));
    $cur = 0;
    error_log(print_r($m2,1));
    for($i=0;$i<count($m2);$i++) {   
      if ($cur==0) {
	$row = pg_fetch_assoc($rm2);
	$cur = intval($row['id_m2']);
      }
      
      error_log($i.' => '.$cur.' - '.$m2[$i]);
      if ($cur==$m2[$i]) {
	echo "<span class=\"m2\">ok</span>";
	$cur=0;
      } else echo "<span class=\"m2\">&nbsp;</span>";
    }
    pg_free_result($rm2);
  }
  echo "</a>\n";
  $odd = ($odd+1)%2;
}
pg_free_result($r);

stc_footer();
?>