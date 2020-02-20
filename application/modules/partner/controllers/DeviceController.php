<?php

class Partner_DeviceController extends Zend_Controller_Action {

    
     /**
     * 
     * Returns the set of devices based on the search key
     * 
     * @param q Keyword     
     * returns JSON encoded device results set
     */
    public function searchAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $devicesInfo = '';

        $results = array();
        $deviceName = $this->_request->getParam('q');
        $devices = array();
        //Zend_Debug::dump(Zend_Registry::get('config')->nexva->application->search->sphinx->enable);die();
        if ($deviceName != "") {

            $deviceModel = new Model_Device();
            if (Zend_Registry::get('config')->nexva->application->search->sphinx->enable) {

                //Use Sphinx
                $sphinx = new Nexva_Util_Sphinx_Sphinx ( );
                $results = $sphinx->searchDevices($deviceName, ' SPH_MATCH_ANY');

                if ($results === false) 
                {
                    //Zend_Debug::dump($results);die();
                } 
                else
                {

                    if ($results ['total'] != 0) {

                        if (is_array($results ["matches"])) {
                            foreach ($results ["matches"] as $device) 
                            {
                                $devices [] = $device['id'];
                            }
                        }
                    }
                }
            }
            else
            {
                $devicesInfo = $deviceModel->findDevicesByKeyword($deviceName, 12);
            }



            if (count($devices) == 0 and count($devicesInfo) == 0) {
                $results[0]['id'] = "error";
                $results[0]['img'] = '';
                $results[0]['phone'] = "Your phone make/model could not be found in our phone database.";
            } else {

                if (Zend_Registry::get('config')->nexva->application->search->sphinx->enable) {
                    $devicesInfo = $deviceModel->findDevicesByIds($devices, 12);
                }

                $i = 0;
                $results = '';

                foreach ($devicesInfo as $device) {
                    $device = $device->toArray();

                    $results[$i]['id'] = $device['device_id'];
                    $results[$i]['img'] = file_exists('./vendors/device_pix/' . $device['wurfl_actual_root_device'] . '.gif') ?
                            '/vendors/device_pix/' . $device['wurfl_actual_root_device'] . '.gif' :
                            '/partner/default/assets/img/unknown_phone_icon.png';

                    $results[$i]['phone'] = $device['brand'] . " " . $device['model'];

                    $i++;
                }
            }
        }

        echo json_encode($results);
		
		die();
    }
    
    
    public function setAction()
    {
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
      
        $deviceId = $this->_request->id;         
          
        $deviceModel = new Model_Device();
        
        $device = $deviceModel->fetchRow('id = '.$deviceId);
        
        if(is_null($device)) $this->_redirect($_SERVER['HTTP_REFERER']); //device not found? can only happen if URL is entered by hand.

        $session = new Zend_Session_Namespace("devices");

        /** Zend Sessions have a problem with arrays - so this is the workaround they suggest.
         *  @see: http://framework.zend.com/manual/en/zend.session.advanced_usage.html
         */
        
        $deviceDetails = array();
        $deviceDetails['id'] = $device['id'];
        $deviceDetails['phone'] = $device['brand']. " ". $device['model'];
        $deviceDetails['image'] = file_exists('./vendors/device_pix/'.$device['wurfl_actual_root_device'].'.gif') ?
                                '/vendors/device_pix/'.$device['wurfl_actual_root_device'].'.gif' :
                                '/partner/default/assets/img/unknown_phone_icon.png';
        
        $session->selectedDevices = $deviceDetails;

        isset($_SERVER['HTTP_REFERER']) ?
            $this->_redirect($_SERVER['HTTP_REFERER'])
        :
            $this->_redirect('/');
                   
    }

    public function removeAction() {

        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $session = new Zend_Session_Namespace("devices");

        /** Zend Sessions have a problem with arrays - so this is the workaround they suggest.
         *  @see: http://framework.zend.com/manual/en/zend.session.advanced_usage.html
         */

        $deviceDetails = $session->selectedDevices;

        $deviceDetails =null;
        
        $session->selectedDevices = $deviceDetails;

        isset($_SERVER['HTTP_REFERER']) ?
            $this->_redirect($_SERVER['HTTP_REFERER'])
        :
            $this->_redirect('/');
        

    }

}