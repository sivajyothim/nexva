<?php 

if(isset($_POST['update']) && $_POST['update'] == "edit_channel") {
	include"../config.inc.php";
	$size = count($_POST['chan_caid']);
	$i = 0;
	while($i < $size) {
		$chan_id =  $_POST['chan_id'][$i];
		$chan_caid = $_POST['chan_caid'][$i];
		$chan_ident = $_POST['chan_ident'][$i];
		$chan_chaid = $_POST['chan_chaid'][$i];
		$chan_provider = $_POST['chan_provider'][$i];
		$chan_channel_name = $_POST['chan_channel_name'][$i];
		$chan_category = $_POST['chan_category'][$i];
		$chan_encription = $_POST['chan_encription'][$i];
		$chan_sat = $_POST['chan_sat'][$i];
		$sql = "UPDATE cccam_channelinfo SET chan_caid = '$chan_caid', chan_ident = '$chan_ident', chan_chaid = '$chan_chaid', chan_provider = '$chan_provider', chan_channel_name = '$chan_channel_name', chan_encription = '$chan_encription', chan_sat = '$chan_sat', chan_category = '$chan_category' WHERE chan_id = '$chan_id' ";
		mysql_query($sql);
		$i++;
	}
header("Refresh:3; URL=list_channel.php?page=1");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Modifica effettuata con successo!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}

if(isset($_POST['ins_mu']) && $_POST['ins_mu'] == "multiple") {
include"../config.inc.php";
if((!empty($_FILES["uploaded_file"])) && ($_FILES['uploaded_file']['error'] == 0)) {
  //Check if the file is txt plain and it's size is less than 500Kb
  $filename = basename($_FILES['uploaded_file']['name']);
  $ext = substr($filename, strrpos($filename, '.') + 1);
  if (($ext == "txt") && ($_FILES["uploaded_file"]["type"] == "text/plain") && 
    ($_FILES["uploaded_file"]["size"] < 500000)) {
    //Determine the path to which we want to save this file
      $newname = dirname(__FILE__).'/upload/'.$filename;
      //Check if the file with the same name is already exists on the server
      if (!file_exists($newname)) {
        //Attempt to move the uploaded file to it's new place
        if ((move_uploaded_file($_FILES['uploaded_file']['tmp_name'],$newname))) {
           $msg = "It's done! The file has been saved and inserted!";
		   $msg .= "<br><span class='Testo'><a href='list.php'>Return to list<a></span>";
		   $sql = "LOAD DATA LOCAL INFILE '$newname' INTO TABLE `cccam_channelinfo` FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n'";
		   mysql_query($sql) or die (mysql_error());
		   unlink($newname);
		   header("Refresh:3; URL=list_channel.php?page=1");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Inserimento multiplo avvenunto con successo!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
        } else {
           $msg = "Error: A problem occurred during file upload!";
		   		   header("Refresh:3; URL=add_channel.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "$msg</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
        }
      } else {
         $msg = "Error: File ".$_FILES["uploaded_file"]["name"]." already exists";
		 		   		   header("Refresh:3; URL=add_channel.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "$msg</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
      }
  } else {
     $msg = "Error: Only .txt file under 500Kb are accepted for upload";
	 		   		   header("Refresh:3; URL=add_channel.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "$msg</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
  }
} else {
$msg = "Error: No file uploaded";
		   		   header("Refresh:3; URL=add_channel.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "$msg</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}
}
if(isset($_POST['ins_si']) && $_POST['ins_si'] == "single") {
	include"../config.inc.php";
		$chan_caid = $_POST['chan_caid'];
		$chan_ident = $_POST['chan_ident'];
		$chan_chaid = $_POST['chan_chaid'];
		$chan_provider = $_POST['chan_provider'];
		$chan_channel_name = $_POST['chan_channel_name'];
		$chan_category = $_POST['chan_category'];
		$chan_encription = $_POST['chan_encription'];
		$chan_sat = $_POST['chan_sat'];
			$sql_insert = "INSERT INTO cccam_channelinfo ( chan_id, chan_caid, chan_ident, chan_chaid, chan_provider, chan_category, chan_channel_name, chan_encription, chan_sat ) VALUES ( NULL, '$chan_caid', '$chan_ident', '$chan_chaid', '$chan_provider', '$chan_category', '$chan_channel_name', '$chan_encription', '$chan_sat' )";
			$result = mysql_query($sql_insert) or die (mysql_error());
				if($result > 0) {
				header("Refresh:3; URL=list_channel.php?page=1");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Inserimento avvenuto con successo</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	} else {
		header("Refresh:3; URL=add_channel.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Errore inserimento. Riprova.</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	}
}

if(isset($_POST['del']) && $_POST['del'] == "channel") {
	include"../config.inc.php";
	$size = count($_POST['chan_id']);
	$i = 0;
	while($i < $size) {
		$chan_id =  $_POST['chan_id'][$i];
		
		$sql = "DELETE FROM cccam_channelinfo WHERE chan_id = '$chan_id'";
		mysql_query($sql);
		$i++;
	}
	header("Refresh:3; URL=list_channel.php?page=1");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Delete Success!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}

if(isset($_POST['add']) && $_POST['add'] == "user") {
	include"../config.inc.php";
	$user_name = $_POST['user_name'];
	$user_surname = $_POST['user_surname'];
	$user_street = $_POST['user_street'];
	$user_number = $_POST['user_number'];
	$user_zip_code = $_POST['user_zip_code'];
	$user_city = $_POST['user_city'];
	$user_phone = $_POST['user_phone'];
	$user_email = $_POST['user_email'];
	$user_level = $_POST['user_level'];
	$user_username = $_POST['user_username'];
	$user_password = $_POST['user_password'];
	$user_note = $_POST['user_note'];
	$fline_username = $_POST['fline_username'];
	$fline_password = $_POST['fline_password'];
	$fline_uphops = $_POST['fline_uphops'];
	$fline_shareemus = $_POST['fline_shareemus'];
	$fline_allowemm = $_POST['fline_allowemm'];
	$fline_reshare = $_POST['fline_reshare'];
	$fline_cardlimit = $_POST['fline_cardlimit'];
	$fline_cardlimitHops = $_POST['fline_cardlimitHops'];
	$fline_chanlimit = $_POST['fline_chanlimit'];
	$fline_timeFrom = $_POST['fline_timeFrom'];
	$fline_timeTo = $_POST['fline_timeTo'];
	$fline_hostlimit = $_POST['fline_hostlimit'];
	$quote_value = $_POST['quote_value'];
	$quote_from = $_POST['quote_from'];
	$quote_to = $_POST['quote_to'];
	$ftp_active = $_POST['ftp_active'];
	$ftp_ip = $_POST['ftp_ip'];
	$ftp_port = $_POST['ftp_port'];
	$ftp_user = $_POST['ftp_user'];
	$ftp_pass = $_POST['ftp_pass'];
	$ftp_remote = $_POST['ftp_remote'];
	$ftp_local = $_POST['ftp_local'];
	$server = $_POST['server_id'];
	
	// Insert User
	
	$sql_ins_user = "INSERT INTO cccam_user ( user_id, user_name, user_surname, user_street, user_number, user_zip_code, user_city, user_phone, user_email, user_level, user_username, user_password, user_note ) VALUES ( NULL, '$user_name', '$user_surname', '$user_street', '$user_number', '$user_zip_code', '$user_city', '$user_phone', '$user_email', '$user_level', '$user_username', '$user_password', '$user_note' )";
	mysql_query($sql_ins_user) or die (mysql_error());
	$insert_id=mysql_insert_id();
	
	
	// Insert Fline
	
	// Retrive value cardlimit /////////////////
	
	$size = count($fline_cardlimit);
	$i = 0;
	while($i < $size) {
		$caid = $fline_cardlimit[$i];
		$limit = $fline_cardlimitHops[$i];
		if($limit == "" && $caid == "") {
		} else {
		$value = ", ".$caid.$limit;
		file_put_contents("card_limit.txt", $value, FILE_APPEND);
		}
		
		$i++;
	}
	// Remove ", " first 2 char
	$remove_cardlimit_char = file_get_contents("card_limit.txt");
	// Value To Insert
	$mod_cardlimit = substr($remove_cardlimit_char, 2);
	///////////////////////////////////////////



	// Retrive value chanlimit ////////////////

	foreach($fline_chanlimit as $value) {
		$chan_limit = ", ".$value;
		file_put_contents("chan_limit.txt", $chan_limit, FILE_APPEND);
	}
			// Remove ", " first 2 char
			$remove_chanlimit_char = file_get_contents("chan_limit.txt");
			// Value To Insert
			$mod_chanlimit = substr($remove_chanlimit_char, 2);
	///////////////////////////////////////////
	
	// Retrive time limit
	
	if($fline_timeFrom == "" && $fline_timeTo == "") {
		$time = "";
	} else {
		$time = $fline_timeFrom . "-" . $fline_timeTo;
	}
	// Insert into db
	
	
	
	
	
	if($ftp_active == "0") {
	
	$sql_insert_fline = "INSERT INTO cccam_fline ( fline_id, user_id, fline_active, fline_username, fline_password, fline_uphops, fline_shareemus, fline_allowemm, fline_reshare, fline_cardlimit, fline_chanlimit, fline_timelimit, fline_hostlimit, ftp_active, ftp_ip, ftp_port, ftp_user, ftp_pass, ftp_local, ftp_remote ) VALUES ( NULL, '$insert_id', '1', '$fline_username', '$fline_password', '$fline_uphops', '$fline_shareemus', '$fline_allowemm', '$fline_reshare', '$mod_cardlimit', '$mod_chanlimit', '$time', '$fline_hostlimit', '0', '-', '-', '-', '-', '-', '-' )";
	mysql_query($sql_insert_fline) or die (mysql_error());
	
		// Last id insert for fline
	$last_fline_id1=mysql_insert_id();

	// Insert server selected into db
	$size = count($server);
	$i = 0;
	while($i < $size) {
		$server_id = $server[$i];
		$select_server = "SELECT * FROM cccam_server_list WHERE server_id = '$server_id'";
		$select_query = mysql_query($select_server);
		$select_result = mysql_fetch_assoc($select_query);
		
		$host = $select_result['server_host'];
		$port = $select_result['server_port'];
		$wemu = $select_result['server_wantemu'];
		$upho = $select_result['server_uphops'];
		$sql_server = "INSERT INTO cccam_servers ( server_id, fline_id, server_list_id, server_host, server_port, server_wantemu, server_uphops ) VALUE ( NULL, '$last_fline_id1', '$server_id', '$host', '$port', '$wemu', '$upho' )";
		mysql_query($sql_server);
		
		$i++;
	}
	
	
	} else {
	$sql_insert_fline = "INSERT INTO cccam_fline ( fline_id, user_id, fline_active, fline_username, fline_password, fline_uphops, fline_shareemus, fline_allowemm, fline_reshare, fline_cardlimit, fline_chanlimit, fline_timelimit, fline_hostlimit, ftp_active, ftp_ip, ftp_port, ftp_user, ftp_pass, ftp_local, ftp_remote ) VALUES ( NULL, '$insert_id', '1', '$fline_username', '$fline_password', '$fline_uphops', '$fline_shareemus', '$fline_allowemm', '$fline_reshare', '$mod_cardlimit', '$mod_chanlimit', '$time', '$fline_hostlimit', '1', '$ftp_ip', '$ftp_port', '$ftp_user', '$ftp_pass', '$ftp_local$fline_username', '$ftp_remote' )";
	mysql_query($sql_insert_fline) or die (mysql_error());
	
	// Last id insert for fline
	$last_fline_id2=mysql_insert_id();
	
	// Insert server selected into db
	$size = count($server);
	$i = 0;
	while($i < $size) {
		$server_id = $server[$i];
		$select_server = "SELECT * FROM cccam_server_list WHERE server_id = '$server_id'";
		$select_query = mysql_query($select_server);
		$select_result = mysql_fetch_assoc($select_query);
		
		$host = $select_result['server_host'];
		$port = $select_result['server_port'];
		$wemu = $select_result['server_wantemu'];
		$upho = $select_result['server_uphops'];
		$sql_server = "INSERT INTO cccam_servers ( server_id, fline_id, server_list_id, server_host, server_port, server_wantemu, server_uphops ) VALUE ( NULL, '$last_fline_id2', '$server_id', '$host', '$port', '$wemu', '$upho' )";
		mysql_query($sql_server);
		
		$i++;
	}
	
	}



// Remove chan_limit.txt and card_limit.txt

exec('rm chan_limit.txt');
exec('rm card_limit.txt');

	// Insert Quote
	
	if($_POST['check1'] == "1") {
		
	$sql_insert_quote = "INSERT INTO cccam_quote ( quote_id, user_id, quote_value, quote_from, quote_to ) VALUES ( NULL, '$insert_id', '-1', '-1', '-1' )";
	mysql_query($sql_insert_quote) or die ("Errore query cccam_quote : " . mysql_error());
	} else {
	$sql_insert_quote = "INSERT INTO cccam_quote ( quote_id, user_id, quote_value, quote_from, quote_to ) VALUES ( NULL, '$insert_id', '$quote_value', '$quote_from', '$quote_to' )";
	mysql_query($sql_insert_quote) or die ("Errore query cccam_quote : " . mysql_error());
	}

	header("Refresh:3; URL=add_user.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "User Added. Success!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";

}

if(isset($_POST['edit']) && $_POST['edit'] == "user") {
	include"../config.inc.php";
	$user_id = $_POST['user_id'];
	$user_name = $_POST['user_name'];
	$user_surname = $_POST['user_surname'];
	$user_street = $_POST['user_street'];
	$user_number = $_POST['user_number'];
	$user_zip_code = $_POST['user_zip_code'];
	$user_city = $_POST['user_city'];
	$user_phone = $_POST['user_phone'];
	$user_email = $_POST['user_email'];
	$user_level = $_POST['user_level'];
	$user_note = $_POST['user_note'];
		$sql_update_user = "UPDATE cccam_user SET user_name = '$user_name', user_surname = '$user_surname', user_street = '$user_street', user_number = '$user_number', user_zip_code = '$user_zip_code', user_city = '$user_city', user_phone = '$user_phone', user_email = '$user_email', user_level = '$user_level', user_note = '$user_note' WHERE user_id = '$user_id' ";
		mysql_query($sql_update_user) or die (mysql_error());
	header("Refresh:3; URL=list_user_detail.php?user_id=$user_id");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "User Edit. Success!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}
	
if(isset($_POST['del']) && $_POST['del'] == "user") {
	include"../config.inc.php";
	$size = count($_POST['del_user_id']);
	$i = 0;
	while($i < $size) {
		$del_user_id =  $_POST['del_user_id'][$i];
		
		echo $fline_id;
		$sql = "DELETE FROM cccam_user WHERE user_id = '$del_user_id'";
		mysql_query($sql);
		
		$sql_fline = "DELETE FROM cccam_fline WHERE user_id = '$del_user_id'";
		mysql_query($sql_fline);
		
		$sql_quote = "DELETE FROM cccam_quote WHERE user_id = '$del_user_id'";
		mysql_query($sql_quote);
		
		
		
		$i++;
	}
	$fline_id = $_POST['fline_id'];
	foreach($fline_id as $value){
	
	$sql_server = "DELETE FROM cccam_servers WHERE fline_id = '$value'";
	mysql_query($sql_server);
	
	}
	header("Refresh:3; URL=list_user.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "User Delete Success!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}
	
	
if(isset($_POST['edit']) && $_POST['edit'] == "quote") {
	include"../config.inc.php";
	$user_id = $_POST['user_id'];
	$quote_value = $_POST['quote_value'];
	$quote_from = $_POST['quote_from'];
	$quote_to = $_POST['quote_to'];
	
	if($_POST['check1'] == "1") {
	
		$sql_update_quote = "UPDATE cccam_quote SET quote_value = '-1', quote_from = '-1', quote_to = '-1' WHERE user_id = '$user_id' ";
		mysql_query($sql_update_quote) or die (mysql_error());
		} else {
		$sql_update_quote = "UPDATE cccam_quote SET quote_value = '$quote_value', quote_from = '$quote_from', quote_to = '$quote_to' WHERE user_id = '$user_id' ";
		mysql_query($sql_update_quote) or die (mysql_error());
}
	header("Refresh:3; URL=list_user_detail.php?user_id=$user_id");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "User Quote. Updated!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}	
	
if(isset($_POST['add']) && $_POST['add'] == "cardlimit") {
	include"../config.inc.php";
	$fline_id = $_POST['fline_id'];
	$cardlimit = $_POST['fline_cardlimit'];
	$hops = $_POST['fline_cardlimitHops'];
	
	$sql_cardlimit = "SELECT * FROM cccam_fline WHERE fline_id = '$fline_id'";
	$query_cardlimit = mysql_query($sql_cardlimit) or die (mysql_error());
	
	$result_cardlimit = mysql_fetch_assoc($query_cardlimit);
	
	if($result_cardlimit['fline_cardlimit'] == NULL) {
		$value = $cardlimit.$hops;
		$sql = "UPDATE cccam_fline SET fline_cardlimit = '$value' WHERE fline_id = '$fline_id'";
		mysql_query($sql) or die (mysql_error());
			header("Refresh:3; URL=edit_fline.php?fline_id=$fline_id");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Card Limit. Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	} else {
		$value = $result_cardlimit['fline_cardlimit'] . ", " . $cardlimit  .$hops;
		$sql = "UPDATE cccam_fline SET fline_cardlimit = '$value' WHERE fline_id = '$fline_id'";
		mysql_query($sql) or die (mysql_error());
				header("Refresh:3; URL=edit_fline.php?fline_id=$fline_id");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Card Limit. Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	}
	
}
if(isset($_POST['update']) && $_POST['update'] == "fline") {
	include"../config.inc.php";
	$fline_id = $_POST['fline_id'];
	$fline_uphops = $_POST['fline_uphops'];
	$fline_shareemus = $_POST['fline_shareemus'];
	$fline_allowemm = $_POST['fline_allowemm'];
	$fline_reshare = $_POST['fline_reshare'];
	$fline_cardlimit = $_POST['fline_cardlimit'];
	$fline_chanlimit = $_POST['fline_chanlimit'];
	$fline_timelimit = $_POST['fline_timelimit'];
	$fline_hostlimit = $_POST['fline_hostlimit'];
	$ftp_active = $_POST['ftp_active'];
	$ftp_ip = $_POST['ftp_ip'];
	$ftp_port = $_POST['ftp_port'];
	$ftp_user = $_POST['ftp_user'];
	$ftp_pass = $_POST['ftp_pass'];
	$ftp_local = $_POST['ftp_local'];
	$ftp_remote = $_POST['ftp_remote'];
	
	exec("rm chan_limit.txt");
foreach($fline_chanlimit as $value) {
		$chan_limit = ", ".$value;
		file_put_contents("chan_limit.txt", $chan_limit, FILE_APPEND);
	}
			// Remove ", " first 2 char
			$remove_chanlimit_char = file_get_contents("chan_limit.txt");
			// Value To Insert
			$mod_chanlimit = substr($remove_chanlimit_char, 2);
	///////////////////////////////////////////
	
	$sql_update_fline = "UPDATE cccam_fline SET fline_uphops = '$fline_uphops', fline_shareemus = '$fline_shareemus', fline_allowemm = '$fline_allowemm', fline_reshare = '$fline_reshare', fline_cardlimit = '$fline_cardlimit', fline_chanlimit = '$mod_chanlimit', fline_timelimit = '$fline_timelimit', fline_hostlimit = '$fline_hostlimit', ftp_active = '$ftp_active', ftp_ip = '$ftp_ip', ftp_port = '$ftp_port', ftp_user = '$ftp_user', ftp_pass = '$ftp_pass', ftp_local = '$ftp_local', ftp_remote = '$ftp_remote' WHERE fline_id = '$fline_id'";
	mysql_query($sql_update_fline);
	
	
	
	/////////////////////////////////
	
	$delete = "DELETE FROM cccam_servers WHERE fline_id = '$fline_id'";
	mysql_query($delete);
	$size = count($_POST['server_id']);
	$i = 0;
	while($i < $size) {
		$server_id = $_POST['server_id'][$i];
		$select_sql = "SELECT * FROM cccam_server_list WHERE server_id = '$server_id'";
		$select_query = mysql_query($select_sql) or die (mysql_error());
		$select_result = mysql_fetch_assoc($select_query);
		$rows = mysql_num_rows($select_query);
		
		
		$host = $select_result['server_host'];
		$port = $select_result['server_port'];
		$wemu = $select_result['server_wantemu'];
		$upho = $select_result['server_uphops'];
		
		
		if($rows <= 0) {
		} else {
		$sql_server = "INSERT INTO cccam_servers ( server_id, fline_id, server_list_id, server_host, server_port, server_wantemu, server_uphops ) VALUES ( NULL, '$fline_id', '$server_id', '$host', '$port', '$wemu', '$upho')";
		mysql_query($sql_server);
		}
			
	$i++;
	}
	
	
	header("Refresh:3; URL=list_fline.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "F-Line Updated!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";

}
	
if(isset($_POST['add']) && $_POST['add'] == "newfline") {
	include"../config.inc.php";
	$user_id = $_POST['user_id'];
	$fline_username = $_POST['fline_username'];
	$fline_password = $_POST['fline_password'];
	$fline_uphops = $_POST['fline_uphops'];
	$fline_shareemus = $_POST['fline_shareemus'];
	$fline_allowemm = $_POST['fline_allowemm'];
	$fline_reshare = $_POST['fline_reshare'];
	$fline_cardlimit = $_POST['fline_cardlimit'];
	$fline_cardlimitHops = $_POST['fline_cardlimitHops'];
	$fline_chanlimit = $_POST['fline_chanlimit'];
	$fline_timeFrom = $_POST['fline_timeFrom'];
	$fline_timeTo = $_POST['fline_timeTo'];
	$fline_hostlimit = $_POST['fline_hostlimit'];
	$quote_value = $_POST['quote_value'];
	$quote_from = $_POST['quote_from'];
	$quote_to = $_POST['quote_to'];
    $ftp_active = $_POST['ftp_active'];
	$ftp_ip = $_POST['ftp_ip'];
	$ftp_port = $_POST['ftp_port'];
	$ftp_user = $_POST['ftp_user'];
	$ftp_pass = $_POST['ftp_pass'];
	$ftp_remote = $_POST['ftp_remote'];
	$ftp_local = $_POST['ftp_local'];
    $server = $_POST['server_id'];
	
	
// Insert Fline
	
	// Retrive value cardlimit /////////////////
	
	$size = count($fline_cardlimit);
	$i = 0;
	while($i < $size) {
		$caid = $fline_cardlimit[$i];
		$limit = $fline_cardlimitHops[$i];
		if($limit == "" && $caid == "") {
		} else {
		$value = ", ".$caid.$limit;
		file_put_contents("card_limit.txt", $value, FILE_APPEND);
		}
		
		$i++;
	}
	// Remove ", " first 2 char
	$remove_cardlimit_char = file_get_contents("card_limit.txt");
	// Value To Insert
	$mod_cardlimit = substr($remove_cardlimit_char, 2);
	///////////////////////////////////////////


	// Retrive value chanlimit ////////////////

	foreach($fline_chanlimit as $value) {
		$chan_limit = ", ".$value;
		file_put_contents("chan_limit.txt", $chan_limit, FILE_APPEND);
	}
			// Remove ", " first 2 char
			$remove_chanlimit_char = file_get_contents("chan_limit.txt");
			// Value To Insert
			$mod_chanlimit = substr($remove_chanlimit_char, 2);
	///////////////////////////////////////////
	
	// Retrive time limit
	
	if($fline_timeFrom == "" && $fline_timeTo == "") {
		$time = "";
	} else {
		$time = $fline_timeFrom . "-" . $fline_timeTo;
	}
	// Insert into db
	
	if($ftp_active == "0") {
	
	$sql_insert_fline = "INSERT INTO cccam_fline ( fline_id, user_id, fline_active, fline_username, fline_password, fline_uphops, fline_shareemus, fline_allowemm, fline_reshare, fline_cardlimit, fline_chanlimit, fline_timelimit, fline_hostlimit, ftp_active, ftp_ip, ftp_port, ftp_user, ftp_pass, ftp_local, ftp_remote ) VALUES ( NULL, '$user_id', '1', '$fline_username', '$fline_password', '$fline_uphops', '$fline_shareemus', '$fline_allowemm', '$fline_reshare', '$mod_cardlimit', '$mod_chanlimit', '$time', '$fline_hostlimit', '0', '-', '-', '-', '-', '-', '-' )";
	mysql_query($sql_insert_fline) or die (mysql_error());
	
		// Last id insert for fline
	$last_fline_id1=mysql_insert_id();

	// Insert server selected into db
	$size = count($server);
	$i = 0;
	while($i < $size) {
		$server_id = $server[$i];
		$select_server = "SELECT * FROM cccam_server_list WHERE server_id = '$server_id'";
		$select_query = mysql_query($select_server);
		$select_result = mysql_fetch_assoc($select_query);
		
		$host = $select_result['server_host'];
		$port = $select_result['server_port'];
		$wemu = $select_result['server_wantemu'];
		$upho = $select_result['server_uphops'];
		$sql_server = "INSERT INTO cccam_servers ( server_id, fline_id, server_list_id, server_host, server_port, server_wantemu, server_uphops ) VALUE ( NULL, '$last_fline_id1', '$server_id', '$host', '$port', '$wemu', '$upho' )";
		mysql_query($sql_server);
		
		$i++;
	}
	
	
	} else {
	$sql_insert_fline = "INSERT INTO cccam_fline ( fline_id, user_id, fline_active, fline_username, fline_password, fline_uphops, fline_shareemus, fline_allowemm, fline_reshare, fline_cardlimit, fline_chanlimit, fline_timelimit, fline_hostlimit, ftp_active, ftp_ip, ftp_port, ftp_user, ftp_pass, ftp_local, ftp_remote ) VALUES ( NULL, '$user_id', '1', '$fline_username', '$fline_password', '$fline_uphops', '$fline_shareemus', '$fline_allowemm', '$fline_reshare', '$mod_cardlimit', '$mod_chanlimit', '$time', '$fline_hostlimit', '1', '$ftp_ip', '$ftp_port', '$ftp_user', '$ftp_pass', '$ftp_local$fline_username', '$ftp_remote' )";
	mysql_query($sql_insert_fline) or die (mysql_error());
	
	// Last id insert for fline
	$last_fline_id2=mysql_insert_id();
	
	// Insert server selected into db
	$size = count($server);
	$i = 0;
	while($i < $size) {
		$server_id = $server[$i];
		$select_server = "SELECT * FROM cccam_server_list WHERE server_id = '$server_id'";
		$select_query = mysql_query($select_server);
		$select_result = mysql_fetch_assoc($select_query);
		
		$host = $select_result['server_host'];
		$port = $select_result['server_port'];
		$wemu = $select_result['server_wantemu'];
		$upho = $select_result['server_uphops'];
		$sql_server = "INSERT INTO cccam_servers ( server_id, fline_id, server_list_id, server_host, server_port, server_wantemu, server_uphops ) VALUE ( NULL, '$last_fline_id2', '$server_id', '$host', '$port', '$wemu', '$upho' )";
		mysql_query($sql_server);
		
		$i++;
	}
	
	}
	
	
	
	/* $sql_insert_fline = "INSERT INTO cccam_fline ( fline_id, user_id, fline_active, fline_username, fline_password, fline_uphops, fline_shareemus, fline_allowemm, fline_reshare, fline_cardlimit, fline_chanlimit, fline_timelimit, fline_hostlimit ) VALUES ( NULL, '$user_id', '1', '$fline_username', '$fline_password', '$fline_uphops', '$fline_shareemus', '$fline_allowemm', '$fline_reshare', '$mod_cardlimit', '$mod_chanlimit', '$time', '$fline_hostlimit' )";
	mysql_query($sql_insert_fline) or die (mysql_error());
*/
// Remove chan_limit.txt and card_limit.txt

exec('rm chan_limit.txt');
exec('rm card_limit.txt');
	header("Refresh:3; URL=list_fline.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "F-Line Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}

if(isset($_POST['edit_cline']) && $_POST['edit_cline'] == "ok") {
	include"../config.inc.php";
	$cline_id = $_POST['cline_id'];
	$user_id = $_POST['user_id'];
	$cline_host = $_POST['cline_hostname'];
	$cline_port = $_POST['cline_port'];
	$cline_user = $_POST['cline_username'];
    $cline_pass = $_POST['cline_password'];
    $cline_wemu = $_POST['cline_wantemus'];
	$cline_resh = $_POST['cline_reshare'];
	$cline_clim = $_POST['cline_cardlimit'];
	
	if($cline_wemu == "no") {
		$sql_update_cline = "UPDATE cccam_cline SET cline_hostname = '$cline_host', cline_port = '$cline_port', cline_username = '$cline_user', cline_password = '$cline_pass', cline_wantemus = '$cline_wemu', cline_caid_id_hops = '$cline_resh', cline_cardlimit = '$cline_clim', user_id = '$user_id' WHERE cline_id = '$cline_id' ";
		mysql_query($sql_update_cline);
	} else {
		$sql_update_cline = "UPDATE cccam_cline SET cline_hostname = '$cline_host', cline_port = '$cline_port', cline_username = '$cline_user', cline_password = '$cline_pass', cline_wantemus = '$cline_wemu', cline_caid_id_hops = '', cline_cardlimit = '' user_id = '$user_id' WHERE cline_id = '$cline_id' ";
		mysql_query($sql_update_cline);
	}
		header("Refresh:3; URL=edit_cline.php?cline_id=$cline_id");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "C Line Updated!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}

if(isset($_POST['add']) && $_POST['add'] == "cardlimit_cline") {
	include"../config.inc.php";
	$cline_id = $_POST['cline_id'];
	$cardlimit = $_POST['cline_cardlimit'];
	$hops = $_POST['cline_cardlimitHops'];
	
	$sql_cardlimit = "SELECT * FROM cccam_cline WHERE cline_id = '$cline_id'";
	$query_cardlimit = mysql_query($sql_cardlimit) or die (mysql_error());
	
	$result_cardlimit = mysql_fetch_assoc($query_cardlimit);
	
	if($result_cardlimit['cline_cardlimit'] == NULL) {
		$value = $cardlimit.$hops;
		$sql = "UPDATE cccam_cline SET cline_cardlimit = '$value' WHERE cline_id = '$cline_id'";
		mysql_query($sql) or die (mysql_error());
			header("Refresh:3; URL=edit_cline.php?cline_id=$cline_id");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Card Limit. Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	} else {
		$value = $result_cardlimit['cline_cardlimit'] . ", " . $cardlimit  .$hops;
		$sql = "UPDATE cccam_cline SET cline_cardlimit = '$value' WHERE cline_id = '$cline_id'";
		mysql_query($sql) or die (mysql_error());
				header("Refresh:3; URL=edit_cline.php?cline_id=$cline_id");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Card Limit. Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	}
	
}

if(isset($_POST['add_cline']) && $_POST['add_cline'] == "ok") {
	include"../config.inc.php";
	$user_id = $_POST['user_id'];
	$cline_host = $_POST['cline_hostname'];
	$cline_port = $_POST['cline_port'];
	$cline_user = $_POST['cline_username'];
    $cline_pass = $_POST['cline_password'];
    $cline_wemu = $_POST['cline_wantemus'];
	$cline_resh = $_POST['cline_reshare'];
	$cline_clim = $_POST['cline_cardlimit'];
	$cline_climHop = $_POST['cline_cardlimitHops'];
	
	if($cline_wemu == "no") {
	
	// Retrive value cardlimit /////////////////
	
	$size = count($cline_clim);
	$i = 0;
	while($i < $size) {
		$caid = $cline_clim[$i];
		$limit = $cline_climHop[$i];
		if($limit == "" && $caid == "") {
		} else {
		$value = ", ".$caid.$limit;
		file_put_contents("card_limit.txt", $value, FILE_APPEND);
		}
		
		$i++;
	}
	// Remove ", " first 2 char
	$remove_cardlimit_char = file_get_contents("card_limit.txt");
	// Value To Insert
	$mod_cardlimit = substr($remove_cardlimit_char, 2);
	///////////////////////////////////////////
	///////////////////////////////////////////
	
	$sql_insert_cline = "INSERT INTO cccam_cline ( cline_id, cline_hostname, cline_port, cline_username, cline_password, cline_wantemus, cline_caid_id_hops, cline_cardlimit, user_id, cline_active ) VALUES ( NULL, '$cline_host', '$cline_port', '$cline_user', '$cline_pass', '$cline_wemu', '$cline_resh', '$mod_cardlimit', '$user_id', '1' )";
	mysql_query($sql_insert_cline);
	} else {
	$sql_insert_cline = "INSERT INTO cccam_cline ( cline_id, cline_hostname, cline_port, cline_username, cline_password, cline_wantemus, cline_caid_id_hops, cline_cardlimit, user_id, cline_active ) VALUES ( NULL, '$cline_host', '$cline_port', '$cline_user', '$cline_pass', '$cline_wemu', '', '', '$user_id', '1' )";
	mysql_query($sql_insert_cline);	
	}
	exec('rm card_limit.txt');
	header("Refresh:3; URL=list_cline.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "C Line. Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}

if(isset($_POST['edit_config']) && $_POST['edit_config'] == "ok") {
	include"../config.inc.php";
	$config_id = $_POST['config_id'];
	$config_value = $_POST['config_value'];
	
		$sql_update_config = "UPDATE cccam_config SET config_value = '$config_value' WHERE config_id = '$config_id'";
		mysql_query($sql_update_config);

		header("Refresh:3; URL=list_config.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Config. Updated!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	
}
if(isset($_POST['add_config']) && $_POST['add_config'] == "ok") {
	include"../config.inc.php";
	$config_value_name = $_POST['config_value_name'];
	$config_value = $_POST['config_value'];
 		
		$sql_insert_config = "INSERT INTO cccam_config ( config_id, config_value_name, config_value, config_active ) VALUES ( NULL, '$config_value_name', '$config_value', '0' ) ";
		mysql_query($sql_insert_config);

		header("Refresh:3; URL=list_config.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Config. Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";

}
if(isset($_POST['add_reader']) && $_POST['add_reader'] == "ok") {
	include"../config.inc.php";
	$reader_value_name = $_POST['reader_value_name'];
	$reader_value = $_POST['reader_value'];
 	$reader_misc = $_POST['reader_misc'];
	
		$sql_insert_reader = "INSERT INTO cccam_reader ( reader_id, reader_value_name, reader_value, reader_misc, reader_active ) VALUES ( NULL, '$reader_value_name', '$reader_value', '$reader_misc', '0' ) ";
		mysql_query($sql_insert_reader);

		header("Refresh:3; URL=list_reader.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Reader. Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";

}
if(isset($_POST['edit_nline']) && $_POST['edit_nline'] == "ok") {
	include"../config.inc.php";
	$nline_id = $_POST['nline_id'];
	
	$nline_hostname = $_POST['nline_hostname'];
	$nline_port = $_POST['nline_port'];
	$nline_username = $_POST['nline_username'];
	$nline_password = $_POST['nline_password'];
	$nline_des = $_POST['nline_des'];
	$nline_hops = $_POST['nline_hops'];
	$nline_stealth = $_POST['nline_stealth'];
	
		$sql_update_nline = "UPDATE cccam_nline SET nline_hostname = '$nline_hostname', nline_port = '$nline_port', nline_username = '$nline_username', nline_password = '$nline_password', nline_des = '$nline_des', nline_hops = '$nline_hops', nline_stealth = '$nline_stealth' WHERE nline_id = '$nline_id'";
		mysql_query($sql_update_nline);

		header("Refresh:3; URL=list_nline.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "N-Line. Updated!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	
}
if(isset($_POST['add_nline']) && $_POST['add_nline'] == "ok") {
	include"../config.inc.php";

	$nline_hostname = $_POST['nline_hostname'];
	$nline_port = $_POST['nline_port'];
	$nline_username = $_POST['nline_username'];
	$nline_password = $_POST['nline_password'];
	$nline_des = $_POST['nline_des'];
	$nline_hops = $_POST['nline_hops'];
	$nline_stealth = $_POST['nline_stealth'];
	
		$sql_insert_nline = "INSERT INTO cccam_nline ( nline_id, nline_hostname, nline_port, nline_username, nline_password, nline_des, nline_hops, nline_stealth, nline_active ) VALUES ( NULL, '$nline_hostname', '$nline_port', '$nline_username', '$nline_password', '$nline_des', '$nline_hops', '$nline_stealth', '1' )";
		mysql_query($sql_insert_nline);

		header("Refresh:3; URL=list_nline.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "N-Line. Updated!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	
}
if(isset($_POST['edit_lline']) && $_POST['edit_lline'] == "ok") {
	include"../config.inc.php";
	$lline_id = $_POST['lline_id'];
	
	$lline_hostname = $_POST['lline_hostname'];
	$lline_port = $_POST['lline_port'];
	$lline_username = $_POST['lline_username'];
	$lline_password = $_POST['lline_password'];
	$lline_caid = $_POST['lline_caid'];
	$lline_ident = $_POST['lline_ident'];
	$lline_hops = $_POST['lline_hops'];
	
		$sql_update_lline = "UPDATE cccam_lline SET lline_hostname = '$lline_hostname', lline_port = '$lline_port', lline_username = '$lline_username', lline_password = '$lline_password', lline_caid = '$lline_caid', lline_ident = '$lline_ident', lline_hops = '$lline_hops' WHERE lline_id = '$lline_id'";
		mysql_query($sql_update_lline);

		header("Refresh:3; URL=list_lline.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "L-Line. Updated!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	
}
if(isset($_POST['add_lline']) && $_POST['add_lline'] == "ok") {
	include"../config.inc.php";
	$lline_id = $_POST['lline_id'];
	
	$lline_hostname = $_POST['lline_hostname'];
	$lline_port = $_POST['lline_port'];
	$lline_username = $_POST['lline_username'];
	$lline_password = $_POST['lline_password'];
	$lline_caid = $_POST['lline_caid'];
	$lline_ident = $_POST['lline_ident'];
	$lline_hops = $_POST['lline_hops'];
	
		$sql_insert_lline = "INSERT INTO cccam_lline ( lline_id, lline_hostname, lline_port, lline_username, lline_password, lline_caid, lline_ident, lline_hops, lline_active ) VALUES ( NULL, '$lline_hostname', '$lline_port', '$lline_username', '$lline_password', '$lline_caid', '$lline_ident', '$lline_hops', '1' )";
		mysql_query($sql_insert_lline);

		header("Refresh:3; URL=list_lline.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "L-Line. Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	
}

if(isset($_POST['edit_rline']) && $_POST['edit_rline'] == "ok") {
	include"../config.inc.php";
	$rline_id = $_POST['rline_id'];
	
	$rline_hostname = $_POST['rline_hostname'];
	$rline_port = $_POST['rline_port'];
	$rline_caid = $_POST['rline_caid'];
	$rline_ident = $_POST['rline_ident'];
	$rline_hops = $_POST['rline_hops'];
	
		$sql_update_rline = "UPDATE cccam_rline SET rline_hostname = '$rline_hostname', rline_port = '$rline_port', rline_caid = '$rline_caid', rline_ident = '$rline_ident', rline_hops = '$rline_hops' WHERE rline_id = '$rline_id'";
		mysql_query($sql_update_rline);

		header("Refresh:3; URL=list_rline.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "R-Line. Updated!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	
}

if(isset($_POST['add_rline']) && $_POST['add_rline'] == "ok") {
	include"../config.inc.php";
	
	$rline_hostname = $_POST['rline_hostname'];
	$rline_port = $_POST['rline_port'];
	$rline_caid = $_POST['rline_caid'];
	$rline_ident = $_POST['rline_ident'];
	$rline_hops = $_POST['rline_hops'];
	
		$sql_insert_rline = "INSERT INTO cccam_rline ( rline_id, rline_hostname, rline_port, rline_caid, rline_ident, rline_hops, rline_active ) VALUES ( NULL, '$rline_hostname', '$rline_port', '$rline_caid', '$rline_ident', '$rline_hops', '1' )";
		mysql_query($sql_insert_rline);

		header("Refresh:3; URL=list_rline.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "R-Line. Added!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	
}

if(isset($_POST['up_server']) && $_POST['up_server'] == "ok") {
include"../config.inc.php";

	$fline_id = $_POST['fline_id'];
	
	$delete = "DELETE FROM cccam_servers WHERE fline_id = '$fline_id'";
	mysql_query($delete);
	$size = count($_POST['server_id']);
	$i = 0;
	while($i < $size) {
		$server_id = $_POST['server_id'][$i];
		$select_sql = "SELECT * FROM cccam_server_list WHERE server_id = '$server_id'";
		$select_query = mysql_query($select_sql) or die (mysql_error());
		$select_result = mysql_fetch_assoc($select_query);
		$rows = mysql_num_rows($select_query);
		
		
		$host = $select_result['server_host'];
		$port = $select_result['server_port'];
		$wemu = $select_result['server_wantemu'];
		$upho = $select_result['server_uphops'];
		
		
		if($rows <= 0) {
		} else {
		$sql_server = "INSERT INTO cccam_servers ( server_id, fline_id, server_list_id, server_host, server_port, server_wantemu, server_uphops ) VALUES ( NULL, '$fline_id', '$server_id', '$host', '$port', '$wemu', '$upho')";
		mysql_query($sql_server);
		}
			
	$i++;
	}
	$uid = $_POST['user_id'];
	header("Refresh:3; URL=list_user_detail.php?user_id=$uid&fline_id=$fline_id");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Server Update Success!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
		
}
if(isset($_POST['update']) && $_POST['update'] == "server") {
	include"../config.inc.php";
	$id = $_POST['server_id'];
	$host = $_POST['server_host'];
	$port = $_POST['server_port'];
	$wemu = $_POST['server_wantemu'];
	$upho = $_POST['server_uphops'];
	
	
		$sql_update_server = "UPDATE cccam_server_list SET server_host = '$host', server_port = '$port', server_wantemu = '$wemu', server_uphops = '$upho' WHERE server_id = '$id'";
		mysql_query($sql_update_server);
		
		$sql_update_server_client = "UPDATE cccam_servers SET server_host = '$host', server_port = '$port', server_wantemu = '$wemu', server_uphops = '$upho' WHERE server_list_id = '$id'";
		mysql_query($sql_update_server_client);
		
		
		
		header("Refresh:3; URL=server_manager.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Server Update Success!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
	
}
if(isset($_POST['add_server']) && $_POST['add_server'] == "ok")
{
	include"../config.inc.php";
	$host = $_POST['server_host'];
	$port = $_POST['server_port'];
	$wemu = $_POST['server_wantemu'];
	$upho = $_POST['server_uphops'];
	if($_POST['all'] != "0") 
	{
			$sql_insert_server = "INSERT INTO cccam_server_list ( server_id, server_host, server_port, server_wantemu, server_uphops ) VALUES ( NULL, '$host', '$port', '$wemu', '$upho' )";
			mysql_query($sql_insert_server);
	
	} else {
			$sel_fline_id = "SELECT * FROM cccam_fline";
			$query_fline_id = mysql_query($sel_fline_id);
			while($result_fline_id = mysql_fetch_assoc($query_fline_id)) {
					$flineID[] = $result_fline_id['fline_id'];
			}
			$sql_insert_server = "INSERT INTO cccam_server_list ( server_id, server_host, server_port, server_wantemu, server_uphops ) VALUES ( NULL, '$host', '$port', '$wemu', '$upho' )";
			mysql_query($sql_insert_server);
			$server_ID_ins = mysql_insert_id();
			
			foreach($flineID as $fid) {
				$sql_insert_all = "INSERT INTO cccam_servers ( server_id, fline_id, server_list_id, server_host, server_port, server_wantemu, server_uphops ) VALUES ( NULL, '$fid', '$server_ID_ins', '$host', '$port', '$wemu' ,'$upho' )";
				mysql_query($sql_insert_all);
			}
	}
			header("Refresh:3; URL=server_manager.php");
			
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Server Update Success!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}
if(isset($_POST['paypal']) && $_POST['paypal'] == "update")
{
	include"../config.inc.php";
	$email = $_POST['paypal_email'];
	$abo1 = $_POST['paypal_cur_1'];
	$abo3 = $_POST['paypal_cur_3'];
	$abo6 = $_POST['paypal_cur_6'];
	$abo12 = $_POST['paypal_cur_12'];
	$currency = $_POST['paypal_cur_code'];
	
	$pay_sql_update = "UPDATE cccam_paypal SET paypal_email = '$email', paypal_cur_1 = '$abo1', paypal_cur_3 = '$abo3', paypal_cur_6 = '$abo6', paypal_cur_12 = '$abo12', paypal_cur_code = '$currency'";
	mysql_query($pay_sql_update);
	
				header("Refresh:3; URL=paypal_config.php");
	echo "<html>";
	echo "<head>";
	echo "<style type='text/css'>";
	echo "body {";
	echo "background-color: #5B7CFF";
	echo "}";
	echo "</style>";
	echo "<link href='../css/style.css' rel='stylesheet' type='text/css'>";
	echo "<body>";
	echo "<br><br><br><br><br><br><br><br>";
	echo "<table width='600' align='center' border='0' cellpadding='0' cellspacing='0'class='Contorno'  >";
	echo "<tr>";
	echo "<td bgcolor='#003366'>";
	echo "<br>";
	echo "<div align='center' class='TitoloMenu'>";
	echo "Paypal Setting Update Success!</br>";
	echo "</div>";
	echo "<br>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</body>";
	echo "</head>";
	echo "</html>";
}
	?>