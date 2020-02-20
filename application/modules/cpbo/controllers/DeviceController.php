<?php
class Cpbo_DeviceController extends Nexva_Controller_Action_Cp_MasterController {
    
    function searchAction() {
        $deviceName = $this->_getParam('device', null);
        
        $devicesInfo    = array();
        if ($deviceName) {
            $deviceModel    = new Model_Device();
            if (Zend_Registry::get ( 'config' )->nexva->application->search->sphinx->enable) { 
                $sphinx = new Nexva_Util_Sphinx_Sphinx();
                $sphinx->limit = 200;
                $results = $sphinx->searchDevices($deviceName, ' SPH_MATCH_ANY' );
                if ($results !== false && isset($results["matches"])) {
                    $devices        = array();
                    foreach ( $results ["matches"] as $device ) {
                        //fetch each and every matched content 
                        $devices[] = $device['id'];
                    }
                    $devicesInfo = $deviceModel->findDevicesByIds($devices, 200);
                }
            } else {
                $devicesInfo  = $deviceModel->findDevicesByKeyword($deviceName, 200);        
            }
        }
        
        $this->view->devices    = $devicesInfo;
        $this->view->deviceName = $deviceName;
        
    }
}  