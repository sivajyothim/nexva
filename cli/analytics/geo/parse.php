<?php
include_once('../../../application/BootstrapCli.php');
include 'Ip2Country.php';
$i = new Ip2Country;
$i->dir = APPLICATION_PATH . '/../cli/analytics/geo/php_db/'; 

//run below function once only. It will parse IpToCountry.csv
//file into PHP files and save them into php_db directory
//$i->parseCSV2();

//to display countryCode:


//to display country and countryCode:
$i->load('1.23.221.247');
print_r($i);
echo $i->countryCode . '';
echo $i->country . '';
