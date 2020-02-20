<?php
/**
 *
 * @copyright   neXva.com
 * @author      Cheran@nexva.com
 * @package     cp
 *
 */
class Model_Platform extends Zend_Db_Table_Abstract {

    protected $_name = "platforms";
    protected $_id  = "id";


    function __construct() {
        parent::__construct();
    }
    
    /**
     * Fetch All platforms
     * @return <type> 
     */
    public function getAllPlatforms() {
        $resultSet = $this->fetchAll('status = 1');
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = $row;
            $entries[$row->id] = $row->name;
        }
        return $entries;
    }

    public function getPlatformName($platformId){
        $row = $this->find($platformId);

        if(count($row)>0){
       return $row->current()->name;
        }
    }
    
    public function getPlatforms()
    {
        $platformSql   = $this->select(); 
        $platformSql->from('platforms', array('id','name'))
                    ->where('status= ?',1) 
                    ->where('id != ?',0)  
                    ->order('name ASC');         
           
         return $this->fetchAll($platformSql);
    }    
    
    public function getPlatformByid($id)
    {
    	$platformSql   = $this->select();
    	$platformSql->from('platforms', array('id','name'))
    	            ->where('status= ?',1)
    	            ->where('id = ?',$id);
    	 
    	return $this->fetchAll($platformSql);
    }
    
    public function getPlatformDetalsByid($id)
    {
    	$platformSql   = $this->select();
    	$platformSql->from('platforms', array('id','name'))
    	            ->where('status= ?',1)
    	            ->where('id = ?',$id);
    	return $this->fetchRow($platformSql);
    }
    
    public function getPlatformIdByName($name)
    {
    	$platformSql   = $this->select();
    	$platformSql->from('platforms', array('id'))
    	            ->where('status= ?',1)
    	            ->where('name = ?',$name);
    	return $this->fetchRow($platformSql);
    }

}

?>
