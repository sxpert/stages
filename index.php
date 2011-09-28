<?php

require_once ('lib/stc.php');

if (array_key_exists('from',$_REQUEST)) {
  $from = $_REQUEST['from'];
  stc_set_labo_provenance ($from);
}

stc_top();

// menu
$menu = stc_default_menu();
stc_menu($menu);

// contenu
?>
introduction au site
<?php

stc_footer();


?>
