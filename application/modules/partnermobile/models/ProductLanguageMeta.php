<?php

class Partnermobile_Model_ProductLanguageMeta extends Zend_Db_Table_Abstract
{
    protected $_name = 'product_language_meta';
    protected $_id = 'id';
   
    //return the translation of products PRODUCT_NAME,PRODUCT_SUMMARY,PRODUCT_DESCRIPTION
    public function getTranslationValue($productId,$languageId,$metaName)
    {        
        $sql = $this->select();
        $sql->from($this->_name,array('meta_value'))
            ->where('product_id = ?',$productId)
            ->where('language_id = ?',$languageId)
            ->where('meta_name = ?',$metaName); 
        //echo $sql->assemble(); die(); 
        return $this->fetchRow($sql)->meta_value;
        
    }

}