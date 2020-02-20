<?php

/**
 *
 * UserMeta model
 *
 * @author jahufar
 */
class Model_UserMeta extends Nexva_Db_EAV_EAVModel
{
    protected $_name    = 'user_meta';
    protected $_primary = 'id';

    protected $_metaModel = "Model_UserMeta";
    protected $_entityIdColumn = "user_id";
    
    //Returns uses telephone number
    public function getTelephone($userId)
    {
        $sql = $this->select();
        $sql->from($this->_name,array('meta_value'))                
                ->where('user_id = ?', $userId)
                ->where('meta_name = ?', 'TELEPHONE');
       
        
        $userTel =  $this->fetchRow($sql);
       //Zend_Debug::dump($userTel);die();
        
        if($userTel && count($userTel)>0 && !is_null($userTel))
        {
            return $userTel->meta_value;
        }
        else
        {
            $userTel = '-';
            return $userTel;
        }
        
    }
    public function getFirstName($userId)
    {
        $sql = $this->select();
        $sql->from($this->_name,array('meta_value'))                
                ->where('user_id = ?', $userId)
                ->where('meta_name = ?', 'FIRST_NAME');
       
        
        $userTel =  $this->fetchRow($sql);
       //Zend_Debug::dump($userTel);die();
        
        if($userTel && count($userTel)>0 && !is_null($userTel))
        {
            return $userTel->meta_value;
        }
        else
        {
            $userTel = '-';
            return $userTel;
        }
        
    }
    public function getLastName($userId)
    {
        $sql = $this->select();
        $sql->from($this->_name,array('meta_value'))                
                ->where('user_id = ?', $userId)
                ->where('meta_name = ?', 'LAST_NAME');
       
        
        $userTel =  $this->fetchRow($sql);
       //Zend_Debug::dump($userTel);die();
        
        if($userTel && count($userTel)>0 && !is_null($userTel))
        {
            return $userTel->meta_value;
        }
        else
        {
            $userTel = '-';
            return $userTel;
        }
        
    }

    public function getVerified($user_id)
    {
        $sql = $this->select();
        $sql    ->from($this->_name,array('meta_value','user_id'))
                ->where('user_id = ?', $user_id)
                ->where('meta_name = ?', 'VERIFIED_ACCOUNT');
                //->where('meta_name = ?', 'TELEPHONE');

        $userVerified =  $this->fetchRow($sql);

        if($userVerified && count($userVerified)>0 && !is_null($userVerified))
        {
            return $userVerified->meta_value;
        }
    }

    public function setVerified($user_id)
    {
        
        $varifies=$this->getVerified($user_id);
        $sql=FALSE;
        if($varifies==1){
            $sql = $this->update(array(
                'meta_name'=>'VERIFIED_ACCOUNT',
                'meta_value'=>'1',
            ),array(            
                'user_id'=>$user_id
            ));
        }else{
             $sql = $this->insert(array(
                'meta_name'=>'VERIFIED_ACCOUNT',
                'meta_value'=>'1',
                'user_id'=>$user_id
            ));
        }
        return $sql;
    }
    
}
?>
