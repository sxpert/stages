<?php

// injects the country database

// loads the json

$j = file_get_contents ("iso-3166-1.json");
$jso = json_decode ($j);
if (is_null($jso) {
	echo json_last_error_msg ();
} else var_dump ($jso);
?>
