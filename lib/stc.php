<?php

require_once('xhtml.php');

function stc_top() {
  xhtml_header();
  ?><head>
<title>Stages et Thèses</title>
<link rel="stylesheet" href="css/base.css"/>
</head>
<body>
<div id="top">logos et autres</div>
<?php
}

function stc_menu_init () {
}

function stc_menu_add () {
}

function stc_menu() {
  ?><div id="main">
<div id="menu"><?php
    echo "menu\n";
?></div>
<div id="content">s
<?php
}

function stc_footer() {
  ?></div></div>
<div id="footer">footer
</div>
</body>
</html>
<?php
}

?>