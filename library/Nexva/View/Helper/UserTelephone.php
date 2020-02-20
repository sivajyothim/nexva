<?php

/**
 * Returns no of download or Uploads of a user
 */
class Nexva_View_Helper_UserTelephone extends Zend_View_Helper_Abstract {

  /**
   * Returns telephone no. of the user
   *
   * @param User Id $userId
   */
  public function userTelephone($userId) 
  {    
      $userMetaModel = new Model_UserMeta();
      $telephone =  $userMetaModel->getTelephone($userId);           

      if(empty($telephone))
      {
          $telephone =  '-';
      }
      
      return $telephone;
  }

}

?>