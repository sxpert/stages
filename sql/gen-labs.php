#!/usr/bin/php
<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

define ('SOURCE_URL', 'http://www.insu.cnrs.fr/co/unites/referentiel');

/* loads the html file */
$html = file_get_contents(SOURCE_URL, 'r');
$doc = new DOMDocument ();
$doc->loadHTML($html);

$tables = $doc->getElementsByTagName('table');
if ($tables->length!=1) {
  echo "Le format a changé, désolé, je ne suis pas programmé pour gérer ca\n";
  exit(1);
}

/* version actuelle avec une seule table dans le document HTML */

$table = $tables->item(0);

/* TODO: récupère les entetes */
//$head = $table->getElementsByTagName('thead');

$body = $table->getElementsByTagName('tbody');
$lines = $body->item(0)->childNodes;
$labos = array();
foreach($lines as $line) {
  if ($line->tagName=='tr') {
    $l = array();
    foreach($line->getElementsByTagName('td') as $item) {
      $children = $item->childNodes;
      if ($children->length>1) {
	// find the last 'a' tag
	foreach($children as $child)
	  if (($child->nodeType==XML_ELEMENT_NODE)&&($child->tagName=='a'))
	    $a = $child;
	$attr = $a->attributes;
	$url = $attr->getNamedItem('href');
	$l['url'] = $url->textContent;
	$children = $a->childNodes;
	if($children->length!=1) {
	  array_push($l,'unknown');
	  continue;
	}
      }
      $child = $children->item(0);
      if ($child->nodeType==XML_TEXT_NODE)
	array_push($l,trim($child->textContent));
    }
    array_push($labos, $l);
  }
}

$sectionsCNRS = array();

foreach($labos as $id=>$labo) {
  $l = array();

  if (array_key_exists('url',$labo))
    $l['url'] = $labo['url'];

  $ln = trim($labo[0]);
  $pos = strpos($ln,'(');
  if ($pos==0) $pos = strlen($ln);
  $l['name'] = trim(substr($ln,0,$pos));
  $l['sigle'] = substr($ln,$pos+1,-1);
  
  $ln = trim($labo[1]);
  $l['typelabo'] = preg_replace('/^([A-Z]+)\d+$/','${1}',$ln);
  $l['idlabo'] = preg_replace('/^[A-Z]+(\d+)$/','${1}',$ln);
  
  $l['ville'] = trim($labo[3]);

  $ln = trim($labo[9]);
  $pos = strpos($ln,':');
  $sid = intval(substr($ln,0,$pos));
  $snm = trim(substr($ln,$pos+1));
  if ($sid!=0) {
    if (!array_key_exists($sid,$sectionsCNRS))
      $sectionsCNRS[$sid]=$snm;
  } else $sid=null;
  $l['section']=$sid;

  $labos[$id] = $l;
}

// insertion dans la base de données

require_once('../lib/db.php');

$db = db_connect_adm();

// d'abord les sections CNRS

$sql = "insert into sections_cnrs (id, description) values($1,$2);";
foreach($sectionsCNRS as $id => $description) {
  pg_query_params($db, $sql, array($id, $description));
}

// ensuite, les labos
$sql = "insert into laboratoires (type_unite, id, id_section, sigle, description, city) values ($1,$2,$3,$4,$5,$6);";
foreach($labos as $labo) {
  pg_query_params($db, $sql, 
		  array ($labo['typelabo'], 
			 $labo['idlabo'],
			 $labo['section'],
			 $labo['sigle'],
			 $labo['name'],
			 $labo['ville']));
}



?>
