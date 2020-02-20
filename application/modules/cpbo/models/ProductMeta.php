<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 12/10/13
 * Time: 1:16 PM
 * To change this template use File | Settings | File Templates.
 */
class Cpbo_Model_ProductMeta extends Zend_Db_Table_Abstract{

    protected $_id   =   'id';
    protected $_name =   'product_meta';

    function  __construct(){
        parent::__construct();
    }

    function compareData($productId,$form_values){

        $productChangedTime = $form_values['product_changed'];
        unset($form_values['product_changed']);
        unset($form_values['product_id']);

        $sql = $this->select()
                    ->from(array('pm' => $this->_name))
                    ->where('pm.product_id =?',$productId);
        $results = $this->fetchAll($sql)->toArray();

        if(count($results) == 0){
            return false;
        }
        else{
            $exist_value = array();
            foreach($results as $result){
                if($result['meta_name'] != 'PRODUCT_CHANGED'){
                    $exist_value[strtolower($result['meta_name'])] = $result['meta_value'];
                }
            }
            $val = array_diff($form_values, $exist_value);
            $form_values['product_changed'] = $productChangedTime;
            $form_values['product_id'] = $productId;
            if($val){
                return true;
            }else{
                return false;
            }
        }
    }
}