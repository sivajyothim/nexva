<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 4/29/13
 * Time: 4:51 PM
 * To change this template use File | Settings | File Templates.
 */

class Model_PricePoint extends Zend_Db_Table_Abstract {

    protected $_name = 'price_points';
    protected $_id = 'id';

    function __construct() {
        parent::__construct();
    }
########################################################################################################################
#                                                                                                                      #
#                                               Admin Section                                                          #
#                                                                                                                      #
########################################################################################################################

    //passing values for listing the Price Points
    function getPricePointList()
    {
        $pricePoints = $this->select();
        $pricePoints        ->from('price_points')
                            ->setIntegrityCheck(false)
                            ->columns(array('price_points.id AS price_point_id','price_points.gateway_id AS GATEWAY_ID'))
                            ->join('payment_gateways', 'payment_gateways.id = price_points.gateway_id')
                            ->where('price_points.deleted = 1');

        $filterGateway = Zend_Session::namespaceGet('filter_session');
                            if(!empty($filterGateway['selected_gateway']))
                            {
                                $pricePoints->where('price_points.gateway_id =?',$filterGateway['selected_gateway']);
                            }

                            if(!empty($filterGateway['filter_keyword']))
                            {
                                $pricePoints->where('price_points.price_point LIKE "%'.$filterGateway['filter_keyword'].'%"');
                            }
        $pricePoints        ->order('price_points.id ASC');

        $pricePoints = $this->fetchAll($pricePoints)->toArray();

        return $pricePoints;
    }

    function checkExists($pricePoint)
    {
        //return $pricePoint['price'];
        $pricePoints = $this->select();
        $pricePoints        ->from('price_points')
                            ->where('price_points.price =? ',$pricePoint["price"])
                            ->where('price_points.price_point =? ',$pricePoint["price_point"])
                            ->where('price_points.gateway_id =? ',$pricePoint["gateway_id"]);
        //return $pricePoints->__toString();
        $pricePoints = $this->fetchAll($pricePoints);
        $rowCount = count($pricePoints);
        if($rowCount > 0)
        {
            return TRUE;
        }
        else
        {
            return false;
        }
    }
}