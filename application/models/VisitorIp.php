<?php
/**
 * @copyright   neXva.com
 * @author      Rooban <rooban at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

class Model_VisitorIp extends Zend_Db_Table_Abstract {

  protected $_name = 'visitor_ip';
  protected $_id = 'id';

  function __construct() {
    parent::__construct();
  }
  
  function selectIp($ip){
      $select = $this->select(array('INET_NTOA(ip)','country_code'))
                     ->where('ip = ?', sprintf("%u", ip2long($ip)));
      
      $result = $this->fetchRow($select);
      if(count($result) > 0){
          return $result->toArray();
      }
  }
  
  function insertIp($data){
      return $this->insert($data);     
  }
  

}

?>
