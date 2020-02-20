<?php

/**
 * Returns no of download for given chap
 */
class Nexva_View_Helper_DownloadStatsByChap extends Zend_View_Helper_Abstract {

  /**
   * Returns Download count
   * @param User Id $userId
   * @param Chap Id $chapId
   * @return Download count
   */

    public function DownloadStatsByChap($productId, $chapId){

        $statisticsDownloadsModel   =   new Model_StatisticDownload();
        $appCounts  =  $statisticsDownloadsModel
                ->select()
                ->setIntegrityCheck(false)
                ->from("statistics_downloads", "count(id) as count")
                ->where("product_id = '$productId'")
                ->where("chap_id = '$chapId'")
                ->query()
                ->fetch();

       return($appCounts->count);

    }
}

?>