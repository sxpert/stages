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
		echo "<p>Les stages ne seront accessibles aux étudiants qu'à partir de ".
			"la mi-octobre de l'année courante".
			". Tout stage déposé après cette date sera validé puis affiché au fil de l'eau.</p>";
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
		echo "<p>Les stages ne seront accessibles aux étudiants qu'à partir de ".
			"la mi-octobre de l'année courante".
			". Tout stage déposé après cette date sera validé puis affiché au fil de l'eau.</p>";
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

<h2>Bienvenue sur la base de données des stages de M2R en Astronomie et Astrophysique de France</h2>

<p>Ce serveur national est le moyen privilégié pour déposer vos propositions de stage à l’attention 
des formations de niveau Master 2 en Astrophysique et Planétologie de France. Ces propositions 
seront ensuite diffusées aux étudiants suivant les modalités de chacune des formations. A noter 
que certaines d’entre elles accueillent également des élèves-ingénieurs et qu’il est donc possible 
de proposer des stages à dominante plus appliquée et instrumentale susceptibles de les intéresser.</p>
   
<p><strong>Les propositions doivent être déposées au plus tôt sur le serveur, pour une diffusion à 
partir de la mi-octobre dans chacun des Masters.</strong> Après cette date, le serveur restera ouvert 
et accessible aux étudiants, mais les sujets ne seront validés par les responsables de formation 
qu'une fois par semaine (et l’impact auprès des étudiants sera moindre).</p>

<p>Afin de déposer ou de consulter une offre de stage de M2, il faut au préalable s’inscrire dans 
la base de données en choisissant un nom d'utilisateur et un mot de passe. Il suffit de le faire 
une seule fois. Pour cela, cliquer sur le menu « Créer un compte » à gauche, et suivre la procédure. 
La base envoie ensuite un email de confirmation à l'adresse que vous aurez fourni. L'inscription ne 
sera effective qu'après validation, faite en cliquant sur le lien inclus dans l'email. Le dépôt 
d'une proposition de stage ainsi que la consultation et/ou modification ultérieure deviendront 
alors possibles après connexion.</p>

<p>Les propositions de stage sont ensuite validées par les divers responsables de filières en 
Astrophysique de France. Avec le menu "Consulter", vous aurez la liste des formations qui auront 
validé votre stage. Celui-ci sera dès lors accessible aux étudiants via la page web individuelle 
de chacune des formations. A noter que toute modification du stage nécessite une nouvelle validation. 
Seuls les stages validés seront proposés aux étudiants.</p>

<p>Pour toute question concernant la validation de vos stages, prière de vous adresser directement 
au(x) responsable(s) des formations concernées.</p>

<p><strong><a href="http://sf2a.eu/spip/spip.php?rubrique41" target="_">Liste des formations concernées</a></strong></p>
<?php
/*
<ul>
	<li>Bordeaux: parcours Astrophysique du M2 de Physique de l’Université de Bordeaux</li>
	<li>Grenoble: M2 A2P (Astrophysique, Plasmas et Planètes) de l’Université Joseph Fourier</li>
	<li>Ile de France:
		<ul>
			<li>M2 AAIS (Astronomie Astrophysique et Ingénierie Spatiale, parcours Astrophysique + 
				parcours Dynamique des Systèmes Gravitationnels) de l’Observatoire de Paris, 
				Université Pierre et Marie Curie, Université Denis Diderot, Université Paris-Sud et 
				Ecole Normale Supérieure</li>
			<li>M2 du parcours Planétologie Ile de France</li>
			<li>M2 PSL-ITI du Master de l’Institut de Technologie et d’Innovation de Paris Sciences 
				et Lettres</li>
		</ul>
	</li>
	<li>Lyon: parcours Astrophysique commun au M2 des Sciences de la Matière de l’École Normale 
		Supérieure de Lyon et au M2 de Physique de l’Université Claude Bernard</li>
	<li>Marseille: parcours Astrophysique du M2 M3TPMA (Physique Theorique et Mathematique, Physique 
		des Particules et Astrophysique) de l’Université Aix-Marseille</li>
	<li>Montpellier: M2 CCP  (parcours Cosmos, Champs et Particules) du Master Physique et Ingénierie 
		de l’Université Montpellier 2</li>
	<li>Nice: spécialité IMAG2E (Imagerie et Modélisation Astrophysique Geophysique Espace et 
		Environnement) du Master de Physique Appliquée de l’Université Nice Sophia Antipolis</li>
	<li>Orléans: M2 SAE (Sciences de l’Atmosphère et de l’Espace) de l’Université d’Orléans</li>
	<li>Strasbourg: spécialité Astrophysique du M2R de Physique de l’Université de Strasbourg</li>
	<li>Toulouse: M2 ASEP (Astrophysique, Sciences de l’Espace, Planétologie) de l’Université Paul 
		Sabatier</li>
</ul>
<?php
*/
  }
}

stc_footer();


?>
