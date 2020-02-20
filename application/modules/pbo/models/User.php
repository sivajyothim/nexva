<?php
class Pbo_Model_User extends Zend_Db_Table_Abstract {

    protected $_name = 'users';
    protected $_id = 'id';

    
    //Get all users
    public function getAllRegUsers($chapId)
    {   
        $regUserSql   = $this->select();
        $regUserSql->from($this->_name,array('id','username', 'email', 'type','created_date'))
                        ->where('chap_id = ?', $chapId)
                        ->where('status = ?', 1)
                        ->order('id DESC');

         return $regUserSql;
    }

    //Get Registered Users
    public function getSerachedrRegUsers($chapId, $userSearchKey , $userType, $fromDate = null, $toDate = null)
    {
                 
        $db = $this->getAdapter();
            
        $showRegUsersSql   = $this->select();              
        $showRegUsersSql->from($this->_name,array('id','username', 'email', 'type','created_date','*'))

                    //->setIntegrityCheck(false)
                    //->join('user_meta','user_meta.user_id = users.id')

                    ->where('chap_id = ?', $chapId)
                    //->where('status = ?', 1)
                    ->order('users.id DESC');
        
        if(!empty($userSearchKey) && !is_null($userSearchKey) && $userSearchKey != 'all')
        {   
            $orCondition1 = $db->quoteInto('mobile_no LIKE ?', $userSearchKey.'%');
            $orCondition2 = $db->quoteInto('mobile_no LIKE ?', '%'.$userSearchKey.'%');
            
            $showRegUsersSql->where($orCondition1 .' OR '. $orCondition2);
                            //Below commented statemnt is same as above, and the above is the readable way
                            //->where('(' . $db->quoteInto('username LIKE ?', $userSearchKey.'%') . ' OR ' . $db->quoteInto('email LIKE ?', '%'.$userSearchKey.'%') . ')')
        }
        
        if(!empty($userType) && !is_null($userType))
        {
            if($userType != 'ALL')
            {
                $showRegUsersSql->where('type = ?', $userType);
            }
            else
            {
                $showRegUsersSql->where('(' . $db->quoteInto('type = ?', 'CP') . ' OR ' . $db->quoteInto('type = ?', 'USER'). ')');
            }
        }
        else
        {
            $showRegUsersSql->where('type = ?','CP');
        }
        
        if((!empty($fromDate) && !is_null($fromDate) && $fromDate != 'all') && (!empty($toDate) && !is_null($toDate) && $toDate != 'all'))
        {
            $showRegUsersSql->where('DATE(created_date) >= ?', $fromDate)
                            ->where('DATE(created_date) <= ?', $toDate);
        }
        elseif(!empty($fromDate) && !is_null($fromDate)  && $fromDate != 'all')
        {
            $showRegUsersSql->where('DATE(created_date) >= ?', $fromDate);
        }
        elseif(!empty($toDate) && !is_null($toDate) && $toDate != 'all')
        {
            $showRegUsersSql->where('DATE(created_date) <= ?', $toDate);
        }
            //$showRegUsersSql->group('users.id');
        return $showRegUsersSql;
    }

    //duplicate function for getSerachedrRegUsers, this fn returns queried result instead of sql query
    public function getSerachedrRegUsersForReport($chapId, $userSearchKey , $userType, $fromDate = null, $toDate = null)
    {

        $db = $this->getAdapter();

        $showRegUsersSql   = $this->select();
        $showRegUsersSql->from($this->_name,array('id','username', 'email', 'type','created_date','*'))

            //->setIntegrityCheck(false)
            //->join('user_meta','user_meta.user_id = users.id')

            ->where('chap_id = ?', $chapId)
            //->where('status = ?', 1)
            ->order('users.id DESC');

        if(!empty($userSearchKey) && !is_null($userSearchKey) && $userSearchKey != 'all')
        {
            $orCondition1 = $db->quoteInto('username LIKE ?', $userSearchKey.'%');
            $orCondition2 = $db->quoteInto('email LIKE ?', '%'.$userSearchKey.'%');

            $showRegUsersSql->where($orCondition1 .' OR '. $orCondition2);
            //Below commented statemnt is same as above, and the above is the readable way
            //->where('(' . $db->quoteInto('username LIKE ?', $userSearchKey.'%') . ' OR ' . $db->quoteInto('email LIKE ?', '%'.$userSearchKey.'%') . ')')
        }

        if(!empty($userType) && !is_null($userType))
        {
            if($userType != 'ALL')
            {
                $showRegUsersSql->where('type = ?', $userType);
            }
            else
            {
                $showRegUsersSql->where('(' . $db->quoteInto('type = ?', 'CP') . ' OR ' . $db->quoteInto('type = ?', 'USER'). ')');
            }
        }
        else
        {
            $showRegUsersSql->where('(' . $db->quoteInto('type = ?', 'CP') . ' OR ' . $db->quoteInto('type = ?', 'USER'). ')');
        }

        if((!empty($fromDate) && !is_null($fromDate) && $fromDate != 'all') && (!empty($toDate) && !is_null($toDate) && $toDate != 'all'))
        {
            $showRegUsersSql->where('DATE(created_date) >= ?', $fromDate)
                ->where('DATE(created_date) <= ?', $toDate);
        }
        elseif(!empty($fromDate) && !is_null($fromDate)  && $fromDate != 'all')
        {
            $showRegUsersSql->where('DATE(created_date) >= ?', $fromDate);
        }
        elseif(!empty($toDate) && !is_null($toDate) && $toDate != 'all')
        {
            $showRegUsersSql->where('DATE(created_date) <= ?', $toDate);
        }
        //$showRegUsersSql->group('users.id');
        return $this->fetchAll($showRegUsersSql);
    }
    
    
    /**
     * Returns user count
     * @param - $chapId
     * @param - $userType
     * @param - $fromDate
     * @param - $toDate    
     */
    public function getUserCount($chapId, $userType=null ,$fromDate=null, $toDate=null)
    {   
        $userSql   = $this->select();
        $userSql->from($this->_name,array('count(id) as user_count'))
                        ->where('chap_id = ?', $chapId)
                        ->where('status = ?', 1);
                
        if(!is_null($userType) && !empty($userType))
        {
            $userSql->where('type = ?', $userType);
        }

        if(!is_null($fromDate) && !is_null($toDate) &&!empty($fromDate) && !empty($toDate))
        {
            $userSql->where('DATE(created_date) >= ?', $fromDate)
                    ->where('DATE(created_date) <= ?', $toDate);
        }
        
        $userCount =  $this->fetchRow($userSql);
        
        return $userCount->user_count;
    }
    
       
    public function getAllTelcos($chapId)
    {             
        $regUserSql   = $this->select();
        $regUserSql->from($this->_name,array('id','username', 'email', 'created_date'))
                    ->where('status != ?', 0)
                    ->where("type = 'CHAP'")
                    ->where('chap_id = ?', $chapId)
                    ->order('created_date DESC');

        return $this->fetchAll($regUserSql);
    }
    
    public function getTelco($telcoId)
    {             
        $regUserSql   = $this->select();
        $regUserSql->from($this->_name,array('id','username', 'email', 'created_date'))
                    ->where('status != ?', 0)
                    ->where('id = ?', $telcoId);

        return $this->fetchAll($regUserSql);

    }
    
    public function getPayoutDetailsByUser($chapId)
    {
        $selectSql   = $this->select();
        $selectSql->from(array('u' => $this->_name),array(''))
                ->setIntegrityCheck(false)
                ->join(array('p' => 'payouts'), 'u.payout_id = p.id', array('payout_cp', 'payout_nexva', 'payout_chap', 'payout_super_chap'))                 
                ->where('u.id = ?', $chapId);
        
        return $this->fetchRow($selectSql);
        
    }

    /**
     * @return username
     * @param - $user
     */
    function getUserNameByUserID($user)
    {
        $select_sql = $this ->select()
                            ->from(array('u' => $this->_name),array('u.username'))
                            ->where('u.id ='.$user);

        $user = $this->fetchAll($select_sql);
        return $user->toArray();
    }

    
    /*
     * This function will call when ajax request (auto complete) comes from pbo filter app section
     */
    public function getRegUserEmails($chapId, $userSearchKey , $userType, $fromDate = null, $toDate = null)
    {
                 
        $db = $this->getAdapter();
            
        $showRegUsersSql   = $this->select();              
        $showRegUsersSql->from($this->_name,array('email'))
                        ->where('chap_id = ?', $chapId)
                        ->order('users.id DESC')
                        ->limit(10,0);
        
        if(!empty($userSearchKey) && !is_null($userSearchKey) && $userSearchKey != 'all')
        {   
            $orCondition1 = $db->quoteInto('username LIKE ?', $userSearchKey.'%');
            $orCondition2 = $db->quoteInto('email LIKE ?', '%'.$userSearchKey.'%');
            
            $showRegUsersSql->where($orCondition1 .' OR '. $orCondition2);
        }
        
        if(!empty($userType) && !is_null($userType))
        {
            if($userType != 'ALL')
            {
                $showRegUsersSql->where('type = ?', $userType);
            }
            else
            {
                $showRegUsersSql->where('(' . $db->quoteInto('type = ?', 'CP') . ' OR ' . $db->quoteInto('type = ?', 'USER'). ')');
            }
        }
        else
        {
            $showRegUsersSql->where('(' . $db->quoteInto('type = ?', 'CP') . ' OR ' . $db->quoteInto('type = ?', 'USER'). ')');
        }
        
        if((!empty($fromDate) && !is_null($fromDate) && $fromDate != 'all') && (!empty($toDate) && !is_null($toDate) && $toDate != 'all'))
        {
            $showRegUsersSql->where('DATE(created_date) >= ?', $fromDate)
                            ->where('DATE(created_date) <= ?', $toDate);
        }
        elseif(!empty($fromDate) && !is_null($fromDate)  && $fromDate != 'all')
        {
            $showRegUsersSql->where('DATE(created_date) >= ?', $fromDate);
        }
        elseif(!empty($toDate) && !is_null($toDate) && $toDate != 'all')
        {
            $showRegUsersSql->where('DATE(created_date) <= ?', $toDate);
        }
        
        //echo $productSql->assemble();
        $results =$this->fetchAll($showRegUsersSql);
        
        if(count($results) > 0){
            return $results->toArray();
        }
        else{
            return array();
        }
    }
    
    /*
     * This function will call when ajax request (auto complete) comes from pbo filter app section
     */
    public function getRegUserMobile($chapId, $userSearchKey , $userType, $fromDate = null, $toDate = null)
    {
                 
        $db = $this->getAdapter();
            
        $showRegUsersSql   = $this->select();              
        $showRegUsersSql->from($this->_name,array('mobile_no'))
                        ->where('chap_id = ?', $chapId)
                        ->order('users.id DESC')
                        ->limit(10,0);
        
        if(!empty($userSearchKey) && !is_null($userSearchKey) && $userSearchKey != 'all')
        {   
            $orCondition1 = $db->quoteInto('mobile_no LIKE ?', $userSearchKey.'%');
            $orCondition2 = $db->quoteInto('mobile_no LIKE ?', '%'.$userSearchKey.'%');
            
            $showRegUsersSql->where($orCondition1 .' OR '. $orCondition2);
        }
        
        if(!empty($userType) && !is_null($userType))
        {
            if($userType != 'ALL')
            {
                $showRegUsersSql->where('type = ?', $userType);
            }
            else
            {
                $showRegUsersSql->where('(' . $db->quoteInto('type = ?', 'CP') . ' OR ' . $db->quoteInto('type = ?', 'USER'). ')');
            }
        }
        else
        {
            $showRegUsersSql->where('(' . $db->quoteInto('type = ?', 'CP') . ' OR ' . $db->quoteInto('type = ?', 'USER'). ')');
        }
        
        if((!empty($fromDate) && !is_null($fromDate) && $fromDate != 'all') && (!empty($toDate) && !is_null($toDate) && $toDate != 'all'))
        {
            $showRegUsersSql->where('DATE(created_date) >= ?', $fromDate)
                            ->where('DATE(created_date) <= ?', $toDate);
        }
        elseif(!empty($fromDate) && !is_null($fromDate)  && $fromDate != 'all')
        {
            $showRegUsersSql->where('DATE(created_date) >= ?', $fromDate);
        }
        elseif(!empty($toDate) && !is_null($toDate) && $toDate != 'all')
        {
            $showRegUsersSql->where('DATE(created_date) <= ?', $toDate);
        }
        
        //echo $productSql->assemble();
        $results =$this->fetchAll($showRegUsersSql);
        
        if(count($results) > 0){
            return $results->toArray();
        }
        else{
            return array();
        }
    }

    /**
     * @param $chapId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getAllUsersForCampaign($chapId){
        $sql    = $this->select();
        $sql    ->from($this->_name)
                ->where('chap_id = ?', $chapId)
                ->where('type = ?', 'USER')
                ->where('status = ?', 1)
                ->order('id DESC')
                ;
        return $this->fetchAll($sql);
    }

    /**
     * @param $chapId
     * @param $signUpDate
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getUserBySignUpDate($chapId,$signUpDate){
        $sql    = $this->select();
        $sql    ->from($this->_name)
                ->where('chap_id = ?', $chapId)
                ->where('type = ?', 'USER')
                ->where('status = ?', 1)
                ->where('created_date <= ?', $signUpDate)
                ->order('id ASC')
                ;
        return $this->fetchAll($sql);
    }

    public function getUserByDownloadedPlatform($chapId,$platform){

        $sql    = $this->select();
        $sql    ->from(array('u'=>$this->_name))
                ->setIntegrityCheck(false)
                ->join(array('ud' => 'user_downloads'), 'ud.user_id = u.id')
                ->join(array('pb' => 'product_builds'), 'ud.build_id = pb.id')
                ->where('pb.platform_id = ?',$platform)
                ->where('ud.chap_id = ?',$chapId)
                ;
        return $this->fetchAll($sql);
    }

    public function getUserByDownloadedCategory($chapId,$category){
        $sql    = $this->select();
        $sql    ->from(array('u'=>$this->_name))
                ->setIntegrityCheck(false)
                ->join(array('sd'=>'statistics_downloads'),'sd.user_id = u.id')
                ->join(array('pc'=>'product_categories'),'pc.product_id = sd.product_id')
                ->where('sd.chap_id = ?',$chapId)
                ->where('pc.category_id = ?',$category)
                ;
        return $this->fetchAll($sql);
    }
}

?>
