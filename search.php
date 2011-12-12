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
$projmgr = intval(stc_get_variable($_REQUEST,'projmgr'));
$type = stc_get_variable ($_GET,'type');
$notvalid = intval(stc_get_variable($_REQUEST,'notvalid'));

stc_top(array("/css/liste.css"));
$menu = stc_default_menu();
stc_menu($menu);

$outer_select=false;
$outer_where = array();

if ($user==0) {
  /* utilisateur non loggué */
  if ($from==0) { 
    /* utilisateur ne provenant pas d'une M2 */
    echo "Affichage non autorisé";
    stc_footer();
    exit(0);
  } else {
    $select = array("offres.id","offres.sujet","laboratoires.sigle as labo","laboratoires.city as ville");
    $tables = array("offres","offres_m2","users_view","laboratoires");
    $where  = "offres.year_value=$1 and offres.id = offres_m2.id_offre and id_m2=$2 and ".
      "offres.id_project_mgr = users_view.id and users_view.id_laboratoire = laboratoires.id";
    $arr    = array(stc_calc_year(), $from);
  }
} else {
  if ($admin&&(!$projmgr)) {
    $select = array("offres.id","offres.sujet","laboratoires.sigle as labo","laboratoires.city as ville");
    $tables = array("offres","users_view","laboratoires");
    $where  ="offres.year_value=$1 and ".
      "offres.id_project_mgr = users_view.id and users_view.id_laboratoire = laboratoires.id";
    $arr    = array(stc_calc_year());
  } else {
    $select = array("offres.id","offres.sujet");
    $tables = array("offres");
    $where  = "offres.year_value=$1 and offres.id_project_mgr = $2";
    $arr    = array(stc_calc_year(), $user);
  }
}
 
/****
 * formulaires de filtrage
 */


$categories     = stc_get_variable($_POST, 'categories');
$categories_op  = stc_get_variable($_POST, 'categories_op');
$nature_stage   = stc_get_variable($_POST, 'nature_stage');
$nature_op      = stc_get_variable($_POST, 'nature_op');
$labo           = stc_get_variable($_POST, 'labo');
$ville          = stc_get_variable($_POST, 'ville');
$keywords       = stc_get_variable($_POST, 'keywords');
if (!is_array($categories)) $categories = null;

$distinct = false;

function append_value(&$arr, $value) {
  array_push($arr, $value);
  return count($arr);
}

if ($projmgr) {
  $where.=" and offres.id_project_mgr=$".append_value($arr, $projmgr);
}

$categories = stc_form_clean_multi($categories);
if (is_array($categories) and (count($categories)>0)) {
  $outer_select=true;
  array_push($select, "array(select id_categorie from offres_categories where offres_categories.id_offre=offres.id) as categories");
  // 1 = ANY (categories) and 2 = ANY (categories) ;
  $w = array();
  foreach($categories as $c)
    array_push($w, "$".append_value($arr, $c)." = any ( categories )");
  array_push($outer_where,"( ".implode(" ".$categories_op." ",$w)." )");
}

$nature_stage = stc_form_clean_multi($nature_stage);
if (is_array($nature_stage) and (count($nature_stage)>0)) {
  $outer_select=true;
  array_push($select, "array(select id_nature_stage from offres_nature_stage where offres_nature_stage.id_offre=offres.id) ".
	     "as nature_stage");
  $w = array();
  foreach($nature_stage as $n)
    array_push($w, "$".append_value($arr, $n)." = any ( nature_stage)");
  array_push($outer_where, "( ".implode(" ".$nature_op." ",$w)." )");
}

$labo=intval($labo);
if ($labo>0) {
  if (!in_array('users_view',$tables)) array_push($tables, 'users_view');
  $where.=" and offres.id_project_mgr=users_view.id and users_view.id_laboratoire=$".append_value($arr, $labo);
  
}

$ville=trim($ville);
if (strlen($ville)>0) {
  if ($labo==0) { 
    if (!in_array('users_view', $tables)) array_push($tables, 'users_view');
    $where.=" and offres.id_project_mgr=users_view.id";
  }
  if (!in_array('laboratoires', $tables)) array_push($tables, 'laboratoires');
  $where.=" and users_view.id_laboratoire=laboratoires.id and laboratoires.city=$".append_value($arr, $ville);
}

if (($notvalid!=0)&&($admin>0)) {
  $where.=' and offres.id not in (select id_offre from offres_m2 where offres_m2.id_m2=$'.append_value($arr, $admin).")";
}

/****
 * TODO: gérer l'opérateur "ou" ? opérateurs sélectionnés par l'utilisateur ?
 * expressions ?
 */
$keywords=trim($keywords);
if (strlen($keywords)>0) {
  $words = explode(' ',$keywords);
  $vector = implode(' & ',$words);
  error_log('keywords => '.$vector);
  $where.=" and fulltext @@ to_tsquery('french', $".array_push($arr,$vector).")";
}

$sql = 
  ($outer_select?"select * from ( ":"").
  "select "." ".implode(',',$select).
  " from ".implode(',',$tables).
  " where ".$where.
  ($outer_select?(" ) as offres where ".implode(" and ",$outer_where)):"").
  ";";

$width="400pt";

/****
 * Options de recherche
 * Affichées si on est pas en mode "manager de projet" 
 * (on ne cherche pas dans ses propres offres)
 */
if (!$projmgr) {
  echo "<h1>Options de recherche</h1>\n";
  $form = stc_form("POST", "search.php", null);
  stc_form_hidden($form, 'projmgr', $projmgr);
  stc_form_select ($form, "Catégories", "categories", $categories, "liste_categories",
		   array("multi" => true, "width" => $width, 
			 "operator" => array("type" => "radio", 
					     "name" => "categories_op",
					     "value" => $categories_op,
					     "labels" => array("ou", "et"),
					     "values" => array("or", "and")
					     )
			 )
		   );
  stc_form_select ($form, "Nature du travail", "nature_stage", $nature_stage, "liste_nature_stage",
		   array("multi" => true, "width" => $width,
			 "operator" => array("type" => "radio",
					     "name" => "nature_op",
					     "value" => $nature_op,
					     "labels" => array("ou", "et"),
					     "values" => array("or", "and")
					     )
			 )
		   );
  stc_form_select ($form, "Laboratoire", "labo", $labo, "liste_labos", array("width" => $width));
  stc_form_select ($form, "Ville", "ville", $ville, "liste_villes", array("width" => $width));
  stc_form_text ($form, "Mots clé", "keywords", $keywords, $width);
  stc_form_button ($form, "Filtrer", "filter");
  stc_form_end();
  echo "<hr/>\n";
}

/*******************************************************************************
 * 
 *
 *
 */


if (!$projmgr) {
  stc_form("POST", "detail.php", null);
}

/****
 * entêtes
 */
$m2 = array();
echo "<div>";
if (!$projmgr) echo "<span class=\"checkbox\"></span>";
echo "<span class=\"sujet\">Sujet du stage</span>";
if ((($user==0)||($admin))&&(!$projmgr)) {
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

pg_send_query_params($db, $sql, $arr);
$r = pg_get_result($db);
if (pg_result_status($r)!=PGSQL_TUPLES_OK) {
  $dberrmsg = "Error: ".pg_result_error_field($r, PGSQL_DIAG_SQLSTATE)."\n".pg_last_error($db);
  error_log("impossible de récupérer la liste des stages =>".$dberrmsg);
  echo "<div>Erreur d'accès à la base de données</div>\n";
  echo "<pre>".$dberrmsg."</pre>\n";
  stc_footer();
  exit(1);
}
while ($row = pg_fetch_assoc($r)) {
  echo "<a href=\"/detail.php?offreid=".$row['id']."\"";
  if ($odd) echo " class=\"odd\"";
  echo ">";
  if (!$projmgr) {
    echo "<span class=\"checkbox\"><input type=\"checkbox\" name=\"multisel[]\" ".
      "value=\"".$row['id']."\"></span>";
  }
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

if (!$projmgr) {
  /* boutons a la fin */
  echo "<hr/>\n<div class=\"buttons\">";
  echo "<span>";
  echo "<button id=\"select\">Tout sélectionner</button>";
  echo "&nbsp;";
  echo "<button id=\"deselect\">Tout désélectionner</button>";
  echo "</span>";
  echo "<span style=\"float:right;\"><button id=\"print\" name=\"action\" value=\"print\">Impression</button></span>";
  echo "</div>";
  stc_form_end();
  stc_script_add('/js/search.js',-1);
}

if (DEBUG) {
  echo "<tt>".$sql."<br/>\n".print_r($arr,1)."</tt>";
}

stc_footer();
pg_free_result($r);
?>