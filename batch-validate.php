<?php
/*******************************************************************************
 *
 * Gestion des offres de stage de M2 en Astro
 * (c) Raphaël Jacquot 2011
 * Fichier sous licence GPL-3
 *
 ******************************************************************************/

require_once('lib/stc.php');

$method = $_SERVER['REQUEST_METHOD'];

$user = stc_user_id();
$admin = stc_is_admin();
$action = stc_get_variable($_POST, 'action');

/*
 * error if we are neither admin, nor we are here to batch-validate
 */
if (($method!='POST')||(!$admin)||($action!='batch-validate')) {
    stc_top();
    $menu = stc_default_menu();
    stc_menu($menu);
    echo "<h1>Accès interdit</h1>\n";
    stc_footer();    
    exit(0);
}

$multisel = stc_get_variable ($_POST, 'multisel');
$return_to = stc_get_variable ($_POST, 'return_to');

$debug = FALSE;
if ($return_to=="") 
    $debug = TRUE;

if($debug) {
    stc_top();
    $menu = stc_default_menu();
    stc_menu($menu);
    echo "<pre>";
    echo "user\n";
    print_r($user);
    echo "\nadmin\n";
    if ($admin==TRUE) echo "true";
    else print_r($admin);
    echo "\n_REQUEST\n";
    print_r($_REQUEST);
    echo "\nmultisel\n";
    print_r($multisel);
    echo "\n";
}
#
# do the changes
#

# first, obtain which m2 we have to validate for
$m2_list = array();
if ($admin==TRUE) {
    # we are superadmin, validate everything
    $res = pg_query($db, "select id from m2 where active='t' order by id;");
    $list = pg_fetch_all($res);
    foreach($list as $m2)
        array_push($m2_list, $m2['id']);
} else {
    # only one m2: the one we're the admin for
    array_push($m2_list, $admin);
}
if ($debug) print_r($m2_list);
 
# loop on each offer
foreach($multisel as $offer) {
    if ($debug) {
        echo "offer ";
        print_r($offer);
        echo ": ";
    }

    $res = pg_query_params($db, "select id_m2 from offres_m2 where id_offre=$1 order by id_m2;", array($offer));
    $list = pg_fetch_all($res);
    $validated = array();
    foreach($list as $m2)
        array_push($validated, $m2['id_m2']);
    $validate_list = array_diff($m2_list, $validated);
    foreach($validate_list as $m2) {
        $res = pg_query_params($db, "insert into offres_m2 (id_offre, id_m2) values ($1, $2);", array($offer, $m2));
        if ($debug) echo "$m2 ";
    }
    if ($debug) echo "\n";
}
    
if ($debug) {
    echo "</pre>";
    stc_footer();
}
   
if (!$debug) header('Location: '.$return_to);

?>