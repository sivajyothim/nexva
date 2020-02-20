<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 10/14/13
 * Time: 6:42 PM
 * To change this template use File | Settings | File Templates.
 */
class Pbo_Model_StatisticsProducts extends Zend_Db_Table_Abstract
{
    protected $_name = 'statistics_products';
    protected $_id = 'id';

    /**
     * @param $end
     * @param $start
     * @return days between given date range
     */
    private function __getDaysInBetween($end, $start ) {

        // Vars
        $day = 86400; // Day in seconds
        $format = 'Y-m-d'; // Output format (see PHP date funciton)
        $sTime = strtotime($start); // Start as time
        $eTime = strtotime($end); // End as time
        $numDays = round(($eTime - $sTime) / $day) + 1;
        $days = array();

        // Get days
        for ($d = 0; $d < $numDays; $d++) {
            $days[] = date($format, ($sTime + ($d * $day)));
        }

        // Return days
        return $days;
    }

    /**
     * @param $chapId
     * @param null $firstDayThisMonth
     * @param null $lastDayThisMonth
     * @param $source
     * @return array of app views filtered by source (i.e; API,WEB,MOBILE WEB)
     */
    function getAppViewsBySource($chapId,$firstDayThisMonth=NULL, $lastDayThisMonth=NULL,$source)
    {
        if($firstDayThisMonth == NULL) $firstDayThisMonth = date('Y-m-01');
        if($lastDayThisMonth == NULL) $lastDayThisMonth = date('Y-m-t');

        $select     =   $this->select()
                        ->from(array('sp' => $this->_name), array("date"  => "sp.date", "count" => "count(sp.id)"))
                        ->where('sp.chap_id =?',$chapId)
                        ->where('sp.date >=?',$firstDayThisMonth)
                        ->where('sp.date <=?',$lastDayThisMonth)
                        ->where('sp.source =?',$source)
                        ->group("DATE_FORMAT(sp.date,'%Y-%m-%d')")
                        ->query()
                        ->fetchAll()
                        ;

        $resultViewsByDate = array();
        foreach ($select as $val) {
            $key = (string) (substr($val->date, 0, 10));
            $resultViewsByDate[$key] = $val->count;
        }

        $datesOfTheMonth = array();

        $dates = $this->__getDaysInBetween($lastDayThisMonth, $firstDayThisMonth);

        foreach ($dates as $day) {
            if (isset($resultViewsByDate[$day])) {
                $datesOfTheMonth[$day] = $resultViewsByDate[$day];
            } else {
                $datesOfTheMonth[$day] = 0;

            }
        }

        $array = array();


        // return date as timestamp * 1000 in Milliseconds
        foreach ($datesOfTheMonth as $dKey => $val) {
            $key = (string) (strtotime($dKey) * 1000);
            $array[$key] = $val;
        }

        return $array;
    }

    public function getViewedUsersByApp($chapId,$appId){
        $sql =  $this->select();
        $sql    ->from(array('sp' => $this->_name))
                ->setIntegrityCheck(false)
                ->join(array('u' => 'users'),'sp.user_id = u.id')
                ->where('sp.product_id = ?',$appId)
                ->where('sp.chap_id = ?',$chapId)
                ;

        return $this->fetchAll($sql);
    }

}