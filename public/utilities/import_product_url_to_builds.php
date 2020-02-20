<?php

include_once("dbconfig.php");
error_reporting(E_ALL);
set_time_limit(0);

mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(NEXVA_DB) or die("Couldn't select database");
$insert_count = 0;
$sql_product = 'SELECT
products.id as product_id,
products.name as product_name,
products.content_type as content_type
FROM
products
WHERE products.content_type = "URL"';

$rs_product = mysql_query($sql_product) or die("Error in query: " . mysql_error() . $sql_product);

while ($row_product = mysql_fetch_array($rs_product)) {
  import_to_build($row_product);
  flush();
}

function import_to_build($row_product) {
  $product_id = $row_product['product_id'];
  echo "Updating Product id " . $product_id . "\n";
  $sql = 'UPDATE product_builds SET build_type = "urls" WHERE product_id = ' . $product_id;
  mysql_query($sql) or die("Error in query: " . mysql_error() . $sql);
}

?>
