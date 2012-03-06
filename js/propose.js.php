<?php
require_once ('../lib/db_config.php');
echo "function update_counter(counter, length) {\n";
echo "    counter.text('reste '+(".$MAX_CHARS."-length)+' signes');\n";
echo "}\n";
echo "\n";
echo "$(function() {\n";
echo "    var counter = $('#counter');\n";
echo "    var textarea = $('[name=\"description\"]');\n";
echo "    textarea.bind('keyup',function(event) {\n";
echo "        update_counter(counter,event.currentTarget.value.length);\n";	
echo "    });\n";
echo "    update_counter(counter, textarea.text().length);\n";
echo "});";
?>