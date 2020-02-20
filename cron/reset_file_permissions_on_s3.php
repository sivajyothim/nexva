<?php
include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(NEXVA_DB) or die("Couldn't select database");

$insert_count = 0;
// get the non demo product files by priduct id
$expires_files = strtotime("-90 seconds");
$date = date("Y-m-d H:i:s", $expires_files);
$sql_file = "SELECT
s3_public_files.id as fid,
s3_public_files.filename as filename,
s3_public_files.`time` as time
FROM
s3_public_files
WHERE
s3_public_files.`time` <  '$date'";
$rs_file = mysql_query($sql_file) or die("Error in query: " .mysql_error() . $sql_file);

include_once 's3-php5-curl/S3.php';
$s3 = new S3('AKIAJTGAZOK4BIO5PY7Q', 'O17Xft/uJp5mf6UuK3GI/SzYGJ4VIkEvPalUKjm8');
$missing = '';
//write result to a file
$log = "revert_file_permissons_on_s3.txt";
$log_append = fopen($log, 'a') or die("can't open file");
while($row_file = mysql_fetch_array($rs_file)) {
    $file_not_found_on_s3 = revert_object_permissions_on_s3($row_file);
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
$header .= 'Cc: jahufar@nexva.com' . "\r\n";
$header .= 'Bcc: heshanmw@gmail.com' . "\r\n";
$header .= "Reply-To: heshan@nexva.com\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
$header .= "This is a multi-part message in MIME format.\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$header .= "S3 Objects which Reverted to Private\r\n\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-Type: application/octet-stream; name=\"".$log."\"\r\n"; // use different content types here
$header .= "Content-Transfer-Encoding: base64\r\n";
$header .= "Content-Disposition: attachment; filename=\"".$log."\"\r\n\r\n";
$header .= $content."\r\n\r\n";
$header .= "--".$uid."--";

@mail('jahufar@nexva.com', 'S3 Objects Reverted to Privates', "", $header);
fclose($log_append);

// importing to product_build, build_files, build_devices tables
function revert_object_permissions_on_s3($row) {
    global $s3;
    global $date;
    $fid = $row['fid'];
    $uri = $row['filename'];
    $now = date("Y-m-d H:i:s");
    $bucket = 'production.applications.nexva.com';
    // default ACL is private
    $metaHeaders = array('x-amz-metadata-directive' => 'REPLACE');
    if(!$s3->copyObject($bucket, $uri, $bucket, $uri, 'private', $metaHeaders))
        return "$now|$fid|$uri|Reverting error occured $date\r\n";
    $delete = 'DELETE FROM s3_public_files WHERE id = ' . $fid;
    mysql_query($delete);
    return "$now|$fid|$uri|Reverting older than $date\r\n";
}

?>