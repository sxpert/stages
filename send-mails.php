<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

define("DEBUG", FALSE);

/*
 * should be run with php7.0-cli:
 * ~sysosug $ php7.0 send-mails.php
 * 
 * note:
 * the UGA mail server is pretty pedantic about not sending more 
 * than a mail every few seconds or so...
 * add this to /etc/postfix/main.cf:
 * 
 * -----
 * # delay mail sending because the UGA mail gateway is stupid
 * default_destination_rate_delay = 10s
 * initial_destination_concurrency = 1
 * default_destination_concurrency_limit = 1
 * -----
 * 
 * note: should check the current date against the date configured
 */
if (php_sapi_name() != 'cli') {
    echo "Fatal Error";
    exit(0);
}
echo "ok\n";
require_once('lib/stc.php');

$dba = db_connect_adm();
$res = pg_query($dba, "select login, email from users where account_valid='t' order by login;");
$users = pg_fetch_all($res);

$bo = date_parse_from_format('Y-m-d', stc_config_get('DATE_OUVERTURE'));
$loc = setlocale(LC_ALL, stc_config_get('LOCALE', 'nl_NL.UTF-8'));
$bo_str = strftime('%A %e %B %Y', mktime(0, 0, 0, $bo['month'], $bo['day'], $bo['year']));

foreach($users as $user) {
    $login = $user['login'];
    $email = $user['email'];

    $subject = "Ouverture du serveur de stages M2 Astro";
    $message =
    "Bonjour,\r\n".
    "\r\n".
    "Le serveur de stages http://stages-masters.sf2a.eu  est à nouveau\r\n".
    "accessible pour y déposer vos propositions de stage de M2.\r\n".
    "Celles-ci seront validées ensuite par les divers responsables des\r\n".
    "formations. Seuls les stages validés seront visibles par les\r\n".
    "étudiants d’une formation donnée, et ceci à partir d’un lien placé\r\n".
    "dans la page web de la formation elle-même.\r\n".
    "Les stages seront accessibles et consultables par les étudiants à\r\n".
    "partir du ".$bo_str.".\r\n".
    "Tout stage déposé après cette date pourra encore être validé au fil\r\n".
    "de l’eau par les divers responsables de formation et donc proposé aux\r\n".
    "étudiants.\r\n".
    "\r\n".
    "Votre identifiant (username) est: ".$login."\r\n".
    "Si vous avez oublié votre mot de passe, utilisez le formulaire\r\n".
    "disponible sur le serveur.\r\n";

   if ((!DEBUG) || (DEBUG && ($login=='sxpert'))) {
        echo sprintf("%-40s",$login).$email."\n";  
        //echo $message;      
        stc_send_email($email, $subject, $message);
   }
}
?>