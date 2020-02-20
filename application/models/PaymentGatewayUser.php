<?php
/**
 * @copyright   neXva.com
 * @author      Rooban <rooban at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

class Model_PaymentGatewayUser extends Zend_Db_Table_Abstract {

    protected $_name = 'payment_gateway_user';
    protected $_id = 'id';

    function __construct() {
        parent::__construct();
    }
  
    /**
    * Get default payment gateway for chap
    * @param <type> $chapId
    * @return <type>
    */
    function getPaymentGatewayByChapId($chapId) {
        
        $sql = $this->select();
        $sql->from(array('pgu' => $this->_name), array('pgu.id','pgu.payment_gateway_id'))
            ->setIntegrityCheck(false)
            ->join(array('pg' => 'payment_gateways'), 'pgu.payment_gateway_id = pg.id', array('name','gateway_id','maketing_name'))
            ->where('pgu.chap_id = ?',$chapId)
            ->where('pgu.status = ?',1)
            ;  
        
        //echo $sql->assemble(); die();
        return $this->fetchAll($sql);

    }

}

?>
