<?php
include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(NEXVA_DB) or die("Couldn't select database");

$insert_count = 0;
// get the non demo product files by priduct id
$sql_file = 'SELECT
build_files.filename,
product_builds.name,
products.name as product_name,
products.id as product_id,
product_builds.id as build_id
FROM
products
Inner Join product_builds ON products.id = product_builds.product_id
Inner Join build_files ON product_builds.id = build_files.build_id
WHERE
products.content_type <>  "URL"
ORDER BY
products.price DESC';
$rs_file = mysql_query($sql_file) or die("Error in query: " .mysql_error() . $sql_file);

include_once 's3-php5-curl/S3.php';
$s3 = new S3('AKIAJTGAZOK4BIO5PY7Q', 'O17Xft/uJp5mf6UuK3GI/SzYGJ4VIkEvPalUKjm8');
$missing = '';
//write result to a file
$log = "s3_missing_files.txt";
$log_append = fopen($log, 'a') or die("can't open file");
while($row_file = mysql_fetch_array($rs_file)) {
    $file_not_found_on_s3 = check_file_exists_on_s3($row_file);
    fwrite($log_append, $file_not_found_on_s3);
    flush();
}

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
$header .= "S3 File Test Results\r\n\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-Type: application/octet-stream; name=\"".$log."\"\r\n"; // use different content types here
$header .= "Content-Transfer-Encoding: base64\r\n";
$header .= "Content-Disposition: attachment; filename=\"".$log."\"\r\n\r\n";
$header .= $content."\r\n\r\n";
$header .= "--".$uid."--";

@mail('heshanmw@gmail.com', 'S3 Test Results', "", $header);
@mail('jahufar@nexva.com', 'S3 Test Results', "", $header);
fclose($log_append);

// importing to product_build, build_files, build_devices tables
function check_file_exists_on_s3($row) {
    global $s3;
    $bucket = 'staging.applications.nexva.com';
    $file_name = $row['filename'];
    $product_id = $row['product_id'];
    $uri = 'productfile/' . $product_id . '/' . $file_name;
//    echo '<br/>';
    $product_name = $row['product_name'];
    $build_id = $row['build_id'];
    if(!$s3->getObjectInfo($bucket, $uri)) {
//        echo 'Product File is not exists for ' . $product_name . 'and priduct id ' . $product_id . ' and build Id ' . $build_id;
//        echo '<br/>';
//        sleep(1);
        return "$product_id|$product_name|$build_id|$uri\r\n";
    }
}

?>