<?php
/**
 * 
 
 */
class Pbo_Model_Orders extends Zend_Db_Table_Abstract  
{    
    protected $_name = 'Orders';
    protected $_id = 'id';
    
    /**
     * Returns upload count
     * @param - $chapId
     * @param - $appType
     * @param - $fromDate
     * @param - $toDate    
     */
    public function getUploadCount($chap_id, $appType=null ,$fromDate=null, $toDate=null)
    {   
        $uploadSql = $this->select();
        $uploadSql->from(array('p' => $this->_name),array('count(p.id) as upload_count'))
                ->setIntegrityCheck(false)  
                ->join(array('u' => 'users'), 'p.user_id = u.id', array(''))
                ->where('p.status = ?', 'APPROVED')
                ->where('p.deleted = ?', 0)
                ->where('u.chap_id = ?', $chap_id)
                ->where('u.type = ?', 'CP')
                ->where('u.status = ?', 1);
                
        if(!is_null($appType) && !empty($appType))
        {
            if($appType == 'free')
            {
                $uploadSql->where('p.price = ?', 0);
            }
            else
            {
                $uploadSql->where('p.price > ?', 0);
            }            
        }

        if(!is_null($fromDate) && !is_null($toDate) &&!empty($fromDate) && !empty($toDate))
        {
            $uploadSql->where('DATE(p.created_date) >= ?', $fromDate)
                    ->where('DATE(p.created_date) <= ?', $toDate);
        }
        
        //Zend_Debug::dump($uploadSql->__toString());die();
        $uploadCount =  $this->fetchRow($uploadSql);
        
        return $uploadCount->upload_count;
    }
}
