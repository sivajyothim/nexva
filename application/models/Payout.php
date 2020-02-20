<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_Payout extends Zend_Db_Table_Abstract {

  protected $_name = 'payouts';
  protected $_id = 'id';

  function __construct() {
    parent::__construct();
  }

  
    public function getPayoutForUser($userId = 0) {
        $select     = $this->select(false)->setIntegrityCheck(false);
        $select     ->from('users', array())
                    ->joinLeft('payouts', 'users.payout_id = payouts.id', array('*'))
                    ->where('users.id = ?', $userId)->limit(1);
        return $this->fetchAll($select)->current();

  }
}

?>
