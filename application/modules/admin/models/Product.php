<?php

class Admin_Model_Product extends Zend_Db_Table_Abstract {

    protected  $_id     = 'id';
    protected  $_name   = 'products';

    public function  __construct() {
        parent::__construct();
    }

    /**
     * Get uploaded proudcts by Type ('APPROVED','NOT_APPROVED','REJECTED');
     *
     * @param String $type
     * @return Zend_Db_Table_Rowset_Abstract
     *
     */
    public function getProductByStatus($status,$search='',$searchin='name') {

        $search  =   trim(strip_tags($search));
        
        $db = Zend_Registry::get('db');

        $product =   $this->select('products')->setIntegrityCheck(false);

        if(isset ($status) and '' != $status) {
            $product->where("products.status = '$status' and deleted <> 1");
        }
        
        

        if(isset($search) and '' != $search) {
            if($searchin != 'cp') {
                $product->where("$searchin like ".$db->quote("%" . $search . "%")." and deleted <> 1");
            }else {

                //$modelCp =   new Model_User();
                //$cp  =   current($modelCp->fetchAll("username = '$search'")->toArray());
                $modelProduct    = new Model_Product();
                return   $modelProduct->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
                                ->setIntegrityCheck(false)
                                ->columns('name')
                                ->join('users', 'products.user_id =users.id',array('username','email') )
                                ->where("username like ".$db->quote("%" . $search . "%")." and deleted <> 1")
                                ->order('products.name');


              
            }

        }


        return  $product ->join('users', 'products.user_id = users.id', array('username','email'))
                ->where("products.deleted <> 1")
                ->order('products.id desc');
    }

    /**
     * Returns the total number of approved apps
     */
  function getTotalApprovedApps($startDate = null, $endDate = null) {
        $select = $this->select(false);
        if($startDate) {
        $select ->from('products', 'COUNT(id) AS totalRows')
                ->where('status = ?', 'APPROVED')
                ->where("created_date BETWEEN '$startDate' and '$endDate' ") 
                ->where('deleted <> ?', '1');
                
        } else {
        $select ->from('products', 'COUNT(id) AS totalRows')
                ->where('status = ?', 'APPROVED')
                ->where('deleted <> ?', '1');
                
        	
        }
        return $this->fetchRow($select)->totalRows;                
    }
    
    
    /**
     * Gives the total number of apps broken down by platform 
     */
    function getApprovedAppsByPlatform() {
        $db     = Zend_Registry::get('db');
        $results    = $db->query("
            SELECT PL.name, COUNT(DISTINCT P.id) AS count
            FROM product_builds PB LEFT JOIN products P ON PB.product_id = P.id 
            LEFT JOIN platforms PL ON PL.id = PB.platform_id
            WHERE 
                P.status = 'APPROVED' 
                AND P.deleted <> 1
            GROUP BY PL.name
            ORDER BY count DESC
        ")->fetchAll();
        
         foreach($results as $values)	{
        	
        	$newArray[$values->name] = $values->count;
        	
        }
        
        return $newArray;
        
       
    }
    
    
    
	function getApprovedAppsByPlatformByRange($startDate, $endDate) {
        $db     = Zend_Registry::get('db');
        $results    = $db->query("SELECT PL.name, COUNT(DISTINCT P.id) AS count
            FROM product_builds PB LEFT JOIN products P ON PB.product_id = P.id 
            LEFT JOIN platforms PL ON PL.id = PB.platform_id
            WHERE 
                P.status = 'APPROVED' 
                AND P.deleted <> 1 
                AND (P.created_date BETWEEN '$startDate' and '$endDate')
            GROUP BY PL.name
            ORDER BY count DESC
        ")->fetchAll();
        
         return $results;
       
    }
    
    /**
     * @param $price
     * @param $id
     * @return Sending app id & price to check whether that price is in the price list in the product table, it is eliminating self record & check for APPROVED apps prices only
     */
    function checkPrice($price,$id)
    {
    	$sql = $this->select()
    	->from('products')
    	->where('products.price = ?',$price)
    	->where('products.id <> ?',$id)
    	->where('products.status = ?','APPROVED');
    	return $price = $this->fetchAll($sql);
    }

    function getProductPrices($productIDs)
    {
        $price_sql = $this  ->select()
            ->setIntegrityCheck(false)
            ->from('products')
            ->where('products.id IN (?)',$productIDs);
        //Zend_Debug::dump($price_sql->assemble());die();
        $prices = $this->fetchAll($price_sql)->toArray();
        return $prices;
    }

    function getProductPrice($ID)
    {
        $price_sql = $this  ->select()
                            ->setIntegrityCheck(false)
                            ->from('products')
                            ->where('products.id = '.$ID);
        $prices = $this->fetchAll($price_sql)->toArray();
        return $prices;
    }

    /**
     * @param $userId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    function userHaveApps($userId){
        $sql =  $this   ->select()
                        ->from(array('p'=>$this->_name))
                        //->setIntegrityCheck(false)
                        ->where('p.user_id = ?',$userId)
                ;
        return $this->fetchAll($sql);

    }
    
    function getAppName($appId){
        $sql =  $this   ->select()
                        ->from(array('p'=>$this->_name),array('name'))
                        ->where('p.id = ?',$appId);
        $appDetails= $this->fetchAll($sql);
        return $appDetails->getRow(0);

    }

    
}

?>