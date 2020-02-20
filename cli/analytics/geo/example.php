<?php
include_once('../../../application/BootstrapCli.php');
include 'Ip2Country.php';
$i = new Ip2Country;
$i->dir = APPLICATION_PATH . '/../cli/analytics/geo/php_db/';

//run below function once only. It will parse IpToCountry.csv
//file into PHP files and save them into php_db directory
//$i->parseCSV2();

//to display countryCode:
echo $i->load('106.185.32.199')->countryCode;
echo '106.185.32.199';
//to display country and countryCode:
$i->load('179.217.143.70');
echo $i->countryCode;
echo $i->country;
