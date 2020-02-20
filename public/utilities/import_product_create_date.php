<?php
include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

mysql_pconnect(HOST, USERNAME_V1, PASSWORD_V1);
mysql_select_db(NEXVA_V1);

$insert_count = 0;
// get the non demo product files by priduct id
$sql_v1_files = 'SELECT * FROM tbl_product_details';
$rs_v1_files = mysql_query($sql_v1_files) or die("Error in query: " .mysql_error() . $sql_v1_files);
mysql_pconnect(HOST, USERNAME, PASSWORD);
echo NEXVA_DB;
mysql_select_db(NEXVA_DB) or die("Couldn't select database");
//$log = "import_build_results.txt";
//$log_append = fopen($log, 'a') or die("can't open file");
while($row_v1_files = mysql_fetch_array($rs_v1_files)) {
    echo $error = import_to_v2($row_v1_files);
//    fwrite($log_append, $error);
    flush();
}

// send an email
// sending an attachment mail once complete
//$file = $log;
//$file_size = filesize($file);
//$handle = fopen($file, "r");
//$content = fread($handle, $file_size);
//$content = chunk_split(base64_encode($content));
//$uid = md5(uniqid(time()));
//$name = basename($file);
//$header = '';
//$header = "From: neXva Mailer Agent <do-not-reply@nexva.com>\r\n";
//$header .= "Reply-To: heshan@nexva.com\r\n";
//$header .= "MIME-Version: 1.0\r\n";
//$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
//$header .= "This is a multi-part message in MIME format.\r\n";
//$header .= "--".$uid."\r\n";
//$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
//$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
//$header .= "Build Results\r\n\r\n";
//$header .= "--".$uid."\r\n";
//$header .= "Content-Type: application/octet-stream; name=\"".$log."\"\r\n"; // use different content types here
//$header .= "Content-Transfer-Encoding: base64\r\n";
//$header .= "Content-Disposition: attachment; filename=\"".$log."\"\r\n\r\n";
//$header .= $content."\r\n\r\n";
//$header .= "--".$uid."--";
//
//@mail('heshanmw@gmail.com', 'Import Builds', "", $header);
////@mail('jahufar@nexva.com', 'S3 Test Results', "", $header);
//fclose($log_append);


// importing to product_build, build_files, build_devices tables
function import_to_v2($row) {
    // variables
    $createDate = $row['dFileDate'];
    $productId = $row['iPId'];
    echo $sql =  "UPDATE products SET created_date = '$createDate' WHERE id = $productId";
    echo "\n";
//    @mysql_query($sql);
//    if(mysql_errno() != 0) {
//        $mysql_err = mysql_error();
//        $error = "$product_id|$build_name|Insert Product Failed\r\n";
//        $error .= "$mysql_err\r\n\r\n";
//        return $error;
//    }
    return true;
//    flush();
}

?>