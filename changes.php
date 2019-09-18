<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once ('lib/stc.php');

stc_top();
$menu = stc_default_menu();
stc_menu($menu);
?>

<h1>version 2019</h1>
<p>
    <ul>
        <li>La date de changement d'année est maintenant une variable de configuration</li>
        <li>Il est maintenant possible de valider un ensemble de proposition en une seule fois (uniquement pour l'année en cours)</li>
        <li>L'envoi de message aux administrateurs (du site et des M2) envoie un mail a chaque responsable concerné</li>
        <li>Un mail est envoyé a l'utilisateur dont le compte est bloqué</li>
        <li>Un mail est envoyé a tout le monde a la date d'ouverture du serveur</li>
    </ul>
</p>

<?php
stc_footer();
?>