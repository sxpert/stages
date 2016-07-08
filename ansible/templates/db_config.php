<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/
$EN_TRAVAUX=False;
$SERVER_EMAIL='{{mail_sender}}@{{inventory_hostname}}';
$TZ='Europe/Paris';

$MAX_CHARS=2500;

$DB_HOST="localhost";
$DB_PORT="5432";
$DB_NAME="{{bdd_name}}";
$DB_USER="{{bdd_user}}";
$DB_PASS="{{db_users[bdd_user]}}";
$DB_ADMIN_USER="{{bdd_owner}}";
$DB_ADMIN_PASS="{{db_users[bdd_owner]}}";

$MAIL_SRV="{{inventory_hostname}}";

$JQUERY_VER="1.11.1";
$JQUERYUI_VER="1.11.1";
$JQUERYUI_THEME="smoothness";

// mois / jour
$BLACKOUT_DATE= [10,21];

$HTTP_OPTS=array(
		 'timeout'=>5,
#        'proxyhost'=>'www-cache.ujf-grenoble.fr',
#        'proxyport'=>3128,
#        'proxytype'=>HTTP_PROXY_HTTP
		 );

$DEBUG=false;
$DEBUG_IPS=array();

?>
