<?php
include("../function.inc.php");
include("../config.inc.php");

$sql = "SELECT * FROM cccam_user, cccam_quote WHERE cccam_user.user_id = cccam_quote.user_id";
$query = mysql_query($sql);


while($result = mysql_fetch_assoc($query)) {
$user_id = $result['user_id'];
$al = $result['quote_to'];

$tipo = "G";
$current=date("d-m-Y");
$rim = datediff($tipo, $current, $al);
	
	if($al == "-1") {	
				} else { 
				if($rim <= "0") { 
				$sql_update = "UPDATE cccam_fline SET fline_active = '0' WHERE cccam_fline.user_id = '$user_id'";
				mysql_query($sql_update); 
				
				}
				}
}
?>