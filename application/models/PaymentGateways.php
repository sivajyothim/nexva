<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_PaymentGateways extends Zend_Db_Table_Abstract {

  protected $_name = 'payment_gateways';
  protected $_id = 'id';

  function __construct() {
    parent::__construct();
  }
  

  

}

?>
