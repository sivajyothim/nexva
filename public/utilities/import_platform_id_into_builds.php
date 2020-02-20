<?php

error_reporting(E_ALL);
set_time_limit(0);

include_once("../../application/BootstrapCli.php");

// models we need
$modelProduct = new Model_Product();
$modelBuildProduct = new Model_ProductBuild();

$products = $modelProduct->fetchAll();

foreach ($products as $product) {
  import_to_builds($product);
}

function import_to_builds($product) {
  global $modelBuildProduct;
  $builds = $modelBuildProduct->getBuildsByProductId($product->id);
  foreach ($builds as $build) {
//    echo $build->id;
    $data = array('platform_id' => $product->platform_id);
    if (!$modelBuildProduct->update($data, 'id = ' . $build->id))
      echo "Faild updating $build->id";
  }
}

?>
