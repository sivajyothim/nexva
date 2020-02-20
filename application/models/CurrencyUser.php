<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 7/2/13
 * Time: 3:40 PM
 * To change this template use File | Settings | File Templates.
 */
class Model_CurrencyUser extends Zend_Db_Table_Abstract
{
    protected $_name = 'currency_users';
    protected $_id = 'id';

    /**
     * @param $id
     * @param $currency
     * @param $status
     * @return string
     */
    function addCurrencyUser($id,$currency,$status)
    {
        $sql = $this->select()
                    ->from('currency_users')
                    ->where('currency_users.user_id = ?',$id);
        $exist = $this->fetchAll($sql)->toArray();

        $date = new DateTime();
        $data = array(
            'currency_id'  => $currency,
            'status'    => $status,
            'date_added'=> date_format($date, 'Y-m-d H:i:s')
        );
        if($exist)
        {
            $this->update($data,'user_id ='.$id);
            return 'Currency Updated';
        }
        else
        {
            $data['user_id'] = $id;
            $this->insert($data);
            return 'Currency Inserted';
        }
    }

    /**
     * @param $chap_id
     * @return array
     */
    function getCurrencyUser($chap_id)
    {
        $sql = $this->select()
                    ->from('currency_users')
                    ->setIntegrityCheck(false)
                    ->columns(array('currencies.*'))
                    ->join('currencies','currencies.id = currency_users.currency_id')
                    ->where('user_id ='.$chap_id);
                    //->where('user_id = 7040');
        $currencyUser = $this->fetchAll($sql)->toArray();
    	return $currencyUser;
    }
    
    function getCurrencyUserRow($chap_id)
    {
    	$sql = $this->select()
    	->from('currency_users')
    	->setIntegrityCheck(false)
    	->columns(array('currencies.*'))
    	->join('currencies','currencies.id = currency_users.currency_id')
    	->where('user_id ='.$chap_id);
    	//->where('user_id = 7040');
       $result= $this->fetchRow($sql);
       $currencyUser=null;
        if(!empty($result)){
            $currencyUser = $result->toArray();
        }
    	return $currencyUser;
    }
}