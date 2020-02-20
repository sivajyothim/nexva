<?php
include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

$con_wurfl   =   mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(NEXVA_DB);

$contents= file_get_contents('s3_missing_files.txt');
if( !$contents ) die("s3_missing_files.txt is missing.");

$contents = nl2br($contents);


$parts = explode('<br />', $contents);

//echo "<pre>"; print_r($parts); echo "</pre>";
//die();

$count = 0;
foreach( $parts as $part)
{
    $t = explode("|", $part);
    if( trim($t[0]) != "")
    {
        echo "Disabling product: ". $t[1]. "(". $t[0].") <br>";

        $sql = "UPDATE products SET status = 'NOT_APPROVED' WHERE id = ". $t[0];
        $result = mysql_query($sql);
        if( !$result) die("Error in query: ". $sql.mysql_error());

        $count++;
    }

}

echo "Done. $count items disabled.";

 









?>