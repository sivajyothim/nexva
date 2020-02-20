<?php

class Partnermobile_Model_Products extends Zend_Db_Table_Abstract
{
    protected $_name = 'products';
    protected $_id = 'id';
    public $languageId          = false;
    public $defaultLanguageId   = false;
    protected $appRules = ''; // This is the new and preferred way to handle app filter. Sadly the word filter has already been taken by price filters

    public $appFilter   = 'ALL'; //filters apps based on price

    public function getDetailsById($appId)
    {        
        $sql = $this->select();
        $sql->from($this->_name,array('name', 'price', 'user_id'))
            ->where('id = ?',$appId);        
         
        return $this->fetchRow($sql);
        
    }

    public function getSearchProductsByKey($deviceId, $keyword, $chapId, $category = null, $limit = null, $page = null)
    {
        $where = array(
            'join'  => array('chap_products' => 'products.id = chap_products.product_id',
                             'product_meta' => 'products.id = product_meta.product_id',
                             'user_meta' => 'products.user_id = user_meta.user_id'
            ),
            'where' => array(
                'chap_products.chap_id = "'.$chapId.'"',
                'product_meta.meta_name = "FULL_DESCRIPTION"',
                'user_meta.meta_name = "COMPANY_NAME"',
                'products.name LIKE "%' . $keyword . '%" ',
                //'products.keywords LIKE "%' . $keyword . '%"',
                //'product_meta.meta_value LIKE "%'.$keyword.'%"',
                //'user_meta.meta_value LIKE "%'.$keyword.'%"'
                //'products.name LIKE "%' . $keyword . '%" OR products.keywords LIKE "%' . $keyword . '%" OR product_meta.meta_value LIKE "%'.$keyword.'%" OR user_meta.meta_value LIKE "%'.$keyword.'%"',
                'products.name LIKE "%' . $keyword . '%"',
            )
        );

        //return $where;
        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $where, $limit, $page);

        $productsInfo = array();
        $product = new Model_Product();

        foreach ($productIds as $key => $value) {
            $productsInfo[] = $product->getProductDetailsById($value->product_id);
        }

        /*  test begins here    */
        //Zend_Debug::dump($productsInfo);die();
        /*echo count($productsInfo),'<br/>';
        foreach($productsInfo as $product){
            echo $product['id'],'-name--------- ',$product['name'],'-keyword--------- ',$product['keywords'],'<br/>';
        }

        die();*/
        /*  test ends here    */

        return $productsInfo;
    }

    // Return the product details for non english chapter site
     public function getSearchProductsByKeyProductLangMeta($deviceId, $keyword, $chapId, $category = null, $limit = null, $page = null, $chapLangId = null)
    {
        $where = array(
            'join'  => array(
                'chap_products' => 'products.id = chap_products.product_id',
                'product_language_meta' => 'products.id = product_language_meta.product_id'),
            //'join'  => array('product_language_meta' => 'products.id = product_language_meta.product_id'),
            'where' => array(
                'chap_products.chap_id = "'.$chapId.'"',
                'product_language_meta.meta_value LIKE "%'.trim($keyword).'%"',
                'product_language_meta.meta_name = "PRODUCT_NAME"',
            ),
             'order' => array('products.id desc' )
        );

        //return $where;
        $productIds = $this->getCompatibleAppsByDeviceId($deviceId, $where, $limit, $page);

        $productsInfo = array();
        $product = new Model_Product();

        foreach ($productIds as $key => $value) {
            $productsInfo[] = $product->getProductDetailsById($value->product_id, FALSE, $chapLangId);
        }
        return $productsInfo;
    }
    
    //Done
    protected function getCompatibleAppsByDeviceId($deviceId, $filter = null, $limit = null, $page = null, $getFromCache = true)
    {
        $productDevices = new Model_ProductDevices();
        $productDevices->languageId         = $this->languageId;
        $productDevices->defaultLanguageId  = $this->defaultLanguageId;
        $productDevices->appFilters         = $this->appRules;

        $product = new Model_Product();
        //check if device has been used,
        if (empty($deviceId )) {
            unset($filter['union']); //this method doesn't use a union
            return $product->getAllProducts($filter, $limit, $page, $getFromCache);
        } else {
           // unset($filter['order']); //ordering needs to be done on the union
            return $productDevices->getAllProductsByDevId($deviceId, $filter, $limit, $page, $getFromCache);
        }
    }

}