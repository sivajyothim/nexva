<?php
include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

mysql_pconnect(HOST, USERNAME_V1, PASSWORD_V1);
mysql_select_db(NEXVA_V1);

$insert_count = 0;
// get the non demo product files by priduct id
$sql_v1_files = 'SELECT * FROM tbl_product_files WHERE cMode <> "D" ORDER BY iPId';
$rs_v1_files = mysql_query($sql_v1_files) or die("Error in query: " .mysql_error() . $sql_v1_files);
mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(NEXVA_DB) or die("Couldn't select database");
$log = "import_build_results.txt";
$log_append = fopen($log, 'a') or die("can't open file");
while($row_v1_files = mysql_fetch_array($rs_v1_files)) {
    $error = import_to_v2($row_v1_files);
    fwrite($log_append, $error);
    flush();
}

// send an email
// sending an attachment mail once complete
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
$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
$header .= "This is a multi-part message in MIME format.\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$header .= "Build Results\r\n\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-Type: application/octet-stream; name=\"".$log."\"\r\n"; // use different content types here
$header .= "Content-Transfer-Encoding: base64\r\n";
$header .= "Content-Disposition: attachment; filename=\"".$log."\"\r\n\r\n";
$header .= $content."\r\n\r\n";
$header .= "--".$uid."--";

@mail('heshanmw@gmail.com', 'Import Builds', "", $header);
//@mail('jahufar@nexva.com', 'S3 Test Results', "", $header);
fclose($log_append);


// importing to product_build, build_files, build_devices tables
function import_to_v2($row) {
    // variables
    $product_id = $row['iPId'];
//    echo "\n";
    $device_str = $row['tDeviceId'];
    $build_name = 'Default Build 1';
    // selete the inserted entries
    $sql_select = 'SELECT id FROM product_builds WHERE product_id = ' . $product_id;
    if( ($numb_of_rows = mysql_num_rows(mysql_query($sql_select) )) > 0 )
        $build_name = 'Default Build ' . ($numb_of_rows + 1);
    // insert to product_builds
    $sql =  "INSERT IGNORE INTO product_builds(product_id, name, device_selection_type, status) VALUES ($product_id, '$build_name', 'CUSTOM', 1)";
    @mysql_query($sql);
    if(mysql_errno() != 0) {
        $mysql_err = mysql_error();
        $error = "$product_id|$build_name|Insert Product Failed\r\n";
        $error .= "$mysql_err\r\n\r\n";
        return $error;
    }
    $build_id = mysql_insert_id();
    //inserting to build_devices
    $devices = explode(',', $device_str);
    $device_err = '';
    foreach($devices as $key=>$value) {
        if(empty ($value))
            continue;
        $sql_device =  "INSERT IGNORE INTO build_devices(device_id, build_id) VALUES ($value, $build_id)";
        @mysql_query($sql_device);
        if(mysql_errno() != 0) {
            $mysql_err = mysql_error();
            $device_err .= "$product_id|$build_id|$build_name|device|$value|$mysql_err\r\n";
            continue;
        }
    }

    //inserting to build_files
    $files = array();
    $columns = array('vJarfile', 'vJadFile', 'vSISFile', 'vOTAFile', 'vCodeFile1', 'vCodeFile2',
            'vCodeFile3', 'vCodeFile4', 'vCodeFile5', 'vCodeFile6', 'vCodeFile7', 'vCodeFile8', 'vCodeFile9',
            'vCodeFile10', 'vWallpaper', 'vScrennServer', 'vVideo', 'vRingtone', 'vApkFile', 'vAudioBook', 'vUrl');

    foreach ($columns as $column) {
        if(isset ($row[$column]) &&  !empty ($row[$column]))
            $files[] = $row[$column];
    }
// if no files for the product
    if(count($files) == 0)
        return "$product_id|$build_id|$build_name|file|No files found for this product build\r\n";

    $files_err = '';
    foreach($files as $key=>$filename) {
//        $filename = str_replace("'", '', $filename);
        $sql_file =  'INSERT IGNORE INTO build_files(filename, build_id) VALUES ("' . $filename . '", ' . $build_id . ')';
        @mysql_query($sql_file);
        if(mysql_errno() != 0) {
            $mysql_err = mysql_error();
            $files_err .= "$product_id|$build_id|$build_name|file|$filename|$mysql_err\r\n";
            continue;
        }
    }
    return $device_err . $files_err;
//    flush();
}

?>