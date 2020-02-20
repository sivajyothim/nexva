<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 3/9/14
 * Time: 9:08 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_CampaignStatistics extends Zend_Db_Table_Abstract {

    protected $_name = 'campaign_statistics';
    protected $_id = 'id';

    public function getCampaignStats($campaignId){
        $sql = $this->select();
        $sql    ->from(array('cs' => $this->_name),array('cs.*','count'=>'count(cs.campaign_id)'))
                ->setIntegrityCheck(false)
                ->join(array('c'=>'campaigns'),'c.id = cs.campaign_id',array('c.*','c.type AS campaign_type'))
                ->join(array('u'=>'users'),'u.id = c.chap_id')
                ->where('cs.campaign_id = ?',$campaignId)
                ->group('cs.campaign_id')
                ;
        //echo $sql->assemble();die();
        return $this->fetchRow($sql);
    }
}