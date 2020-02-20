<?php
error_reporting ( E_ALL );
set_time_limit ( 0 );
include_once ("../application/BootstrapCli.php");


$noOfRecordsToBeUpdated = 10000; // place no of files to be updated... in a batch
$counter = 0;
$fileSize = '';

$proudctBuild = new Model_ProductBuild ( );

$build = $proudctBuild->getBuildsFiles ();

foreach ( $build as $buildInfo ) {
	
	if ($counter > $noOfRecordsToBeUpdated)
		return;
		
    //echo $buildInfo->product_id, $buildInfo->filename, '  - ', $buildInfo->build_id, "\n";
		
    $productModel = new Model_Product ( );
	
	$fileSize = $productModel->getS3Fileinfo ( $buildInfo->product_id . '/' . $buildInfo->filename );
	
	if ($fileSize) {
		$buildFilesModel = new Model_BuildFiles ( );
		
		$buildFilesModel->updateFileSize ( $fileSize, $buildInfo->build_id );
	}
	
	$counter ++;
	echo $counter.'\n';

}
echo '\n'.$counter.'\n';