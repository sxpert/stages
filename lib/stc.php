<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once ('db.php');
require_once('xhtml.php');

/******************************************************************************
 *
 * Fonctions de gestion et nettoyages des entrées
 *
 */

function stc_get_variable ($array, $varname) {
  if (array_key_exists ($varname, $array)) {
    return $array[$varname];
  } else {
    return "";
  }
}

/*
 * retourne le chemin d'ou on vient si le host == le host de la machine sur 
 * laquelle on tourne. False sinon
 */
function stc_check_referer () {
  $ref = '';
  if (array_key_exists('HTTP_REFERER',$_SERVER)) $ref = $_SERVER['HTTP_REFERER'];
  $srv = $_SERVER['SERVER_NAME'];
  $port = $_SERVER['SERVER_PORT'];
  
  if (strlen(trim($ref))==0) return False;

  $url = parse_url($ref);  
  if (array_key_exists('scheme', $url) and strcmp($url['scheme'],'http')!=0) return False;
  if (array_key_exists('host', $url) and strcmp($url['host'],$srv)!=0) return False;
  error_log(print_r($url,1));
  $u='';
  if (array_key_exists('path',$url)) $u.=$url['path'];
  if (array_key_exists('query',$url)) $u.='?'.$url['query'];
  if (array_key_exists('fragment',$url)) $u.='#'.$url['fragment'];
  return $u;
}

/****
 * force la fermeture de session
 */
function stc_close_session() {
  session_unset();
  session_destroy();
}

/*
 * redirige vers une autre page
 */
function stc_redirect($url) {
  header('Location: '.$url);
  exit(0);
} 

/*
 * renvoie une page d'erreur si on a un referer moisi
 */
function stc_reject () {
  header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden");
  stc_close_session();
  

  stc_top();
  $menu = stc_menu_init();
  stc_menu_add_item ($menu, 'Accueil', '/');
  stc_menu($menu);
  
  // contenu
  echo "Vous provenez d'un serveur non reconnu, Connexion refusée";

  stc_footer();
  exit(1);
}

function stc_fail ($code, $message) {
  switch($code) {
  case 403: $msg = 'Forbidden'; break;
  case 405: $msg = 'Method not allowed'; break;
  case 500: $msg = 'Internal server error'; break;
  default: $code = 200; $msg = 'OK';
  }

  header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$msg);
  stc_close_session();  
  stc_top();
  $menu = stc_default_menu();
  stc_menu($menu);
  echo $message;
  stc_footer();
  exit (1);
}

/******************************************************************************
 *
 * Fonctions html (entete, menu, ...)
 * 
 */

$_stc_scripts = array();

function stc_script_add($statement, $script_id=null) {
  GLOBAL $_stc_scripts;
  
  // implementer $script_id = -1 et numérique
  if (is_int($script_id)) {
    if ($script_id==-1) array_push($_stc_scripts,$statement);
    else $_stc_scripts[$script_id] = $statement;
  } else { 
    if (is_null($script_id)) $script_id='_default';
    else if ($script_id[0]=='_') {
      switch($script_id) {
      case "_default":
      case "_begin":
	break;
      default :
	$script_id='_'.$script_id;
      }
    }
    if (array_key_exists($script_id, $_stc_scripts)) $s = $_stc_scripts[$script_id];
    else $s = array();
    array_push($s, $statement);
    $_stc_scripts[$script_id] = $s;
  }
}

function stc_top ($styles=null) {
  xhtml_header();
  echo "<head>\n";
  echo "<title>Stages et Thèses</title>\n";
  echo "<link rel=\"stylesheet\" href=\"/css/base.css\" type=\"text/css\"/>";
  echo "<link href=\"http://fonts.googleapis.com/css?family=Ubuntu:regular,bold\" rel=\"stylesheet\" type=\"text/css\">\n";
  if (!is_null($styles) and is_array($styles))
    foreach ($styles as $style)
      echo "<link rel=\"stylesheet\" href=\"".$style."\"/>\n";
  echo "</head>\n";
  echo "<body>\n";
  echo "<div id=\"top\"><a href=\"/\">logos et autres</a>";
  echo " <a href=\"http://stcoll.sxpert.org/?from=02714f16\">from Grenoble</a>";
  echo " <a href=\"http://stcoll.sxpert.org/?from=fa3dac69\">from Paris</a>";
  echo "</div>\n";
}

/******************************************************************************
 *
 * menu
 *
 */

define ('STC_MENU_SECTION', 0);
define ('STC_MENU_ITEM', 1);
define ('STC_MENU_SEPARATOR', 2);
define ('STC_MENU_FORM', 10);
define ('STC_MENU_FORM_ERROR', 11);
define ('STC_MENU_FORM_HIDDEN', 12);
define ('STC_MENU_FORM_TEXT', 13);
define ('STC_MENU_FORM_PASS', 14);
define ('STC_MENU_FORM_BUTTON', 15);
define ('STC_MENU_FORM_END', 16);

function stc_menu_init () {
  $menu = array();
  return $menu;
}

function stc_menu_add_section (&$menu, $item) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_SECTION;
  $menuitem['item']=$item;
  array_push($menu, $menuitem);
}

function stc_menu_add_item (&$menu, $item, $url) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_ITEM;
  $menuitem['item']=$item;
  $menuitem['url']=$url;
  array_push($menu, $menuitem);
}

function stc_menu_add_separator (&$menu) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_SEPARATOR;
  array_push($menu, $menuitem);
}

function stc_menu_add_form (&$menu, $method, $action) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_FORM;
  $menuitem['method']=$method;
  $menuitem['action']=$action;
  array_push($menu, $menuitem);
}

function stc_menu_form_add_error (&$menu, $message) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_FORM_ERROR;
  $menuitem['message']=$message;
  array_push($menu, $menuitem);
}

function stc_menu_form_add_hidden (&$menu, $variable, $value) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_FORM_HIDDEN;
  $menuitem['variable']=$variable;
  $menuitem['value']=$value;
  array_push($menu, $menuitem);
}

function stc_menu_form_add_text (&$menu, $label, $variable) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_FORM_TEXT;
  $menuitem['label']=$label;
  $menuitem['variable']=$variable;
  array_push($menu, $menuitem);
}

function stc_menu_form_add_password (&$menu, $label, $variable) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_FORM_PASS;
  $menuitem['label']=$label;
  $menuitem['variable']=$variable;
  array_push($menu, $menuitem);
}

function stc_menu_form_add_button (&$menu, $text) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_FORM_BUTTON;
  $menuitem['text']=$text;
  array_push($menu, $menuitem);
}

function stc_menu_form_end (&$menu) {
  $menuitem = array();
  $menuitem['type']=STC_MENU_FORM_END;
  array_push($menu,$menuitem);
}

function stc_menu($menu) {
  ?><div id="main">
<div id="menu">
<?php
  foreach ($menu as $menuitem) {
    switch ($menuitem['type']) {
    case STC_MENU_SECTION: echo "<div>".$menuitem['item']."</div>\n"; break;
    case STC_MENU_ITEM: echo "<a href=\"".$menuitem['url']."\">".$menuitem['item']."</a>\n"; break;
    case STC_MENU_SEPARATOR: echo "<hr/>\n";break;
    case STC_MENU_FORM: echo "<form method=\"".$menuitem['method']."\" action=\"".$menuitem['action']."\">\n"; break;
    case STC_MENU_FORM_ERROR: echo "<div class=\"error\">".$menuitem['message']."</div>\n"; break;
    case STC_MENU_FORM_HIDDEN: echo "<input type=\"hidden\" name=\"".$menuitem['variable']."\" value=\"".stc_form_escape_value($menuitem['value'])."\"/>"; break;
    case STC_MENU_FORM_TEXT: echo "<div><label for=\"".$menuitem['variable']."\">".$menuitem['label']."</label><input type=\"text\" name=\"".$menuitem['variable']."\"></input></div>\n"; break;
    case STC_MENU_FORM_PASS: echo "<div><label for=\"".$menuitem['variable']."\">".$menuitem['label']."</label><input type=\"password\" name=\"".$menuitem['variable']."\"></input></div>\n"; break;
    case STC_MENU_FORM_BUTTON: echo "<div><button>".$menuitem['text']."</button></div>\n"; break;
    case STC_MENU_FORM_END: echo "</form>"; break;
    }
  }
  ?></div><div id="menusepbar">&nbsp;</div>
<div id="content">
<?php
}

/******************************************************************************
 *
 * Formulaires
 *
 */

function stc_form_escape_value ($value) {
  /*
   * nettoies les chaines de caracteres entrées des caracteres spéciaux
   * pour limiter les possibilités de XSS
   */
  return htmlentities($value,ENT_COMPAT,'UTF-8',false);
}

/****
 * Fonctions de gestions et d'affichage des erreurs dans les formulaires
 */

function stc_form_add_error(&$errors, $variable, $message) {
  if (array_key_exists($variable, $errors)) $ve = $errors[$variable];
  else $ve = array();
  array_push($ve, $message);
  $errors[$variable] = $ve;
}

function stc_form_check_errors($form, $variable) {
  if (array_key_exists($variable, $form)) {
    echo "<div class=\"error\">";
    $first = True;
    foreach ($form[$variable] as $error) {
      if ($first) $first=False;
      else echo "<br/>\n";
      echo "$error";
    }
    echo "</div>\n";
  }
}

/****
 * Fonctions de vérification et de nettoyage
 */

/* telephone */

function stc_form_check_phone($phone) {
  $expr = '/^\ *(\+[0-9]+)?\ ?(\(\ *[0-9]+\ *\))?[0-9\ ]*$/';
  $v = preg_match($expr,$phone);
  return $v==1;
}

function stc_form_clean_phone($phone) {
  // remove all extraneous space chars
  $phone = trim($phone);
  // should remove all occurences of multiple spaces between number blocks

  // all done
  return $phone;
}

/* code postal */

function stc_form_check_post_code_fr ($codepostal) {
  $expr = '/^\ *[0-9]{5}\ *$/';
  $v = preg_match($expr,$codepostal);
  return $v == 1;
}

function stc_form_check_post_code ($codepostal) {
  return stc_form_check_post_code_fr ($codepostal);
}

function stc_form_clean_post_code ($codepostal) {
  return trim($codepostal);
}

/* date */ 

function stc_form_clean_date(&$date) {
  $date = trim($date);
  $expr = '/^\d{4}-\d{2}-\d{2}$/';
  $v = preg_match($expr,$date);
  return $v==1;  
}

function stc_form_check_date ($date) {
  $y = intval(substr($date,0,4));
  $m = intval(substr($date,5,2));
  $d = intval(substr($date,8,2));
  return checkdate($m,$d,$y);
}

/* checkbox */

function stc_form_clean_checkbox ($box) {
  if (trim($box)=='on') return true;
  else return false;
}

/* url */

function stc_form_clean_url($url) {
  return trim($url);
}

function stc_form_check_url($url,&$e) {
  $u = parse_url($url);
  if (is_bool($u)&&!$u) {
    $e = 'Adresse mal formée';
    return false;
  }
  if (array_key_exists('scheme',$u)) {
    if (array_key_exists('port',$u)) $port = $u['port'];
    else $port = 0;
    switch ($u['scheme']) {
    case 'http':
      if ($port==0) $port=80;
      break;
    case 'https':
      if ($port==0) $port=443 ;
      break;
    default:
      $e = 'Protocole \''.$u['scheme'].'\' inconnu (\'http\' ou \'https\' attendu)';
      return false;
    }
    $u['port']=$port;
  } else {
    $e = 'Type de protocole manquant (\'http\' ou \'https\' attendu)';
    return false; /* force http ?? better not */
  }
  if (array_key_exists('host',$u)) {
    $ips = dns_get_record($u['host']);
    if ((is_bool($ips)&&!$ips)||(count($ips)==0)) {
      $e = 'Serveur \''.$u['host'].'\' introuvable';
      return false;
    }
    /* trouve si une des adresses réponds */
    $ok = false;
    foreach ($ips as $ip) {
      switch ($ip['type']) {
      case 'A':
	$s=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
	$ok |= @socket_connect($s,$ip['ip'],$u['port']);
	error_log($ip['type'].' - '.$ip['ip'].':'.$u['port'].' '.($ok?'ok':'nok'));
	socket_close($s);
	break;
      case 'AAAA':
	$s=socket_create(AF_INET6,SOCK_STREAM,SOL_TCP);
	$ok |= @socket_connect($s,$ip['ipv6'],$u['port']);
	error_log($ip['type'].' - ['.$ip['ipv6'].']:'.$u['port'].' '.($ok?'ok':'nok'));
	socket_close($s);
	break;
      default:
	continue;
      }
    }
    if (!$ok) {
      $e = 'Connection au serveur impossible';
      return false;
    }
  } else {
    $e = 'Nom de serveur manquant';
    return false; /* si on a pas de host, c'est compromis... */
  }
  /* timeout a 5 secondes */
  $r = http_head($url,array('timeout'=>5),$info);
  if ($info['response_code']>=400) {
    $e = 'Accès au document impossible';
    return false;
  }
  return true;
}

/* select */

function stc_form_check_select ($value, $table) {
  GLOBAL $db;
  if (strlen($value)==0) return false;
  $sql="select key, value from ".$table." where key=$1";
  $r=pg_query_params($db, $sql, array(intval($value)));
  if (pg_num_rows($r)==0) $ok=false;
  else $ok=true;
  pg_free_result($r);
  return $ok;
}

/* multi select */

function stc_form_clean_multi ($values) {
  $val = array();
  sort($values, SORT_NUMERIC);
  foreach($values as $v) {
    $v = intval($v);
    if ($v==0) continue;
    if (!in_array($v, $val)) array_push($val, $v);
  }
  return $val;
}

function stc_form_check_multi ($values, $table) {
  GLOBAL $db;
  $sql="select key, value from ".$table." where key=$1;";
  $ok = true;
  foreach($values as $v) {
    $r = pg_query_params($db, $sql, array($v));
    if (pg_num_rows($r)==0) $ok=false;
    pg_free_result($r);
  }
  return $ok;
}

/****
 * Fonction de génération du HTML pour les formulaires
 */

function stc_form ($method, $action, $errors) {
  echo "<form method=\"".$method. "\" action=\"".$action."\">\n";
  return $errors;
}

function stc_form_hidden($form, $variable, $value="") {
  echo stc_form_check_errors ($form, $variable);
  echo "<input type=\"hidden\" name=\"".$variable."\"";
  if (strlen($value)>0) echo " value=\"".stc_form_escape_value($value)."\"";
  echo "/>";
}

function stc_form_text ($form, $label, $variable, $value="", $width=null, $length=null) {
  echo stc_form_check_errors ($form, $variable);
  echo "<div>";
  echo "<label for=\"".$variable."\">".$label."</label>";
  echo "<input type=\"text\" name=\"".$variable."\"";
  if (!is_null($width)) echo " style=\"width:".$width."\"";
  if (!is_null($length)) echo " maxlength=\"".$length."\"";
  if (strlen($value)>0) echo " value=\"".stc_form_escape_value($value)."\"";
  echo "/></div>\n";
}

// TODO: mettre un sélecteur de date
function stc_form_date ($form, $label, $variable, $value="") {
  echo stc_form_check_errors ($form, $variable);
  echo "<div>";
  echo "<label for=\"".$variable."\">".$label."</label>";
  echo "<input type=\"text\" name=\"".$variable."\"";
  if (strlen($value)>0) echo " value=\"".stc_form_escape_value($value)."\"";
  echo "/></div>\n";
}

function stc_form_password ($form, $label, $variable, $value="") {
  echo stc_form_check_errors ($form, $variable);
  echo "<div>";
  echo "<label for=\"".$variable."\">".$label."</label>";
  echo "<input type=\"password\" name=\"".$variable."\"";
  if (strlen($value)>0) echo " value=\"".stc_form_escape_value($value)."\"";
  echo "></div>\n";
}

function stc_form_textarea ($form, $label, $variable, $value="", $width=null, $height=null) {
  echo stc_form_check_errors ($form, $variable);
  echo "<div>";
  echo "<label for=\"".$variable."\">".$label."</label>";
  echo "<textarea name=\"".$variable."\"";
  $s='';
  if (!is_null($width)) $s.="width:".$width.";";
  if (!is_null($height)) $s.="height:".$height.";";
  if (strlen($s)>0) $s=" style=\"".$s."\"";
  echo $s.">";
  if (strlen($value)>0) echo stc_form_escape_value($value);
  echo "</textarea></div>\n";
}

function stc_form_select ($form, $label, $variable, $value="", $values=null, $options=null) {
  GLOBAL $db;

  $onchange = null;
  $multi=false;
  $width=null;
  if (!is_null($options) and is_array($options)) {
    if (array_key_exists('onchange',$options)) $onchange = $options['onchange'];
    if (array_key_exists('multi',$options)) $multi=$options['multi'];
    if (array_key_exists('width',$options)) $width=$options['width'];
  }
  echo stc_form_check_errors ($form, $variable);
  if ($multi) {
    GLOBAL $_stc_scripts;
    stc_script_add('/lib/js/multiselect.js', -1);
    echo "<div>";
    echo "<label for=\"".$variable."[]\">".$label."</label>";
    echo "<div id=\"".$variable."\" class=\"wrapper\">";
    echo "</div></div>\n";
    $sql = "select key, value from ".$values.";";
    pg_send_query ($db, $sql);
    $r = pg_get_result ($db);
    $_val = array();
    while ($row = pg_fetch_assoc ($r)) array_push($_val, array($row['key'],$row['value']));
    stc_script_add("var ".$variable." = new Array();","_begin");
    stc_script_add($variable."['name']= \"".$variable."\";","_begin");
    stc_script_add($variable."['init']= ".((!is_array($value))?"null":json_encode($value)).";","_begin");
    stc_script_add($variable."['width']= ".(is_null($width)?"null":"'".$width."'").";","_begin");
    stc_script_add($variable."['values'] = ".json_encode($_val).";","_begin");
    pg_free_result ($r);
    stc_script_add( "ms_init(".$variable.");",'window.onload');
  } else {
    echo "<div>";
    echo "<label for=\"".$variable."\">".$label."</label>";
    echo "<select name=\"".$variable."\"";
    if (!is_null($onchange)) echo " onchange=\"".stc_form_escape_value($onchange)."\"";
    if (!is_null($width)) echo " style=\"width:".$width.";\"";
    echo ">\n";
    if (!is_null($values)) {
      if (is_string($values)) {
	// option de base - vide
	echo "<option value=\"\"></option>\n";
	// nom de la vue dans la base de données
	$sql = "select key, value from ".$values.";";
	pg_send_query($db,$sql);
	$r = pg_get_result($db);
	while ($row = pg_fetch_assoc($r)) {
	  echo "<option value=\"".stc_form_escape_value($row['key'])."\"";
	  if (strcmp($row['key'],$value)==0) echo " selected=\"1\"";
	  echo ">".$row['value']."</option>\n";
	}
	pg_free_result($r);
      } else if (is_array($values)) {
	// associative array de valeurs
	foreach($values as $key => $val) {
	  $s = "";
	  if ($key==$value) $s = " selected";
	  echo "<option value=\"".$key."\"".$s.">".$val."</option>\n";
	}
      } else {
	// unknown type
	error_log("stc_form_select : type ".gettype($values)." not supported for values");
      }
    }
    echo "</select></div>";
  }
}

function stc_form_checkbox ($form, $label, $variable, $value="", $group=null) {
  echo stc_form_check_errors ($form, $variable);
  echo "<div>";
  echo "<label for=\"".$variable."\">".$label."</label>";
  echo "<input type=\"checkbox\" name=\"".$variable."\"";
  if ((is_bool($value)&&$value)||(!strcmp($value,'on'))) echo " checked";
  echo "/></div>\n";
}

function stc_form_button ($form, $text, $action="") {
  echo stc_form_check_errors ($form, 'action');
  echo "<div>";
  echo "<button";
  if (strlen($action)>0) echo " name=\"action\" value=\"".$action."\"";
  echo ">".$text."</button>";
  echo "</div>\n";
}

function stc_form_end () {
  echo "</form>\n";
}

/******************************************************************************
 *
 * pied de page
 *
 */

function _append_scripts($scripts=null) {
  GLOBAL $_stc_scripts;
  
  if (is_null($scripts)) $scripts=$_stc_scripts;

  // d'abord les scripts a l'index numérique (aka les fichiers
  $nbts = 0;
  foreach($scripts as $key => $values) {
    if (!is_int($key)) {
      $nbts++;
      continue;
    }
    echo "<script type=\"text/javascript\" src=\"".$values."\"></script>\n";
  }

  // puis les scripts a index texte (les statements)
  if ($nbts==0) return;
  echo "<script type=\"text/javascript\">\n";
  if (array_key_exists("_begin",$scripts)) {
    foreach($scripts["_begin"] as $statement) echo $statement."\n";
  }
  echo "\n";
  foreach($scripts as $key => $values) {
    if (is_int($key)) continue;
    if (strcmp($key, "_begin")==0) continue;
    if (strcmp($key, "_default")==0) {
      foreach($values as $statement) echo $statement."\n";
    } else {
      echo $key." = function () {\n";
      foreach($values as $statement) echo "    ".$statement."\n";
      echo "};\n";
    }
  }
  echo "</script>\n";
}

function stc_footer($scripts=null) {
  GLOBAL $_stc_scripts;
  echo "</div></div>\n<div id=\"footer\">footer<br/>\nAccès par ";
   if (array_key_exists('from', $_SESSION)) {
     $m2 = stc_get_m2($_SESSION['from']);
     echo $m2['description'].' - '.$m2['from_value'];
   } else {
     echo "unknown entity";
   }
   if (array_key_exists('admin', $_SESSION))
     echo " admin = ".($_SESSION['admin']?'true':'false').' '.$_SESSION['admin'];
   echo "\n</div>\n";
    // jquery
    echo "<script type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js\"></script>\n";
    // les scripts
    if (!is_null($scripts)) 
	_append_scripts($scripts);
    _append_scripts();
    echo "</body>\n</html>\n";
}

/******************************************************************************
 *
 * menu standard
 *
 */

function stc_default_menu ($options=null) {
  GLOBAL $db;

  $menu = stc_menu_init();
  
  $logged = stc_is_logged();
  $admin  = stc_is_admin();
  $opt_login = True;
  $opt_register = True;
  $opt_home = False;
  $loginerr = False;
  if (is_array($options)) {
    if (array_key_exists('login', $options)) $opt_login=$options['login'];
    if (array_key_exists('register', $options)) $opt_register=$options['register'];
    if (array_key_exists('home', $options)) $opt_home=$options['home'];
  }
  if (array_key_exists('loginerr', $_SESSION)) {
    $loginerr = $_SESSION['loginerr'];
    unset($_SESSION['loginerr']);
  }
  
  // listage des types de propositions
  $sql = "select code, denom_prop from type_offre order by code";
  pg_send_query($db, $sql);
  $r = pg_get_result($db);
  while ($row = pg_fetch_assoc($r)) {
    stc_menu_add_section ($menu, 'Propositions de '.$row['denom_prop']);
    if ((!$logged)||$admin)
      stc_menu_add_item($menu, 'rechercher', 'search.php?type='.$row['code']);
    if ($logged) {
      stc_menu_add_item($menu, 'proposer', 'propose.php?type='.$row['code']);
      stc_menu_add_item($menu, 'gérer ses propositions', 'search.php?type='.$row['code']);
    } 
    stc_menu_add_separator($menu);
  }
  pg_free_result ($r);
  
  if ($logged) {
    //stc_menu_add_section ($menu, '');
    stc_menu_add_item ($menu, 'déconnexion', 'logout.php');
  } else {
    if ($opt_login) {
      stc_menu_add_section ($menu, 'Connexion');
      stc_menu_add_form($menu,"post", "login.php");
      if ($loginerr!=null) stc_menu_form_add_error($menu,$loginerr);
      stc_menu_form_add_text($menu,"Utilisateur","user");
      stc_menu_form_add_password($menu,"Mot de Passe","password");
      stc_menu_form_add_button($menu,"se connecter");
      stc_menu_form_end($menu);
    }
    if ($opt_register) stc_menu_add_item($menu, "s'enregistrer", "register.php");
    if ($opt_home) stc_menu_add_item($menu, "accueil", "index.php");
  }
  return $menu;
}


/******************************************************************************
 *
 * Accès à la base de données
 *
 */

function stc_connect_db () {
  return db_connect ();
}

function stc_dump_sql_error ($res) {
  $a = array('PGSQL_DIAG_SEVERITY', 
	     'PGSQL_DIAG_SQLSTATE', 
	     'PGSQL_DIAG_MESSAGE_PRIMARY', 
	     'PGSQL_DIAG_MESSAGE_DETAIL', 
	     'PGSQL_DIAG_MESSAGE_HINT', 
	     'PGSQL_DIAG_STATEMENT_POSITION', 
	     'PGSQL_DIAG_INTERNAL_POSITION', 
	     'PGSQL_DIAG_INTERNAL_QUERY', 
	     'PGSQL_DIAG_CONTEXT', 
	     'PGSQL_DIAG_SOURCE_FILE', 
	     'PGSQL_DIAG_SOURCE_LINE',
	     'PGSQL_DIAG_SOURCE_FUNCTION');
  echo "<div><pre>";
  foreach ($a as $key)
    echo $key." : ".pg_result_error_field($res,constant($key))."\n";
  echo "</pre></div>\n";
}

function stc_get_laboratoire ($labo_id) {
  GLOBAL $db;

  $sql = "select * from laboratoires where id = $1";
  $r = pg_query_params($db, $sql, array($labo_id));
  $row = pg_fetch_assoc($r);
  pg_free_result ($r);
  return $row;
}

function stc_get_m2 ($m2_id) {
  GLOBAL $db;

  $sql = "select * from m2 where id = $1";
  $r = pg_query_params($db, $sql, array($m2_id));
  $row = pg_fetch_assoc($r);
  pg_free_result ($r);
  return $row;
}

function stc_user_account_create ($f_name, $l_name, $email, $phone, $status, $labo, $login, $pass1) {
  GLOBAL $db;
  
  $sql = "select * from user_add($1,$2,$3,$4,$5,$6,$7,$8) as (id bigint, hash text);";
  $arr = array($f_name, $l_name, $email, $phone, $status, $labo, $login, $pass1);
  pg_send_query_params($db,$sql,$arr);
  $r = pg_get_result($db);
  return $r;
}

function stc_user_validate_account($login, $password, $hash) {
  GLOBAL $db;
  
  $sql = "select user_validate_account ($1, $2, $3) as id;";
  $arr = array($login,$password,$hash);
  pg_send_query_params($db,$sql,$arr);
  $r = pg_get_result($db);
  $row = pg_fetch_assoc($r);
  pg_free_result($r);
  return intval($row['id']);
}

function stc_user_login($login, $password) {
  GLOBAL $db;
  
  $sql = "select * from user_login($1, $2) as ( id bigint, m2_admin bigint);";
  $arr = array($login, $password);
  pg_send_query_params($db,$sql,$arr);
  $r = pg_get_result($db);
  $row = pg_fetch_assoc($r);
  pg_free_result($r);
  error_log('stc_user_login: row = '.print_r($row,1));
  $id = intval($row['id']);
  $m2adm = intval($row['m2_admin']);
  return array($id, $m2adm);
}

function stc_user_logout() {
  unset($_SESSION['userid']);
  unset($_SESSION['admin']);
}

function stc_rollback($message=null) {
  GLOBAL $db;
  if (is_string($message)) {
    $message = trim($message);
    if (strlen($message)>0)
      error_log($message);
  }
  pg_free_result(pg_query($db, 'rollback;'));
  return false;
}

function stc_offre_add($type, $categories, 
		       $sujet, $description, $url,
		       $nature_stage, $prerequis,
		       $infoscmpl, $start_date, $length,
		       $co_encadrant, $co_enc_email,
		       $pay_state, $thesis) {
  GLOBAL $db;

  $thesis = ($thesis?'true':'false');

  /* debut de transaction */
  pg_free_result(pg_query($db, 'begin;'));

  /**
   * obtention de l'id type offre 
   */
  $sql = "select id from type_offre where code=$1;";
  $r = pg_send_query_params($db, $sql, array($type));
  $r = pg_get_result($db);
  /* should not fail */
  $n = pg_num_rows($r);
    /* $n doit toujours etre 1 ici. quelqu'un doit jouer... */
  if ($n!=1) return stc_rollback('Impossible de trouver le code d\'offre '.$type);
  $row = pg_fetch_assoc($r);
  pg_free_result($r);

  /**
   * ajout de l'offre
   */
  $id_type_offre = $row['id'];
  $id_project_mgr = $_SESSION['userid'];
  /* TODO: calculate year value */
  $year_value = stc_calc_year();
  /* insertion des infos de l'offre */
  $sql = "insert into offres (id_type_offre, id_project_mgr, year_value, sujet, ".
    "description, project_url, prerequis, infoscmpl, start_date, duree, co_encadrant, ".
    "co_enc_email, pay_state, thesis, create_date) values ".
    "($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,CURRENT_TIMESTAMP) returning id;";
  $arr = array(intval($id_type_offre), intval($id_project_mgr), $year_value, $sujet, $description, $url,
	       $prerequis, $infoscmpl, $start_date, $length, $co_encadrant, $co_enc_email,
	       intval($pay_state), $thesis);
  $r = pg_send_query_params($db, $sql, $arr);
  $r = pg_get_result($db);
  if (pg_result_status($r)!=PGSQL_TUPLES_OK)
    /* houston, we have a problem ! */
      return stc_rollback('insert offre => '.pg_result_error_field($r, PGSQL_DIAG_SQLSTATE).
		      ' - '.pg_last_error($db));
  $n = pg_num_rows($r);
  if (pg_num_rows($r)!=1) {
    /* CAN'T HAPPEN */
    pg_free_result($r);
    return stc_rollback('Nombre de réponses invalide ('.$n.') lors de l\'insertion d\'une offre');
  }
  $row = pg_fetch_assoc($r);
  $offreid = $row['id'];

  /**
   * insertion des categories s'appliquant à l'offre
   */
  $sql = "insert into offres_categories (id_offre, id_categorie) values ($1, $2);";
  foreach($categories as $categorie) {
    $r = pg_send_query_params($db, $sql, array($offreid, $categorie));
    $r = pg_get_result($db);
    if (pg_result_status($r)!=PGSQL_COMMAND_OK)
      return stc_rollback('categories execute => '.pg_result_error_field($r,PGSQL_DIAG_SQLSTATE).
		      ' - '.pg_last_error($db));
  }

  /**
   * insertion des nature_stage s'appliquant a l'offre
   */
  $sql = "insert into offres_nature_stage (id_offre, id_nature_stage) values ($1, $2);";
  foreach($nature_stage as $ns) {
    $r = pg_send_query_params($db, $sql, array($offreid, $ns));
    $r = pg_get_result($db);
    if (pg_result_status($r)!=PGSQL_COMMAND_OK)
      return stc_rollback('nature_stage execute => '.pg_result_error_field($r,PGSQL_DIAG_SQLSTATE).
		      ' - '.pg_last_error($db));
  }

  /**
   * tout s'est bien passé, on committe la transaction et on renvoie le 
   * numéro de l'offre nouvellement créée
   */
  pg_free_result(pg_query($db, 'commit;'));
  return $offreid;
}

/****
 *
 * Gestion des utilisateurs
 * 
 */

function stc_is_logged () {
  return array_key_exists('userid',$_SESSION);
}

function stc_user_id () {
  if (array_key_exists('userid',$_SESSION)) return $_SESSION['userid'];
  return 0;
}

function stc_is_admin () {
  if (array_key_exists('admin',$_SESSION)) return $_SESSION['admin'];
  return false;
}

function stc_from () {
  if (array_key_exists('from',$_SESSION)) return intval($_SESSION['from']);
  return 0;
}

function stc_must_be_logged() {
  if (stc_is_logged()) return;
  
  stc_close_session();
  header ('Location: /');
  exit();
}

function stc_set_m2_provenance ($from) {
  GLOBAL $db;

  $sql = 'select id from m2 where from_value = $1';
  $r = pg_query_params ($db, $sql, array($from));
  $n = pg_num_rows($r);
  switch ($n) {
  case False:
    // error during request
    error_log ('stc_set_m2_provenance: sql request failure');
    break;
  case 0:
    // not found.
    error_log ('unable to find m2 for from = \''.$from.'\'');
    break;
  case 1:
    // all ok
    $row = pg_fetch_row ($r);
    pg_free_result($r);
    $m2_id = $row[0];
    error_log('found m2_id = '.$m2_id.' for from=\''.$from.'\'');
    $_SESSION['from']=$m2_id;
    break;
  default:
    // can't happen ;-)
    error_log ('can\'t happen case for from = \''.$from.'\'');
  }
}

/*******************************************************************************
 * Fonctions utilitaires
 */

function stc_calc_year () {
  $d = getdate();
  $y = $d['year'];
  $m = $d['mon'];
  if ($m>=9) $y++;
  return $y;
}

/*******************************************************************************
 * gestion de la session
 */
session_start();
$db = stc_connect_db();


?>