<?php
/*
 * should be run with php7.0-cli:
 * ~sysosug $ php7.0 send-mails.php
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

// foreach($users as $user) {
//     echo $user['login']."\t".$user["email"]."\n";
// }
?>