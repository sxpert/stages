<?php
require_once ('db.php');
require_once('xhtml.php');

/******************************************************************************
 *
 * Fonctions html (entete, menu, ...)
 * 
 */

function stc_top() {
  xhtml_header();
  ?><head>
<title>Stages et Thèses</title>
<link rel="stylesheet" href="css/base.css"/>
</head>
<body>
<div id="top">logos et autres</div>
<?php
}

define ('STC_MENU_SECTION', 0);
define ('STC_MENU_ITEM', 1);
define ('STC_MENU_SEPARATOR', 2);
define ('STC_MENU_FORM', 10);
define ('STC_MENU_FORM_TEXT', 11);
define ('STC_MENU_FORM_PASS', 12);
define ('STC_MENU_FORM_BUTTON', 13);
define ('STC_MENU_FORM_END', 14);

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

function stc_footer() {
  ?></div></div>
<div id="footer">footer<br/>
Accès par <?php
    if (array_key_exists('from', $_SESSION)) {
      $labo = stc_get_laboratoire($_SESSION['from']);
      echo $labo['sigle'].' ('.$labo['description'].') '.$labo['from_value'];
    } else {
      echo "unknown entity";
    }
?>
</div>
</body>
</html>
<?php
}

function stc_default_menu () {
  $menu = stc_menu_init();
  
  $logged = stc_is_logged();
  
  stc_menu_add_section ($menu, 'Propositions de Thèses');
  stc_menu_add_item($menu, 'rechercher', 'test-1.php');
  if ($logged) stc_menu_add_item($menu, 'proposer', 'test-2.php');
  stc_menu_add_separator($menu);
  stc_menu_add_section ($menu, 'Propositions de Stages');
  stc_menu_add_item($menu, 'rechercher', 'test-1.php');
  if ($logged) stc_menu_add_item($menu, 'proposer', 'test-2.php');
  stc_menu_add_separator($menu);
  if ($logged) {
    stc_menu_add_section ($menu, '');
    stc_menu_add_item ($menu, '');
  } else {
    stc_menu_add_section ($menu, 'Connexion');
    stc_menu_add_form($menu,"post", "login.php");
    stc_menu_form_add_text($menu,"Utilisateur","user");
    stc_menu_form_add_password($menu,"Mot de Passe","password");
    stc_menu_form_add_button($menu,"se connecter");
    stc_menu_form_end($menu);
    stc_menu_add_item($menu, "s'enregistrer", "register.php");
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

function stc_get_laboratoire ($labo_id) {
  GLOBAL $db;

  $sql = "select * from laboratoires where id = $1";
  $r = pg_query_params($db, $sql, array($labo_id));
  $row = pg_fetch_assoc($r);
  return $row;
}

/******************************************************************************
 *
 * Gestion des utilisateurs
 * 
 */

function stc_is_logged () {
  return array_key_exists('userid',$_SESSION);
}

function stc_set_labo_provenance ($from) {
  GLOBAL $db;

  $sql = 'select id from laboratoires where from_value = $1';
  $r = pg_query_params ($db, $sql, array($from));
  $n = pg_num_rows($r);
  switch ($n) {
  case False:
    // error during request
    error_log ('stc_set_labo_provenance: sql request failure');
    break;
  case 0:
    // not found.
    error_log ('unable to find lab for from = \''.$from.'\'');
    break;
  case 1:
    // all ok
    $row = pg_fetch_row ($r);
    $lab_id = $row[0];
    error_log('found lab_id = '.$lab_id.' for from=\''.$from.'\'');
    $_SESSION['from']=$lab_id;
    break;
  default:
    // can't happen ;-)
    error_log ('can\'t happen case for from = \''.$from.'\'');
  }
}

// gestion de la session
session_start();
$db = stc_connect_db();


?>