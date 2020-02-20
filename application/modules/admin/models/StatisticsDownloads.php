<?php
/**
 * Created by PhpStorm.
 * User: viraj
 * Date: 8/18/14
 * Time: 12:55 PM
 */

class Admin_Model_StatisticsDownloads extends Model_Language {

    protected $_name = 'statistics_downloads';
    protected $_id = 'id';

    /**
     * Returns sales values based on the params given
     * @param - $fromDate
     * @param - $toDate
     */
    public function getSalesValue( $fromDate=null, $toDate=null)
    {
        $downloadSql   = $this->select();
        $downloadSql->from(array('sd' => $this->_name),array('sum(p.price) as total_val','sd.*','count(sd.id) as download_count'))
                    ->setIntegrityCheck(false)
                    ->join(array('p' => 'products'), 'sd.product_id = p.id', array(''))
                    ->join(array('u' => 'users'), 'sd.chap_id = u.id')
                    //->where('sd.chap_id = ?', $chapId)
                    ->where('p.status = ?', 'APPROVED')
                    ->where('p.deleted = ?', 0)
                    ->where('p.product_type = ?', 'COMMERCIAL')
                    ->where('p.price > ?',0);

        if(!is_null($fromDate) && !is_null($toDate) && !empty($fromDate) && !empty($toDate))
        {
            $downloadSql->where('DATE(sd.date) >= ?', $fromDate)
                        ->where('DATE(sd.date) <= ?', $toDate);
        }
        elseif(!is_null($fromDate) && !empty($fromDate) && (is_null($toDate) || empty($toDate)))
        {
            $downloadSql->where('DATE(sd.date) >= ?', $fromDate);
        }
        elseif(!is_null($toDate) && !empty($toDate) && (is_null($fromDate) || empty($fromDate)))
        {
            $downloadSql->where('DATE(sd.date) <= ?', $toDate);
        }
        $downloadSql->group('sd.chap_id');

        //echo $downloadSql->assemble();die();
        return  $this->fetchAll($downloadSql);

        /*$downloadCount =  $this->fetchAll($downloadSql);

        if(is_null($downloadCount->total_val))
        {
            return '0.00';
        }
        else
        {
            return $downloadCount->total_val;
        }*/

    }
}