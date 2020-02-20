<?php
/**
 * Holds product translations
 * @author Administrator
 *
 */
class Model_ProductLanguageMeta extends Nexva_Db_EAV_EAVModel {
    protected $_name    = 'product_language_meta';
    protected $_primary = 'id';

    protected $_metaModel = "Model_ProductLanguageMeta";
    protected $_entityIdColumn = "product_id";
    protected $_referenceMap    = array(
            'Model_Product' => array(
                            'columns'           => array('product_id'),
                            'refTableClass'     => 'Model_Product',
                            'refColumns'        => array('id')
            ),
    );

    function __construct() {
        parent::__construct();
        $db     = Zend_Registry::get('db');
        $db->query('SET NAMES "utf8"')->execute();
    }
    
    function saveTranslation($proId, $langId, $data, $translationId = null) {
        $name   = array(
            'product_id'    => $proId,
            'language_id'   => $langId,
            'meta_name'     => 'PRODUCT_NAME',
            'meta_value'    => $data['name']   
        );
        
        $summary = $desc = $name;
        $summary['meta_name']   = 'PRODUCT_SUMMARY';
        $summary['meta_value']  = $data['summary'];
        
        $desc['meta_name']   = 'PRODUCT_DESCRIPTION';
        $desc['meta_value']  = $data['desc'];
        
        $data   = array($name, $summary, $desc);
        
        $this->delete("product_id = {$proId} AND language_id = {$langId}");
        foreach ($data as $datum) {
           try {
                $this->insert($datum);
           } catch (Exception $ex) {
               throw $ex;
           } 
        } 
    }
    
    /**
     * This is used for the admin area 
     * @param $proId
     * @param $langId
     */
    function loadTranslation($proId, $langId) {
        
        $translation    = new stdClass();
        $translation->PRODUCT_NAME          = '';
        $translation->PRODUCT_DESCRIPTION   = '';
        $translation->PRODUCT_SUMMARY       = '';
        $translation->language_id           = '';
        
        $data   = $this->fetchAll("product_id = {$proId} AND language_id = {$langId}");
        if ($data) {
            $data   = $data->toArray();   
            foreach ($data as $row) {
                $translation->$row['meta_name'] = $row['meta_value'];
                $translation->language_id       = $row['language_id'];     
            }
        }
        return $translation;
    }
    
    /**
     * Used by the front end. Cascades translations 
     * @param $product array
     * @param $langId
     */
    function translateProduct($product, $langId = null, $chapId = null) {
        if (empty($product['id'])) {
            return $product;
        }
        
        $proId          = $product['id'];
        $langModel      = new Model_Language();
        $defaultLangObj = $langModel->getDefaultLanguage();  
        $defaultLang    = $defaultLangObj->id; 
        
        $langId     = ($langId) ? $langId : $defaultLang;
        if ($langId == $defaultLang) {
            return $product; //no need for translations, it's in english
        }
        
        $stmnt      = $this->select();
        $stmnt->from('product_language_meta', array('*'))
            ->where("product_id = {$proId} AND language_id = {$langId}");  
       
        
        
        $data           = $this->fetchAll($stmnt);
        $translations   = new stdClass();
        if ($data) {
            foreach ($data as $item) {
                $translations->$item['meta_name']    = $item['meta_value']; 
            }
        } 
        

        
        //MTNI only need english for app names
     //   if($chapId != 23045){
            $product['name']            = isset($translations->PRODUCT_NAME) ? $translations->PRODUCT_NAME : $product['name'];
     //   }
        
/*        //MTNI only need english for app names
        if($chapId == 23045){
            
            if($product['name'] == "") {
                $product['name']            = isset($translations->PRODUCT_NAME) ? $translations->PRODUCT_NAME : $product['name'];
                }
            }
           */

        $product['desc_brief']      = isset($translations->PRODUCT_SUMMARY) ? $translations->PRODUCT_SUMMARY : $product['desc_brief'] ;
        $product['desc']            = isset($translations->PRODUCT_DESCRIPTION) ? $translations->PRODUCT_DESCRIPTION : $product['desc'];
        
       

        return $product;
    }
    
    function getAllTranslations($proId = null) {
        if (!$proId) {
            return array();
        }
        
        $select     = $this->select(true);
        $select
            ->setIntegrityCheck(false)
            ->joinLeft('languages', 'languages.id = product_language_meta.language_id', array('languages.name', 'languages.id AS language_id'))
            ->where('product_language_meta.meta_name = "PRODUCT_NAME"')
            ->where('languages.status = 1')
            ->where('product_id = ' . $proId);  
       $data    = $this->fetchAll($select);
       return $data;
    }
}
?>