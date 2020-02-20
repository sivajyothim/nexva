<?php
/**
 *
 * @copyright   neXva.com
 * @package     Admin
 * @version     $Id$
 */


class Model_StatisticDevice extends Zend_Db_Table_Abstract{

    protected  $_id =   "id";
    protected  $_name = "statistics_devices";


    function  __construct() {
        parent::__construct();
    }



    function getDeviceStatisticsById($deviceId){

        $stats  =   $this->fetchAll("device_id = '$deviceId'")->toArray();
        return $stats;
       
    }

    public function updateDeviceStatistics($device_id){

        if(!isset($device_id)){
            throw new Exception("Device id must be provided");
            return false;
        }


      

        
            $data   = array(
                'id' =>NULL,
                'date'=>date('Y-m-d'),
                'device_id' => $device_id,
                'session_id' => session_id(),
                'ip'    => $_SERVER['REMOTE_ADDR']
            );
            $insert =   $this->insert($data);
            return true;
        
    }
}

?>

