<?php 

// Make bash script for update client
include"../config.inc.php";

$sql_removeDir = "SELECT * FROM cccam_fline, cccam_servers WHERE cccam_fline.fline_id = cccam_servers.fline_id AND cccam_fline.ftp_active = '1'";
$query_removeDir = mysql_query($sql_removeDir) or die (mysql_error());

while($result_removeDir = mysql_fetch_assoc($query_removeDir)) {
$user = $result_removeDir['fline_username'];

	unlink("client/$user/CCcam.cfg");
	rmdir("client/$user");
	sleep(1);

}
$sql_user = "SELECT * FROM cccam_fline, cccam_servers WHERE cccam_fline.fline_id = cccam_servers.fline_id AND cccam_fline.ftp_active = '1'";
$query_user = mysql_query($sql_user) or die (mysql_error());
while($result_user = mysql_fetch_assoc($query_user)) {
	$user = $result_user['fline_username'];
	$pass = $result_user['fline_password'];
	$host = $result_user['server_host'];
	$port = $result_user['server_port'];
	$wemu = $result_user['server_wantemu'];
	$upho = $result_user['server_uphops'];
	
	
	$server = "C: " . $host . " " . $port . " " . $user . " " . $pass . " " . $wemu . " " . $upho. "\n";
	
	
	mkdir("client/$user",0777);
	touch("client/$user/CCcam.cfg");
	file_put_contents("client/$user/CCcam.cfg", $server, FILE_APPEND);
}




$sql_active = "SELECT * FROM cccam_fline, cccam_servers WHERE cccam_fline.fline_id = cccam_servers.fline_id AND cccam_fline.ftp_active = '1' GROUP BY cccam_servers.fline_id";
$query_active = mysql_query($sql_active);

// Create File to update client

if(file_exists("../script/client_update")) {
	unlink("../script/client_update");
	sleep(1);
}

exec("touch ../script/client_update");
$put = "#!/bin/bash";
$put .= "\n# Script By Sbarboff";
$put .= "\n\n";

while($result_active = mysql_fetch_assoc($query_active)) {
	$fline_name = $result_active['fline_username'];
	$fline_pass = $result_active['fline_password'];
	$clienthost = $result_active['ftp_ip'];
	$clientport = $result_active['ftp_port'];
	$clientlocal = $result_active['ftp_local'];
	$clientremote = $result_active['ftp_remote'];
	
	
	
	
	
	
	// Create bash Script
$put .= "LOCAL_DIR=\"$clientlocal\"\n";
$put .= "FILE=\"CCcam.cfg\"\n";
$put .= "REMOTE_DIR=\"$clientremote\"\n";
$put .= "IP_DREAM=\"$clienthost\"\n";
$put .= "PORT=\"$clientport\"\n";
$put .= "USERNAME=\"root\"\n";
$put .= "PASSOWRD=\"dreambox\"\n";
//$put .=  "ncftp -u \$USERNAME -p \$PASSOWRD -P \$PORT \$IP_DREAM";
$put .= "ftp -pinv \$IP_DREAM \$PORT<<END_SCRIPT\n";
$put .= "quote USER \$USERNAME\n";
$put .= "quote PASS \$PASSOWRD\n";
$put .= "delete \$REMOTE_DIR/\$FILE\n"; 
$put .= "put \$LOCAL_DIR/\$FILE \$REMOTE_DIR/\$FILE\n";
$put .= "quit\n";
$put .= "END_SCRIPT\n\n";

	

	
	
}
file_put_contents("../script/client_update", $put, FILE_APPEND);
chmod("../script/client_update", 0755);

?>