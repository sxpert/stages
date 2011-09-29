<?php

require_once('lib/stc.php');

stc_top();

$options = array();
$options['register']=False;
$menu = stc_default_menu($options);

stc_menu($menu);

// formulaire d'enregistrement
?>

<?php

stc_footer();

?>