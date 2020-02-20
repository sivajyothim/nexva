<?php
/**
 * This script will go through the analytics collections and 
 * add country details whenever the IP is present
 */
set_time_limit(0);
MongoCursor::$timeout = -1;

define('PROCESS', 'ANALYTICS_IP2COUNTRY');

include_once('../../application/BootstrapCli.php');
include_once('geo/Ip2Country.php');
include_once('common.php');

lock(PROCESS);

$i  = new Ip2Country();
$i->dir = APPLICATION_PATH . '/../cli/analytics/geo/php_db/';

e('Starting on Views');
//Map reduce the content to a temp database
$productView            = new Nexva_Analytics_ProductView();
$productViewTempList    = '_productViewUniqueIpList';
$productView->generateUniqueIpList($productViewTempList);

$tempDb     = $productView->_getConnectionForTempCollections();
$tempCollection = $productView->_getCollection($productViewTempList, true);
$collection     = $productView->_getCollection();


//loop through it and update with the deets
$results    = $tempCollection->find();
$count      = 0;
foreach ($results as $row) {
    $ip = $row['_id']['ip'];
    $i->load($ip);
    
    if (substr($i->country, 0, 1) != '?') {
        $country    = array(
            'name'  => $i->country,
            'code'  => $i->countryCode 
        );
        $collection->update(array('ip' => $ip), array('$set' => array('country' => $country)), array("multiple" => true));    
    } 
    
    $count++;
    if ($count % 10000 == 0) {
        e('Updated ' . $count);    
    }
}
//drop the temp data
    try {
        $tempDb->selectCollection($productViewTempList)->drop();   
    } catch(Exception $ex) {
        e("ERROR : Couldn't delete {$productViewTempList}. Manual delete needed. Continuing");
    }
e('View updates complete');
e('');

/**
 * Adding geo data for downloads
 */

e('Starting on Downloads');
$productDownload            = new Nexva_Analytics_ProductDownload();
$productViewTempList    = '_productDownloadUniqueIpList';
$productDownload->generateUniqueIpList($productViewTempList);

$tempDb     = $productDownload->_getConnectionForTempCollections();
$tempCollection = $productDownload->_getCollection($productViewTempList, true);
$collection     = $productDownload->_getCollection();


//loop through it and update with the deets
$results    = $tempCollection->find();
$count      = 0;
foreach ($results as $row) {
    $ip = $row['_id']['ip'];
    $i->load($ip);
    
    if (substr($i->country, 0, 1) != '?') {
        $country    = array(
            'name'  => $i->country,
            'code'  => $i->countryCode 
        );
        $collection->update(array('ip' => $ip), array('$set' => array('country' => $country)), array("multiple" => true));    
    } 
    
    $count++;
    if ($count % 10000 == 0) {
        e('Updated ' . $count);    
    }
}
//drop the temp data
    try {
        $tempDb->selectCollection($productViewTempList)->drop();
    } catch(Exception $ex) {
        e("ERROR : Couldn't delete {$productViewTempList}. Manual delete needed. Continuing");
    }
e('Download updates complete');

unlock(PROCESS);




//Usage
//$i->load('203.153.222.17');
//echo $i->countryCode . '';
//echo $i->country . '';
