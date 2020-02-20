<?php 
include"../config.inc.php";
	$date = "###################\n";
	$date .= "## " . date("Y-m-d H:i:s") . " ##\n";
	$date .= "###################\n\n";
	file_put_contents("CCcam.cfg", $date, FILE_APPEND);
	
	
	$fline = "#########\n";
	$fline .= "## F-Line ##\n";
	$fline .= "#########\n\n";
	file_put_contents("CCcam.cfg", $fline, FILE_APPEND);
	
	$sql_fline = "SELECT * FROM cccam_fline WHERE fline_active = '1'";
	$query_fline = mysql_query($sql_fline);
	
	while($result_fline = mysql_fetch_assoc($query_fline)) {
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . " }";
					$chanlimit = NULL;
					$timelimit = NULL;
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = NULL;
					$timelimit = NULL;
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = NULL;
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { }";
					$timelimit = " { }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { }";
					$timelimit = " { }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = NULL;
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = "{ " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
		$fline_line = "F: ".$result_fline['fline_username']." ".$result_fline['fline_password']." ".$result_fline['fline_uphops']." ".$result_fline['fline_shareemus']." ".$result_fline['fline_allowemm'] ." " . $reshare . $chanlimit . $timelimit . $hostlimit . "\n";
	file_put_contents("CCcam.cfg", $fline_line, FILE_APPEND);
	}
	
	$cline = "\n#########\n";
	$cline .= "## C-Line ##\n";
	$cline .= "#########\n\n";
	file_put_contents("CCcam.cfg", $cline, FILE_APPEND); 
	
	$sql_cline = "SELECT * FROM cccam_cline WHERE cline_active = '1' ";
	$query_cline = mysql_query($sql_cline);
	
	while($result_cline = mysql_fetch_assoc($query_cline)) {
		
		$hops_val = $result_cline['cline_caid_id_hops'];
			 $wemu_val = $result_cline['cline_wantemus'];
			 $limit_val = $result_cline['cline_cardlimit'];
			 if($wemu_val == "yes") {
				 $wemu = "yes";
				 $hops = NULL;
				 
			 }
			 if($wemu_val == "no") {
				 $wemu = "no";
				 $hops = "{ " . $hops_val . " }";
				
			 }
			if($wemu_val == "no" && $limit_val != NULL) {
				 $wemu = "no";
				 $hops = "{ " . $hops_val.", " . $limit_val . " }";
				 
			 }
		
	$cline_line = "C: " . $result_cline['cline_hostname'] . " " . $result_cline['cline_port'] . " " . $result_cline['cline_username'] . " " . $result_cline['cline_password'] . " " . $wemu . " " . $hops . "\n";
	file_put_contents("CCcam.cfg", $cline_line, FILE_APPEND);
	}
	
	$nline = "\n#########\n";
	$nline .= "## N-Line ##\n";
	$nline .= "#########\n\n";
	file_put_contents("CCcam.cfg", $nline, FILE_APPEND);
	
	$sql_nline = "SELECT * FROM cccam_nline WHERE nline_active = '1'";
	$query_nline = mysql_query($sql_nline);
	
	while($result_nline = mysql_fetch_assoc($query_nline)) {
	
	$stealth = $result_nline['nline_stealth'];
			 if($stealth == "0") {
				 $stealth = NULL;
			 }
	
	$nline_line = "N: " . $result_nline['nline_hostname'] . " " . $result_nline['nline_port'] . " " . $result_nline['nline_username'] . " " . $result_nline['nline_password'] . " " . $result_nline['nline_des'] . " " . $result_nline['nline_hops'] . " " . $stealth . "\n";
	file_put_contents("CCcam.cfg", $nline_line, FILE_APPEND);
	}
	
	$lline = "\n#########\n";
	$lline .= "## L-Line ##\n";
	$lline .= "#########\n\n";
	file_put_contents("CCcam.cfg", $lline, FILE_APPEND);
	
	$sql_limit = "SELECT * FROM cccam_lline WHERE lline_active = '1'";
	$query_limit = mysql_query($sql_limit);
	
	while($result_lline = mysql_fetch_assoc($query_limit)) {
	
	$lline_line = "L: ".$result_lline['lline_hostname']." ".$result_lline['lline_port']." ".$result_lline['lline_username']." ".$result_lline['lline_password']." ".$result_lline['lline_caid'] . " " . $result_lline['lline_ident'] . " " . $result_lline['lline_hops'] ."\n";
	file_put_contents("CCcam.cfg", $lline_line, FILE_APPEND);
	}
	
	$rline = "\n#########\n";
	$rline .= "## R-Line ##\n";
	$rline .= "#########\n\n";
	file_put_contents("CCcam.cfg", $rline, FILE_APPEND);
	
	$sql_rline = "SELECT * FROM cccam_rline WHERE rline_active = '1'";
	$query_rline = mysql_query($sql_rline);
	
	while($result_rline=mysql_fetch_assoc($query_rline)) { 
	
	$rline_line ="R: ".$result_rline['rline_hostname']." ".$result_rline['rline_port']." ".$result_rline['rline_caid']." ".$result_rline['rline_ident']." ".$result_rline['rline_hops']."\n";
	file_put_contents("CCcam.cfg", $rline_line, FILE_APPEND);
	}
	
	$reader = "\n##########\n";
	$reader .= "## Reader ##\n";
	$reader .= "##########\n\n";
	file_put_contents("CCcam.cfg", $reader, FILE_APPEND);
	
	$sql_reader = "SELECT * FROM cccam_reader WHERE reader_active = '1'";
	$query_reader = mysql_query($sql_reader);
	
	while($result_reader = mysql_fetch_assoc($query_reader)) {
	
	$reader_line = $result_reader['reader_value_name'] . " " . $result_reader['reader_value'] . " " . $result_reader['reader_misc'] . "\n";
	file_put_contents("CCcam.cfg", $reader_line, FILE_APPEND);
	}
	
	
	$config = "\n#########\n";
	$config .= "## Config ##\n";
	$config .= "#########\n\n";
	file_put_contents("CCcam.cfg", $config, FILE_APPEND);
	
	$sql_config = "SELECT * FROM cccam_config WHERE config_active = '1'";
	$query_config = mysql_query($sql_config);
	
	while($result_config = mysql_fetch_assoc($query_config)) {
	
	$config_line = $result_config['config_value_name'] . " " . $result_config['config_value'] . "\n";
	file_put_contents("CCcam.cfg", $config_line, FILE_APPEND);
	}
?>