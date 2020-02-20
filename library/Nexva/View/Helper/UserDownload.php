<?php

/**
 * Returns no of download or Uploads of a user
 */
class Nexva_View_Helper_UserDownload extends Zend_View_Helper_Abstract {

  /**
   * Returns Download / Upload count
   *
   * @param User Id $userId
   * @param User Type $userType
   * @return Download / Upload count
   */
  public function userDownload($userId, $userType) 
  {    
      if($userType == 'USER')
      {
          $statsModel = new Pbo_Model_StatisticsDownloads();
          return $statsModel->getDownloadCountsByUser($userId, $userType);
      }      
      else
      {
          $productModel = new Pbo_Model_Products();
          return $productModel->getUploadCountsByUser($userId, $userType);
      }
  }
}

?>