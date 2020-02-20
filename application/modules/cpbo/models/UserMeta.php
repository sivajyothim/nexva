<?php

class Cpbo_Model_UserMeta extends Nexva_Db_EAV_EAVModel{
    protected $_name    = 'user_meta';
    protected $_primary = 'id';


    protected $_metaModel = "Model_UserMeta";
    protected $_entityIdColumn = "user_id";
    
    function  __construct() {
        parent::__construct();
    }

    function getUserMeta($userid){

        $meta   =   $this->fetchAll('user_id ='.$userid,'id')->toArray();

        foreach($meta as $key=>$value){

               $user_meta[$value['meta_name']] =   $value['meta_value'];
        }
       
      

    return $user_meta;
    }
}
?>