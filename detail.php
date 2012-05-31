<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

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
function stc_detail_display ($value, $fieldtype='text') {
  GLOBAL $user, $offre;
  echo "<div class=\"detail\">";
  echo $value;
  echo "</div>\n";
}

/****
 *
 * fonction de mise en forme
 *
 */
function add_br ($s) {
  /* replace "<br/>\n" with "\n" ? */
  $lines = explode("\n", $s);
  return implode("<br/>\n",$lines);
}

/****
 *
 * affiche l'offre
 *
 */

function stc_affiche_offre($id, $multi=false) {
  GLOBAL $db, $user, $admin, $from;

  if ($user==0) {
    /* utilisateur non loggué */
    if ($from==0) {
      /* utilisateur ne provenant ni d'une M2, ni loggué */
      echo "Affichage non autorisé";
      stc_footer();
      return false;
    } else {
      /* l'utilisateur est un étudiant qui vient d'une M2 particulière */
      $sql = "select * from offres_m2 where id_offre = $1 and id_m2 = $2;";
      $r = pg_query_params($db, $sql, array($id, $from));
      if (pg_num_rows($r)!=1) {
	if ($multi) return false;
	echo "Offre non disponible pour votre M2";
	stc_footer();
	return false;
      }
    }
  }

  $sql = "select * from offres where id=$1";
  $r = pg_query_params($db, $sql, array($id));
  if (pg_num_rows($r)==0) {
    /* l'offre a disparue ??? */
  }
  $offre = pg_fetch_assoc($r);
  pg_free_result ($r);
  
  if ((intval($offre['id_project_mgr'])==intval($user)&&(!$multi))||(is_bool($admin)&&$admin)) {
    echo "<div class=\"link\">";
    echo "<a href=\"delete-offer.php?offreid=".$offre['id']."\">";
    echo "Supprimer la proposition";
    echo "</a> | ";
    echo "<a href=\"propose.php?action=edit&offreid=".$offre['id']."\">";
    echo "Modifier l'offre";
    echo "</a></div>\n";
    echo "<hr/>\n";
  }
  
  stc_detail_section ("Informations sur le stage");
  
  $sql = "select description from offres_categories, categories ".
    "where id_categorie = id and id_offre = $1 order by id;";
  $r = pg_query_params($db, $sql, array($id));
  if (pg_num_rows($r)==0) {
    /* pas de catégories ???? */
  }
  stc_detail_subsection("Catégories");
  while ($row=pg_fetch_assoc($r)) {
    stc_detail_display($row['description']);
  }
  pg_free_result($r);
  
  /* description du stage */
  
  stc_detail_subsection ("Sujet du stage");
  stc_detail_display ($offre['sujet']);
  stc_detail_subsection ("Description");
  $desc = add_br($offre['description']);
  stc_detail_display ($desc);
  $url = trim($offre['project_url']);
  if (strlen($url)>0) {
    stc_detail_subsection ("Plus d'informations");
    stc_detail_display ($url);
  }

  /* nature du travail */
  stc_detail_subsection ("Nature du travail demandé");
  $sql = "select description from nature_stage, offres_nature_stage ".
    "where id_offre = $1 and id = id_nature_stage order by id;";
  $r = pg_query_params($db, $sql, array($id));
  while ($row=pg_fetch_assoc($r)) {
    stc_detail_display($row['description']);
  }
  pg_free_result ($r);

  /* prérequis */
  $pr = trim($offre['prerequis']);
  if (strlen($pr)>0) {
    stc_detail_subsection ("Pré-requis");
    stc_detail_display ($pr);
  }

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
  
  return true;
}


$user = stc_user_id();
$admin = stc_is_admin();
$from = stc_from();

$offre_id = intval(stc_get_variable ($_GET,'offreid'));
$multisel = stc_get_variable ($_REQUEST,'multisel');
$mode = stc_get_variable ($_REQUEST,'mode');

stc_style_add("/css/detail.css");
stc_top();
$menu = stc_default_menu();
stc_menu($menu);

if (is_array($multisel)) {
  for ($i=0; $i < count($multisel); $i++) {
    stc_affiche_offre($multisel[$i], true);
    if (($i+1)< count($multisel)) echo "<hr/><div class=\"pagebreak\"></div>\n";
  }

} else {
  if ($mode == "new") echo "<p>La proposition de stage suivante a bien été enregistrée.</p>\n";
  if ($mode == "update") echo "<p>La proposition de stage suivante a bien été mise à jour.</p>\n";
  

  if (!stc_affiche_offre($offre_id)) exit(0);

  /****
   * bouton de validation de l'offre
   */
  
  if ($admin) {
    echo "<hr/>\n";
    echo "<div id=\"validate\">";
    if (is_bool($admin)) {
      echo "superadmin version of validate<br/>";
      $sql = "select m2.id, m2.short_desc, m2.ville ".
	"from m2 ".
	"where m2.id not in ( ".
	  "select om2.id_m2 ".
	  "from offres as o, offres_m2 as om2, m2 ".
	  "where o.id=om2.id_offre and om2.id_m2 = m2.id and o.id=$1 ) ".
	"order by m2.ville, m2.short_desc;";
      $res = pg_query_params ($db, $sql, array($offre_id));
      if ($res) {
	if (pg_num_rows($res)>0) {
	  echo pg_num_rows($res)." validations disponibles";
	  echo "<form method=\"post\" action=\"validate-offre.php\">";
	  echo "<input type=\"hidden\" name=\"offreid\" value=\"".$offre_id."\"/>";
	  echo "<select name=\"m2\">";
	  while ($r = pg_fetch_assoc($res)) 
	    echo "<option value=\"".$r['id']."\">".$r['short_desc']." - ".$r['ville']."</option>";
	  echo "</select>";
	  echo "<button name=\"action\" value=\"validate\">Valider l'offre</button>";
	  echo "</form>";
	} else echo "Aucune validation possible";
	pg_free_result ($res);
      }
    } else {
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
	  $projmgr = intval($row['id_project_mgr']);
	  $r = pg_query_params($db, "select id from users_view where m2_admin=$1;",array($admin));
	  $nb=pg_num_rows($r);
	  if ($projmgr==intval($user)&&($nb>1)) {
	    echo "<span>Vous ne pouvez valider les offres dont vous êtes l'auteur</span>";
	  } else {
	    $r = pg_query_params($db,
				 "select short_desc, ville from m2 where id=$1",
				 array($admin));
	    if (pg_num_rows($r)==1) {
	      $row = pg_fetch_assoc($r);
	      echo "<form method=\"post\" action=\"validate-offre.php\">";
	      echo "<input type=\"hidden\" name=\"offreid\" value=\"".$offre_id."\"/>";
	      echo "<button name=\"action\" value=\"validate\">Valider l'offre pour le M2R<br/>";
	      echo $row['short_desc']." - ".$row['ville']."</button>";
	      echo "</form>";
	    } else {
	      error_log("Impossible de trouver le M2R correspondant a l'indice ".$admin);
	    }
	  }
	}
      }
      pg_free_result($r);
    }
    echo "</div>\n";
  }
}

stc_footer();

?>