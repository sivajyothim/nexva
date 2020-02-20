<?php

/**
 * Get the Vendor name of an app
 */
class Nexva_View_Helper_Vendor extends Zend_View_Helper_Abstract {

  /**
   * Returns Vendor name of the App
   *
   * @param userId $userId
   * @return Vendor name of the App.
   */
  public function vendor($userId) 
  {    
      $userMeta = new Model_UserMeta();      
      
      $userMeta->setEntityId($userId);
      $vendor = $userMeta->COMPANY_NAME;
     
      return $vendor;            
  }

}

?>