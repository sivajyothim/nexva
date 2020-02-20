<?php
include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(NEXVA_DB) or die("Couldn't select database");

$insert_count = 0;
// get the non demo product files by priduct id
//$sql_product = 'SELECT
//products.id as product_id
//FROM
//products
//ORDER BY
//products.id';
$sql_product = 'SELECT
users.username as username,
users.id as user_id,
products.id as product_id,
products.name as product_name,
platforms.name as platform
FROM
products
Inner Join users ON products.user_id = users.id
Inner Join platforms ON platforms.id = products.platform_id';
$rs_product = mysql_query($sql_product) or die("Error in query: " .mysql_error() . $sql_product);

//write result to a file
//$log = "missing_files_on_db.txt";
$log = "zip_files_on_db.txt";
$log_append = fopen($log, 'a') or die("can't open file");
while($row_product = mysql_fetch_array($rs_product)) {
    $file_not_found_on_db = is_file_exists_on_build_files($row_product);
    fwrite($log_append, $file_not_found_on_db);
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
$header .= "DB File Test Results\r\n\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-Type: application/octet-stream; name=\"".$log."\"\r\n"; // use different content types here
$header .= "Content-Transfer-Encoding: base64\r\n";
$header .= "Content-Disposition: attachment; filename=\"".$log."\"\r\n\r\n";
$header .= $content."\r\n\r\n";
$header .= "--".$uid."--";

@mail('heshanmw@gmail.com', 'DB File Test Results', "", $header);
//@mail('jahufar@nexva.com', 'S3 Test Results', "", $header);
//@mail('cheran@nexva.com', 'DB File Test Results', "", $header);
//fclose($log_append);

// checking in product_build, build_files, build_devices tables
function is_file_exists_on_build_files($row) {
    $product_id = $row['product_id'];
    $product_name = $row['product_name'];
    $user_id = $row['user_id'];
    $user_name = $row['username'];
    $platform = $row['platform'];
    
    $sql_build = "SELECT
        product_builds.id as build_id
        FROM
        product_builds
        WHERE
        product_builds.product_id = '$product_id'";
    $rs_build = mysql_query($sql_build) or die("Error in query: " .mysql_error() . $sql_build);
    while($row_build = mysql_fetch_array($rs_build)) {
        $build_id = $row_build['build_id'];
        $sql_file = "SELECT
                build_files.filename as filename
                FROM
                build_files
                WHERE
                build_files.build_id = '$build_id'";
        $rs_file = mysql_query($sql_file) or die("Error in query: " .mysql_error() . $sql_file);
        while($row_file = mysql_fetch_array($rs_file)) {
            // check file extention is zip
            $filename = $row_file['filename'];
            $file_name = end(explode(".", $filename));
            if($file_name == "zip" || $file_name == "ZIP" )
                return "$product_id|$user_id|$user_name|$product_name|$platform|$filename|Zip File Found\r\n";
        }
//        if($row_file['count'] == 0) {
//            $update_prod = "UPDATE products
//                        SET status='UNDER_REVIEW'
//                        WHERE id=$product_id";
//            $update = mysql_query($update_prod);
////            echo 'No files for product id = ' . $product_id . ' and build id = ' .$product_id;
////            echo "\r\n";
//            return "$product_id|$build_id|$update\r\n";
//        }
    }
}

?>