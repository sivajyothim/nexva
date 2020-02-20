<?php
/**
 * 
 * Chap Specific Product Model Class
 * @author Maheel - 02/04/2012
 *
 */
class Chap_Model_Product extends Zend_Db_Table_Abstract  {
    
    
    protected $_name = 'products';
    protected $_id = 'id';
    
    /**     
     * Returns all chap related applications, IF filtering rules has been done,
     * fetch only the filtered appsa, otherwise fetch all
     *
    */
    public function getAllChapProducts($chapId)
    {
            $productSql   = $this->select(); 
            $productSql->from($this->_name,array('id','name','price','product_type','created_date'))                   
                        //->setIntegrityCheck(false)                   
                       // ->join(array('u' => 'users'), 'p.user_id = u.id', array('p.id')) 
                       // ->where('u.chap_id =?', $chapId)
                        ->where('deleted != ?',1) 
                        ->where('status = ?','APPROVED') 
                        ->order('id DESC'); 

            //Excluding manually sand boxed contents
            $exludedChaps = array();
            $exludedChaps[] = 6691;    

            //Filter apps by chap, check if the chap_id is exists with in excluded chaps
            if(!in_array($chapId, $exludedChaps))
            {            
               $productSql->where('user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)');
            }


            $appRules       = new Model_ProductUserRule();
            $productSql        = $appRules->applyRules($chapId, $productSql);

            return $this->fetchAll($productSql);
         
        
    }
    
    
    public function getSerachedChapProducts($chapId,$searchKey)
    {
        
            $productSql   = $this->select();
            $productSql->from($this->_name,array('id','name','price','product_type','created_date')) 
                    ->where('deleted != ?',1) 
                    ->where('status = ?','APPROVED') 
                    ->where('name LIKE ?', '%'.$searchKey.'%')  
                    ->order('name DESC');            
        
            //Excluding manually sand boxed contents
            $exludedChaps = array();
            $exludedChaps[] = 6691;    

            //Filter apps by chap, check if the chap_id is exists with in excluded chaps
            if(!in_array($chapId, $exludedChaps))
            {            
               $productSql->where('user_id NOT IN (SELECT id FROM users WHERE chap_id = 6691)');
            }


            $appRules       = new Model_ProductUserRule();
            $productSql        = $appRules->applyRules($chapId, $productSql);

            return $this->fetchAll($productSql);
    }
    
   
    
}