<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once ('lib/stc.php');

if (array_key_exists('from',$_REQUEST)) {
  $from = $_REQUEST['from'];
  stc_set_m2_provenance ($from);
  $from=stc_from();
} 

if (($_SERVER['REMOTE_ADDR']=='193.107.127.8')&&
		($EN_TRAVAUX)) {
  error_log("Travaux : ".$_SERVER['REMOTE_ADDR']);
  stc_top();
  $menu = stc_menu_init();
  stc_menu_add_item($menu, "Accueil", "index.php");
  stc_menu($menu);
  echo "<h2>Site en travaux</h2>\n";
  echo "<p>Le site revient bientôt, veuillez nous excuser pour le dérangement, 1</p>\n";
	echo "<pre>".print_r($_SERVER,1)."</pre>\n";
	stc_footer();
  exit(0);
}

stc_top();

// menu
$menu = stc_default_menu();
stc_menu($menu);

// contenu
if (stc_is_logged()) {
  if (stc_is_admin()) {
    $nom = stc_get_user_name(stc_user_id());
    $m2r = stc_get_m2_name(stc_is_admin());
    echo "<h2>Bienvenue $nom</h2>\n";
    echo "<p>Vous êtes responsable du M2R $m2r. Les propositions qui attendent votre ".
      "validation sont accessibles dans le menu correspondant à gauche.</p>\n";
    echo "<p>Pour proposer vous-même un nouveau stage, sélectionnez le menu \"Proposer un sujet ".
      "de stage\".<br/>\n".
      "Remplir le formulaire en respectant la mise en page. Le descriptif du ".
      "stage ne peut dépasser $MAX_CHARS signes. Si vous souhaitez fournir plus ".
      "d'informations et des images ou films, mettez-les sur un site et indiquez son ".
      "URL.</p>\n";
    echo "<p>Cliquer à la fin sur \"Enregistrer la proposition\". La proposition sera ".
      "alors mise en attente de validation. Vous pouvez à tout moment la modifier via ".
      "le menu \"Mes propositions de stage\". Toute modification nécessitera une ".
      "nouvelle validation.</p>";

  } else {
    $nom = stc_get_user_name(stc_user_id());
    echo "<h2>Bienvenue $nom</h2>\n";
    echo "<p>Pour proposer un nouveau stage, sélectionnez le menu \"Proposer un sujet ".
      "de stage\".<br/>\n".
      "Remplir le formulaire en respectant la mise en page. Les descriptif du ".
      "stage ne peut depasser $MAX_CHARS signes. Si vous souhaitez fournir plus ".
      "d'informations et des images ou films, mettez les sur un site et indiquez son ".
      "URL.</p>\n";
    echo "<p>Cliquer à la fin sur \"Enregistrer la proposition\". La proposition sera ".
      "alors mise en attente de validation. Vous pouvez à tout moment la modifier via ".
      "le menu \"Mes propositions de stage\". Toute modification nécessitera une ".
      "nouvelle validation.</p>";
  }
} else {
  if (stc_from()>0) {
    $m2r = stc_get_m2_name(stc_from());
    $nbprop = stc_get_nb_offres(stc_from());
    echo "<h2>Propositions de stages validées par le M2R $m2r</h2>\n";
    echo "<p>Il y a $nbprop propositions de stage disponibles en tout. Vous ".
      "avez la possibilité de faire un filtrage par catégories scientifiques, ".
      "nature du travail proposé, laboratoire, ville et mots (ou noms propres). ".
      "Si aucun filtrage n'est fait, toutes les propositions seront affichées.</p>";
    echo "<p>Cliquer sur le sujet du stage pour avoir le détail de celui-ci.</p>";
    echo "<p>Vous pouvez imprimer un ou plusieurs stages en les sélectionnant ".
      "(case à gauche) puis en cliquant sur le bouton \"Imprimer\". Utiliser ensuite ".
      "la fonction \"imprimer\" de votre navigateur pour obtenir le document.<br/>".
      "Chaque stage apparaîtra alors sur une feuille séparée.</p>"; 
  } else {
?>
<h2>Bienvenue sur la base de données des stages de M2R en Astronomie et 
Astrophysique de France</h2>

<p>Afin de déposer ou consulter une offre de stage de M2R, il faut au préalable 
s'inscrire dans la base de données en choisissant un nom d'utilisateur et un mot 
de passe. Il suffit de le faire une seule fois. Pour cela, cliquer sur le menu
"Créer un compte" à gauche, et suivre la procédure.</p>

<p>La base envoie ensuite un email de confirmation à l'adresse que vous aurez 
fourni. L'inscription ne sera effective qu'après validation, faite en cliquant 
sur le lien inclus dans l'email. Le dépôt d'une proposition de stage ainsi que 
la consultation et/ou modification ultérieure seront alors disponibles après 
connexion.</p>

<!--<p>Si vous avez oublié votre mot de passe, cliquer ici (BOUTON).</p>-->

<p>Les stages sont ensuite validés par les divers responsables de filières en 
Astrophysique de France. Avec le menu "Consulter", vous aurez la liste des M2R 
qui auront validé votre stage. Celui-ci sera dès lors accessible aux étudiants 
via la page web de ces M2R.  A noter que toute modification du stage nécessite 
une nouvelle validation. Seuls les stages validés sont proposés aux étudiants.</p>

<p>Pour toute question concernant la validation de vos stages, prière de vous 
adresser directement au(x) responsable(s) du M2R concerné.</p>
<?php
  }
}

stc_footer();


?>
