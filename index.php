<?php

require_once ('lib/db.php');
require_once ('lib/stc.php');


stc_top();

// menu
$menu = stc_menu_init();

if (stc_is_logged()) {
  stc_menu_add_section ($menu, 'Propositions de Thèses');
  stc_menu_add_item($menu, 'rechercher', 'test-1.php');
  stc_menu_add_item($menu, 'proposer', 'test-2.php');
  stc_menu_add_separator($menu);
  stc_menu_add_section ($menu, 'Propositions de Stages');
  stc_menu_add_item($menu, 'rechercher', 'test-1.php');
  stc_menu_add_item($menu, 'proposer', 'test-2.php');
} else {
  stc_menu_add_form($menu,"post", "login.php");
  stc_menu_form_add_text($menu,"Utilisateur","user");
  stc_menu_form_add_password($menu,"Mot de Passe","password");
  stc_menu_form_add_button($menu,"se connecter");
  stc_menu_form_end($menu);
  stc_menu_add_item($menu, "s'enregistrer", "register.php");
}
stc_menu($menu);

// contenu
?>
blah blah blah
<?php

stc_footer();


?>
