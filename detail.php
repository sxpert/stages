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

$offre_id = intval(stc_get_variable ($_GET,'offreid'));

stc_top(array("/css/detail.css"));
$menu = stc_default_menu();
stc_menu($menu);

if ($user==0) {
  /* utilisateur non loggué */
  if ($from==0) {
    /* utilisateur ne provenant ni d'une M2, ni loggué */
    echo "Affichage non autorisé";
    stc_footer();
    exit(0);
  } else {
    /* l'utilisateur est un étudiant qui vient d'une M2 particulière */
    $sql = "select * from offres_m2 where id_offre = $1 and id_m2 = $2;";
    $r = pg_query_params($db, $sql, array($offre_id, $from));
    if (pg_num_rows($r)!=1) {
      echo "Offre non disponible pour votre M2";
      stc_footer();
      exit(0);
    }
  }
}

/**
 * displays a section detail
 */
function stc_detail_section ($title) {
  echo "<h1>$title</h1>\n";
}

function stc_detail_subsection ($title) {
  echo "<h2>$title</h2>\n";
}

/**
 * displays detail information
 * defaults to text
 */
function stc_detail_display ($value) {
  echo "<div class=\"detail\">";
  echo $value;
  echo "</div>\n";
}

/****
 *
 * Informations standard
 *
 */
stc_detail_section ("Informations sur le stage");

$sql = "select description from offres_categories, categories ".
  "where id_categorie = id and id_offre = $1 order by id;";
$r = pg_query_params($db, $sql, array($offre_id));
if (pg_num_rows($r)==0) {
  /* pas de catégories ???? */
}
stc_detail_subsection("Catégories");
while ($row=pg_fetch_assoc($r)) {
  stc_detail_display($row['description']);
}
pg_free_result($r);

$sql = "select * from offres where id=$1";
$r = pg_query_params($db, $sql, array($offre_id));
if (pg_num_rows($r)==0) {
  /* l'offre a disparue ??? */
}
$offre = pg_fetch_assoc($r);
pg_free_result ($r);

/* description du stage */

stc_detail_subsection ("Sujet du stage");
stc_detail_display ($offre['sujet']);
stc_detail_subsection ("Description");
stc_detail_display ($offre['description']);
stc_detail_subsection ("Plus d'informations");
stc_detail_display ($offre['project_url']);

/* nature du travail */
stc_detail_subsection ("Nature du travail demandé");
$sql = "select description from nature_stage, offres_nature_stage ".
  "where id_offre = $1 and id = id_nature_stage order by id;";
$r = pg_query_params($db, $sql, array($offre_id));
while ($row=pg_fetch_assoc($r)) {
  stc_detail_display($row['description']);
}
pg_free_result ($r);

/* prérequis */
stc_detail_subsection ("Pré-requis");
stc_detail_display ($offre['prerequis']);

/****
 *
 * informations complémentaires 
 *
 */
stc_detail_section ("Informations complémentaires");

/* récupération des informations sur l'utilisateur */
$sql = "select * from users_view where id = $1;";
$r = pg_query_params ($db, $sql, array($offre['id_project_mgr']));
$user = pg_fetch_assoc($r);
pg_free_result ($r); 
$sql = "select description from statuts where id=$1;";
$r = pg_query_params ($db, $sql, array($user['statut']));
$statut = pg_fetch_assoc($r);
pg_free_result ($r);

/* laboratoire */
stc_detail_subsection ("Laboratoire");
$sql = "select * from laboratoires where id=$1;";
$r = pg_query_params ($db, $sql, array($user['id_laboratoire']));
$labo = pg_fetch_assoc($r);
pg_free_result ($r);
stc_detail_display($labo['type_unite'].'-'.$labo['id'].' '.$labo["sigle"]);
stc_detail_display($labo['description']);
stc_detail_display($labo['post_addr']);
stc_detail_display($labo['post_code'].' '.$labo['city']);

stc_detail_subsection("Calendrier prévisionnel");
stc_detail_display('Date initiale estimée : '.$offre['start_date']);
stc_detail_display('Durée proposée : '.$offre['duree']);

stc_detail_subsection("Encadrant");
stc_detail_display($user['f_name'].' '.$user['l_name'].' ('.$statut['description'].')');
stc_detail_display($user['email'].' '.$user['phone']);

if (strlen(trim($offre['co_encadrant']))>0) {
  stc_detail_subsection("Co-encadrant");
  stc_detail_display($offre['co_encadrant']);
  stc_detail_display($offre['co_enc_email']);
}

stc_detail_subsection("Gratification");
$sql = "select description from pay_states where id = $1;";
$r=pg_query_params($db,$sql,array($offre['pay_state']));
$pay_state = pg_fetch_assoc($r);
pg_free_result($r);
stc_detail_display($pay_state['description']);


stc_detail_subsection("Poursuite en thèse possible");
stc_detail_display(($offre['thesis']=='t')?'Oui':'Non');

if ($admin) {
  echo "<hr/>\n";
  
  echo "<div id=\"validate\">";
  $r = pg_query_params($db, 
		       "select id_offre, id_m2, description ".
		       "from offres_m2, m2 ".
		       "where id_m2=id and id_offre=$1 and id_m2=$2",
		       array($offre_id, $admin));
  if (pg_num_rows($r)==1) {
    $row= pg_fetch_assoc($r);
    echo "<span>Offre déjà validée pour la M2 '".$row['description']."'</span>";
  } else {
    pg_free_result($r);
    $r = pg_query_params($db, "select id_project_mgr from offres where id=$1", array($offre_id));
    if (pg_num_rows($r)==0) {
      error_log("detail.php impossible de trouver id_project_mgr pour l'offre ".$offre_id);
    } else {
      $row = pg_fetch_assoc($r);
      if (intval($row['id_project_mgr'])==intval($user)) {
	echo "<span>Vous ne pouvez valider les offres dont vous êtes l'auteur</span>";
      } else {
	$r = pg_query_params($db,
			     "select description from m2 where id=$1",
			     array($admin));
	if (pg_num_rows($r)==1) {
	  $row = pg_fetch_assoc($r);
	  echo "<form method=\"post\" action=\"validate-offre.php\">";
	  echo "<input type=\"hidden\" name=\"offreid\" value=\"".$offre_id."\"/>";
	  echo "<button name=\"action\" value=\"validate\">Valider l'offre pour la M2<br/>";
	  echo "'".$row['description']."'</button>";
	  echo "</form>";
	} else {
	  error_log("Impossible de trouver la M2 correspondant a l'indice ".$admin);
	}
      }
    }
  }
  echo "</div>\n";
  pg_free_result($r);
}

stc_footer();

?>