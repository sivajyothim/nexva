<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/28/13
 * Time: 6:24 PM
 * To change this template use File | Settings | File Templates.
 */

class Api_Model_UserDownloads extends Zend_Db_Table_Abstract
{
    protected $_name = 'user_downloads';

    function checkDownloadCapability($appId,$chapId,$userId,$buildId)
    {
        /*$sql = $this->select()
                    ->from(array('ud' => $this->_name),array('count(ud.id) AS downloads'))
                    ->where('ud.app_id = ?', $appId)
                    ->where('ud.chap_id = ?', $chapId)
                    ->where('ud.user_id = ?', $userId)
                    ->where('ud.build_id = ?', $buildId)
                    ->group('ud.user_id');*/

        $sql = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('ud' => $this->_name),array('count(ud.id) AS downloads'))
            ->columns('products.price AS AppPrice')
            ->join('products', 'ud.app_id = products.id' )
            ->where('ud.app_id = ?', $appId)
            ->where('ud.chap_id = ?', $chapId)
            ->where('ud.user_id = ?', $userId)
            ->where('ud.build_id = ?', $buildId)
            ->group('ud.user_id');

        $downloadCapability = $this->fetchAll($sql)->toArray();
        if(count($downloadCapability) == 0)
        {
            return true;
        }
        else
        {
            foreach($downloadCapability as $record)
            {
                if($record['AppPrice'] == '0')
                {
                    return true;
                }
                else
                {
                    if($record['downloads'] >= 5)
                    { 
                       return true;
                    }
                    else
                    {
                        return true;
                    }
                }
            }
        }
    }

    function addDownloadRecord($appId,$chapId,$userId,$buildId)
    {
        $recordData = array(
            'chap_id' => $chapId,
            'user_id' => $userId,
            'build_id' => $buildId,
            'app_id' => $appId
        );
        $this->insert($recordData);
    }
}