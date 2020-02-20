<?php
if (php_sapi_name() != 'cli')
{
    exit('Command line use only');
}

include_once("../application/BootstrapCli.php");

// premium
//$chaps = array(21134, 25022, 33644, 36015, 80184, 81449, 110721, 114306, 115189, 163302, 274515, 276531, 280316, 320345);
        
        
// free
//$chaps = array(61512, 61517, 61522, 61523, 61525, 61527, 61534, 61535, 61537, 61538, 61544, 61519);
//$chaps = array(585474, 585480, 21134, 25022, 33644, 36015, 80184, 81449, 110721, 114306, 115189, 163302, 274515, 276531, 280316, 320345, 61512, 61517, 61522, 61523, 61525, 61527, 61534, 61535, 61537, 61538, 61544, 61519 ); // Orage

//free and premium 
//$chaps = array(21134, 25022, 33644, 36015, 80184, 81449, 110721, 114306, 115189, 163302, 274515, 276531, 280316, 320345, 585474, 585480, 21134, 25022, 33644, 36015, 80184, 81449, 110721, 114306, 115189, 163302, 274515, 276531, 280316, 320345, 61512, 61517, 61522, 61523, 61525, 61527, 61534, 61535, 61537, 61538, 61544, 61519 ); // Orage
//Orange


$chaps = array(585480, 585474);


$apps =  array(63519,
63522,
63541,
63555,
63557,
63560,
63565,
63567,
63568,
63572,
63584,
63600,
63601,
63605
);

//60403, 59336, 58681, 58608, 54185, 60512, 60512, 60466, 60582, 60595 free
//60354, 60358, 60356, 60355, 60352, 60353, 60355 premium


//$chaps = array(100791, 110721, 114306, 115189,   163302, 274515,  280316, 282227, 320342, 320343, 320344,324405, 326728);
//21134 nextapps.mtnonline.com		
//274515	apps.airtel.lk
//276531	mtnivorycoast 
//280316	airteldrc	
//282227	airtelcongo
//320342	airteluganda
//320343	airtelchad
//320344	sierraleone
//320345	mtnbenin
//324405	airtelmadagascar
//326728	airtelbakinafaso
//397395 airtelgh
//163302 malwwi
//274515 niger 
//276531 mtniverycoast 
//71918 xzambia 
// /71949 kenya 
//mtnuganda
//airtel ng 
//100791 airtel tz 
// 110721 airtelgabon 
//114306 airtelrwanda
$chapProducts = new Pbo_Model_ChapProducts();

foreach($chaps as $chapId) {

$count = 0;
foreach($apps as $productId) {
    
  //  $chapProducts->addProductToChap($chapId, $productId);
  //  continue;
    
    $count++;
    $added = $chapProducts->checkProductsadded($chapId, $productId);
    
    if($added) {
        echo 'not added'.$productId. ' '.$count."\n\r";
        continue;
    }    else {
       $chapProducts->addProductToChap($chapId, $productId);
       echo 'added'.$productId. ' '.$count."\n";
    }
    

    
}

}
