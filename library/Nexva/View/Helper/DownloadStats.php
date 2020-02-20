<?php

class Nexva_View_Helper_DownloadStats extends Zend_View_Helper_Abstract {


        public function DownloadStats($productId){

            $statisticsDownloadsModel   =   new Model_StatisticDownload();
            $count  =  $statisticsDownloadsModel
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from("statistics_downloads", "count(id) as count")
                    ->where("product_id = '$productId'")
                    ->query()
                    ->fetch();

           return($count->count);
           
        }
    
}


?>