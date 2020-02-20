<?php
$host = "138.201.82.18"; // Host name Normally 'localhost'
$user = "monzteruser"; // MySQL login username
$pass = "pwdfb7"; // MySQL login password
$database = "cccam"; // Database name

$cccam_info_dir = "/var/www/vhosts/nexva_v2/trunk/public/cccam/download/";
$script_dir = "/var/www/vhosts/nexva_v2/trunk/public/cccam/script/";
 
$conn = mysql_connect($host, $user, $pass) or die (mysql_error());
mysql_select_db($database, $conn) or die (mysql_error());
?>
