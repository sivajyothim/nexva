<?php

include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

$con_wurfl = mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(WURFL_DB);


//include_once("../../nexva_mobi/include/Tera-Wurfl/TeraWurfl.php");
//$wurfl = new TeraWurfl();
//echo 'oki';
//	$tera_wurfl_link = mysql_connect(TERA_WURFL_DB_HOST, TERA_WURFL_DB_USER, TERA_WURFL_DB_PASS) or die("Cannot connect to tera wufl host");
//	mysql_select_db(TERA_WURFL_DB, $tera_wurfl_link) or die("Cannot select tera wurfl DB");;




include_once( '../vendors/Tera-WURFL/TeraWurfl.php' );

$wurfl = new TeraWurfl();

$insert_count = 0;

$sql_tables = 'show tables';
$rs_tables = mysql_query($sql_tables) or die("Error in query: " . $sql_tables);
$log = "device_os.txt";
$log_append = fopen($log, 'a') or die("can't open file");

while ($row_tables = mysql_fetch_array($rs_tables)) {

  $table_name = $row_tables[0];

  if (strrpos($table_name, "_")) {
    import_to_nexva($table_name);
    //echo $table_name ."<br>";
  }
}

$file = $log;
$file_size = filesize($file);
$handle = fopen($file, "r");
$content = fread($handle, $file_size);
$content = chunk_split(base64_encode($content));
$uid = md5(uniqid(time()));
$name = basename($file);
$header = '';
$header = "From: neXva Mailer Agent <do-not-reply@nexva.com>\r\n";
$header .= "Reply-To: heshan@nexva.com\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"\r\n\r\n";
$header .= "This is a multi-part message in MIME format.\r\n";
$header .= "--" . $uid . "\r\n";
$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$header .= "Device Platformsr\n\r\n";
$header .= "--" . $uid . "\r\n";
$header .= "Content-Type: application/octet-stream; name=\"" . $log . "\"\r\n"; // use different content types here
$header .= "Content-Transfer-Encoding: base64\r\n";
$header .= "Content-Disposition: attachment; filename=\"" . $log . "\"\r\n\r\n";
$header .= $content . "\r\n\r\n";
$header .= "--" . $uid . "--";

@mail('heshanmw@gmail.com', 'Device Platforms', "", $header);
fclose($log_append);

//echo "<br><b>Inserted: $insert_count devices.</b>";

function import_to_nexva($tablename) {
  global $wurfl, $insert_count, $log_append;
  $sql = "SELECT * FROM $tablename";
//  echo "<br>------ $tablename ---------<br>";
//  echo "\n";
  mysql_select_db(WURFL_DB) or die("Couldn't select database");
  $rs_devices = mysql_query($sql) or die("Error in query: " . $sql);
  $devices = array();

  while ($row_devices = mysql_fetch_array($rs_devices)) {
    if ($row_devices['user_agent'] != "") {
      $match_found = $wurfl->GetDeviceCapabilitiesFromAgent($row_devices['user_agent']);
      if ($match_found && 1 == $wurfl->capabilities['product_info']['is_wireless_device']) {
        $device_id = $row_devices['deviceID'];
        if ($device_id != "" && $wurfl->capabilities['product_info']['brand_name'] != "" && $wurfl->capabilities['product_info']['model_name'] != "") {
          $devices[$device_id]['device_os'] = mysql_real_escape_string($wurfl->capabilities['product_info']['device_os']);
          //print_r($devices);
        }
      }
    }
  }

  mysql_select_db(NEXVA_DB) or die("Couldn't select database");
  foreach ($devices as $device_id => $device) {
    echo $platform_os = $device['device_os'] . "\n";
    fwrite($log_append, $platform_os);
  }
}

?>