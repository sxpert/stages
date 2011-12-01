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
  if ($admin) {
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


$projmgr        = intval(stc_get_variable($_REQUEST,'projmgr'));
$categories     = stc_get_variable($_POST, 'categories');
$categories_op  = stc_get_variable($_POST, 'categories_op');
$nature_stage   = stc_get_variable($_POST, 'nature_stage');
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
if (count($categories)>0) {
  $outer_select=true;
  array_push($select, "array(select id_categorie from offres_categories where offres_categories.id_offre=offres.id) as categories");
  // 1 = ANY (categories) and 2 = ANY (categories) ;
  $w = array();
  foreach($categories as $c)
    array_push($w, "$".append_value($arr, $c)." = any ( categories )");
  array_push($outer_where,"( ".implode(" ".$categories_op." ",$w)." )");
}

$sql = 
  ($outer_select?"select * from ( ":"").
  "select "." ".implode(',',$select).
  " from ".implode(',',$tables).
  " where ".$where.
  ($outer_select?(" ) as offres where ".implode(" and ",$outer_where)):"").
  ";";

$width="400pt";

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
		 array("multi" => true, "width" => $width));
stc_form_select ($form, "Laboratoire", "labo", $labo, "liste_labos", array("width" => $width));
stc_form_select ($form, "Ville", "ville", $ville, "liste_villes", array("width" => $width));
stc_form_text ($form, "Mots clé", "keywords", $keywords, $width);
stc_form_button ($form, "Filtrer", "filter");
stc_form_end();
echo "<hr/>\n";

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

echo "<hr/>\n";
echo "<tt>".$sql."</tt><br/>\n";
echo "<tt>".print_r($arr,1)."</tt><br/>\n";
pg_result_seek($r,0);
while($row=pg_fetch_assoc($r)) 
  echo "<tt>".implode(' | ',$row)."</tt><br/>\n";

stc_footer();
pg_free_result($r);
?>