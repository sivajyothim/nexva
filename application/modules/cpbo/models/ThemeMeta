<?php
class Cpbo_Model_ThemeMeta extends Zend_Db_Table_Abstract{

    protected $_id   =   'id';
    protected $_name =   'theme_meta';

    function  __construct(){
        parent::__construct();
    }

    function getThemeMetaByChapID($chapId)
    {
        $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('tm' => $this->_name))
                    ->where('tm.user_id =?',$chapId);
        return $this->fetchAll($sql);
    }
}
