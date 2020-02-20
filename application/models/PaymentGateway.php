<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_PaymentGateway extends Zend_Db_Table_Abstract {

  protected $_name = 'payment_gateways';
  protected $_id = 'id';

  function __construct() {
    parent::__construct();
  }

    /**
    * Get payment gateway charges
    * @param <type> $id
    * @return <type>
    */
    function getPaymentGatewayChargesPerProduct($id, $price) {
        $pg = $this->select()
                ->where("id=?", $id);
        $pgRow = $this->fetchRow($pg);
        return $price * ($pgRow->charge / 100) + $pgRow->fixed_cost;
    }

    /**
    * get the payment gateway charges rowset
    *
    * @return payment gateway charges rowset
    */
    function getPaymentGatewayChargesForList() {
        $paymentGateways = $this->select()->from('payment_gateways')->order('payment_gateways.id')->query()->fetchAll();
        return $paymentGateways;
    }

    function createPaymentGateway($PaymentGateway) {

        $values = array(
            "name"              => $PaymentGateway['paymetgatewayName'],
            "charge"            => $PaymentGateway['paymetgatewayCharge'],
            "fixed_cost"        => $PaymentGateway['paymetgatewayFixedCost'],
            "supports_web"      => $PaymentGateway['supportsWeb'],
            "supports_mobile"   => $PaymentGateway['supportsMobile'],
            "supports_inapp"    => $PaymentGateway['supportsInapp'],
            "status"            => $PaymentGateway['status']
        );

        $res  =   $this->insert($values);
        return $payoutArray;
    }
    
    
    function updatePaymentGateway($id, $PaymentGateway)    
    {            
        $this->update(
            array(
                "name"              => $PaymentGateway['paymetgatewayName'],
                "charge"            => $PaymentGateway['paymetgatewayCharge'],
                "fixed_cost"        => $PaymentGateway['paymetgatewayFixedCost'],
                "supports_web"      => $PaymentGateway['supportsWeb'],
                "supports_mobile"   => $PaymentGateway['supportsMobile'],
                "supports_inapp"    => $PaymentGateway['supportsInapp'],
                "status"            => $PaymentGateway['status']
            ),"id = ".$id);                
    }
    
    
    
    function getPaymentGatewayById($id) {

        $select = $this->select()
        ->setIntegrityCheck(false)
        ->from('payment_gateways')
        ->where('id =  ' . $id);

        return $this->fetchRow($select);
    }
    
/* List all the payment gateways for inapp 
     * @param int $supportRecurring if it is support ruccring 1 or 0 
     * @return payment gateways list 
     * @author Chathura
     */
    
    function getPaymentGatewaysForInapp($supportRecurring = 0) {
        
        if ($supportRecurring == 0)
            return $this->fetchAll ( 'status = 1 AND supports_inapp = 1' );
        else
            return $this->fetchAll ( 'status = 1 AND supports_inapp = 1 AND supports_recurring = 1' );
    
    }

    /**
     * Returns a list of payment gateways that supports a certain functionality.
     * 
     * For this function to work, you must ensure that the field names that indicates support in payment_gateways schema must begin 
     * with 'supports_' + functionality. <br /><br />
     * e.g: supports_inapp, supports_web, supports_mobile etc. <br /><br />
     * Functionality is case-insensitive (MoBilE = mobile).
     *
     * @param string|array $supports Supported functionality - e.g: mobile, web, recurring. If this is an array, it would return all gateways that match criteria
     * @param int $enabled
     */
    function getPaymentGatewaysThatSupport($supports, $enabled = 1) {

        $supportsString = "";

        if( is_array($supports) ) {
            foreach( $supports as $support ) {
                $supportsString .= " AND supports_".strtolower($support)."=1 ";
            }
        }
        else {
            $supportsString = ' AND supports_'.strtolower($supports)."=1";
        }

        $cache     = Zend_Registry::get('cache');
        $key       = 'PAYMENT_GATEWAYS_' . md5("status = $enabled $supportsString");
        if (($results = $cache->get($key)) === false) {
            $results    = $this->fetchAll("status = $enabled $supportsString");
            $cache->set($results, $key);
        }
        return $results;

    }

}

?>
