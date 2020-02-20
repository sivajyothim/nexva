<?php
set_time_limit(0);

define('PROCESS', 'ANALYTICS_CACHE');

include_once("../../application/BootstrapCli.php");
include_once('common.php');



/**
 * This is the big ole file that will generate all the temporary views for the analytics 
 * If you add any new views for analytics, make sure to offload the heavy work to this 
 * file if it takes more than a second or two to generate the data/views
 */
    try {
        MongoCursor::$timeout = -1;
    
        lock(PROCESS);
    
        e("Starting...");
        
        e("Creating Poduct View Caches");
        $productView    = new Nexva_Analytics_CacheBuilder_ProductView();
        $productView->createCache(true);
        e("Finished Poduct View Caches");
        e("");
        
        e("Creating Poduct Dowload Caches");
        $productDownload    = new Nexva_Analytics_CacheBuilder_ProductDownload();
        $productDownload->createCache(true);
        e("Finished Poduct Download Caches");
        e("");
        
        unlock(PROCESS);
    } catch (Exception $ex) {
        file_put_contents('error.log', print_r($ex, true));     
        echo $ex;   
        unlock(PROCESS);
    }
    

    