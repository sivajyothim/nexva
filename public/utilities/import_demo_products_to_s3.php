<?php
include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

mysql_pconnect(HOST, USERNAME_V1, PASSWORD_V1);
mysql_select_db(NEXVA_V1);

$insert_count = 0;
// get the non demo product files by priduct id
$sql_v1_files = "select iPFId,tbl_product_files.iPId,tbl_product_files.vJarfile,tbl_product_files.vJadfile,vSISFile,vOTAFile,vCodeFile1,vCodeFile2,vCodeFile3,vCodeFile4,vCodeFile5,vCodeFile6,vCodeFile7,vCodeFile8,vCodeFile9,vCodeFile10,vWallpaper,vScrennServer,vVideo,vRingtone,vApkFile,vAudioBook,vUrl,vNAme from nexvadb.tbl_product_files join nexvadb.tbl_product_details
            on   tbl_product_files.iPId = tbl_product_details.iPId
            where cMode = 'D' order by iPId";
$rs_v1_files = mysql_query($sql_v1_files) or die("Error in query: " .mysql_error() . $sql_v1_files);
mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(NEXVA_DB) or die("Couldn't select database");
$log = "import_demo_files_to_s3_results.txt";
$log_append = fopen($log, 'a') or die("can't open file");
include_once 's3-php5-curl/S3.php';
$s3 = new S3('AKIAJTGAZOK4BIO5PY7Q', 'O17Xft/uJp5mf6UuK3GI/SzYGJ4VIkEvPalUKjm8');

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
$header .= "Import Demo Results\r\n\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-Type: application/octet-stream; name=\"".$log."\"\r\n"; // use different content types here
$header .= "Content-Transfer-Encoding: base64\r\n";
$header .= "Content-Disposition: attachment; filename=\"".$log."\"\r\n\r\n";
$header .= $content."\r\n\r\n";
$header .= "--".$uid."--";
//
@mail('heshanmw@gmail.com', 'Import Files', "", $header);
//@mail('jahufar@nexva.com', 'S3 Test Results', "", $header);
//@mail('cheran@nexva.com', 'S3 Test Results', "", $header);
//fclose($log_append);


// importing to product_build, build_files, build_devices tables
function import_to_v2($row) {
    global $s3;
    // variables
    $product_id = $row['iPId'];
    $prod_name = $row['vNAme'];
    // get new product id
//    mysql_pconnect(HOST, USERNAME, PASSWORD);
    $query_new_demo_property =   "select id,name from products where product_type ='DEMO' and name like '".$row['vNAme']."'";
    $rs_new_demo_property    =   mysql_query($query_new_demo_property);
    if(mysql_errno() == 0)
        $demo_id = mysql_fetch_assoc($rs_new_demo_property);
    else
        return "$product_id|$prod_name|is not found\r\n";
//    if(empty)
    $new_prod_id = $demo_id['id'];
//    echo "\n";
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
        return "$product_id|$new_prod_id|files missing\r\n";
//            return "$product_id|$build_id|$build_name|file|No files found for this product build\r\n";

    $files_err = '';
    foreach($files as $key=>$filename) {
        echo $uri_old = "productfile/$product_id/demo/$filename";
        echo "\n";
        echo $uri_new = "productfile/$new_prod_id/$filename";
        echo "\n";
        $bucket = 'production.applications.nexva.com';
//        $newBucket = 'demo.nexva.com';
        if(!$s3->copyObject($bucket, $uri_old, $bucket, $uri_new))
            return "$product_id|$new_prod_id|$bucket/$uri_old|$bucket/$uri_new\r\n";
    }
//    flush();
}


?>