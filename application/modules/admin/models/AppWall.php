<?php

class Admin_Model_AppWall extends Zend_Db_Table_Abstract
{
    protected $_name = 'app_wall';
    protected $_id = 'id';
    
    public function getAppWallChaps($startDate,$endDate){
        return $this->select()
                    	->from(array('aw' => $this->_name), array("chap_id" => "aw.chap_id"))
                        ->setIntegrityCheck(false)
                    	->join(array('tm' => 'theme_meta'), 'tm.user_id = aw.chap_id', array('tm.meta_value'))
                    	->where("tm.meta_name = 'WHITELABLE_SITE_NAME'")
                    	->where("aw.date between '$startDate' and '$endDate'")
                        ->group(array("aw.chap_id"))
                    	->query()
                    	->fetchAll();
    }
    
    public function getAppWall($chapId, $startDate, $endDate) {
        $select = $this->select()
                ->from(array('aw' => $this->_name), array("count" => "count(aw.id)", 'date' => "(DATE_FORMAT(aw.date,'%Y-%m-%d'))"))
                ->where("aw.chap_id=" . $chapId)
                ->group(array("aw.chap_id", "DATE_FORMAT(aw.date,'%Y-%m-%d')"))
                ->where("aw.date between '$startDate' and '$endDate'")
                ->query()
                ->fetchAll();
        $appWallVisit = array();
        foreach ($select as $row) {
            $appWallVisit[$row->date] = $row->count;
        }


        if ($startDate == null) {
            $noOfDaysInTheMonth = date('t');
            $yearAndMonth = date('Y-m-');
            $datesOfTheMonth = array();
        } else {

            list($year, $month, $day) = explode('-', $startDate);
            $noOfDaysInTheMonth = date('t', mktime(1, 1, 1, $month, $day, $year));
            $yearAndMonth = date('Y-m-', mktime(1, 1, 1, $month, $day, $year));
            $datesOfTheMonth = array();
        }
        $datesOfTheMonth = array();
        // generate the dates  calander month
        for ($day = 1; $day <= $noOfDaysInTheMonth; $day ++) {

            $datesOfTheMonthKey = (string) $yearAndMonth . sprintf("%02s", $day);

            if (isset($appWallVisit[$datesOfTheMonthKey])) {

                $datesOfTheMonth[$datesOfTheMonthKey] = $appWallVisit[$datesOfTheMonthKey];
            } else {

                $datesOfTheMonth[$datesOfTheMonthKey] = 0;
            }
        }

        $appWallVisitDetails = array();
        $days=0;
        foreach ($datesOfTheMonth as $dKey => $val) {
            $key = (string) (strtotime($dKey) * 1000);
            $appWallVisitDetails[$days]['count'] = $val;
            $appWallVisitDetails[$days++]['date'] = $key;
        }
        return $appWallVisitDetails;
    }
    
     public function getAppWallSummary($startDate, $endDate) {
        return $this->select()
                ->from(array('aw' => $this->_name), array("count" => "count(aw.id)", 'date' => "(DATE_FORMAT(aw.date,'%Y-%m-%d'))"))
                ->setIntegrityCheck(false)
                ->join(array('tm' => 'theme_meta'), 'tm.user_id = aw.chap_id', array('tm.meta_value'))
                ->where("tm.meta_name = 'WHITELABLE_SITE_NAME'")
                ->group(array("aw.chap_id"))
                ->where("aw.date between '$startDate' and '$endDate'")
                ->query()
                ->fetchAll();
     }

}