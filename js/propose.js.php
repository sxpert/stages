<?php
require_once ('../lib/db_config.php');
echo "function update_counter(counter, length) {\n";
echo "    var remaining = ".$MAX_CHARS."-length;\n";
echo "    if (remaining<0) remaining = '<span style=\"color:red;\">'+remaining+'</span>';\n";
echo "    counter.html('reste '+remaining+' signes');\n";
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