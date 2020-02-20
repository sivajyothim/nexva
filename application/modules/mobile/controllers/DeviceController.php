<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Mobile
 * @version     $Id$
 */
class Mobile_DeviceController extends Nexva_Controller_Action_Mobile_MasterController {

  public function init() {
    parent::init();
//        $product = new Model_Product();
//         $this->_appCount = $product->getAppCountByDeviceId($this->getDeviceId());
    // Header Msgs
  }

  public function preDispatch() {
//        parent::preDispatch();
  }

  public function indexAction() {
    // Unset session objects
    $deviceSession = new Zend_Session_Namespace('Device');
    unset($deviceSession->deviceId);
    unset($deviceSession->deviceName);
    // remove cookie
    setcookie('device_id', NULL, time() - 3600, '/');
    setcookie('device_name', NULL, time() - 3600, '/');


    $this->view->showUtility = false;
    // Page Title
    $this->view->pageTitle = 'Search Device';
    // show page title
    $this->view->showPageTitle = false;
    $this->view->messages = array(
      'Search your device here.'
    );
    // Search Value
    $keyword = '';
    $devices = '';
    $devicesinfo = '';
    if (isset($this->_request->q))
      $keyword = $this->_request->q;
    $this->view->keyword = $keyword;
    if (isset($keyword) && !empty($keyword)) {
      $this->view->searchValue = $keyword;
      
      
      
    if (Zend_Registry::get ( 'config' )->nexva->application->search->sphinx->enable) {
                

                 $sphinx = new Nexva_Util_Sphinx_Sphinx ( );

                 $results = $sphinx->searchDevices($keyword, ' SPH_MATCH_ANY' );
                    if ($results === false) {
                        // for debug
                        //print "Query failed: " . $cl->GetLastError () . ".\n";
                    

                    } else {
                        
                        if ($results ['total'] != 0) {

                            if (is_array ( $results ["matches"] )) {
                                foreach ( $results ["matches"] as $device ) {
                                    // for debug 
                                    //print "$n. doc_id=$docinfo[id], weight=$docinfo[weight]";
                                    

                                    //fetch each and every matched content 
                                    $devicesinfo [] = $device['id'];
                                
                                }
                            }
                        
                        }
                    }
           
            }
            else {
                  $devices = $this->searchDevices($keyword);
               
            }
			
			if (Zend_Registry::get ( 'config' )->nexva->application->search->sphinx->enable and count ( $devicesinfo ) != 0) {
				$deviceModel = new Model_Device ( );
				$devices = $deviceModel->findDevicesByIds ( $devicesinfo, 12 );
				
				$i = 0;
				$results = array();
				
				foreach ( $devices as $device ) {
					$device = $device->toArray ();
					
					$results [$i] ['id'] = $device ['device_id'];
					$results [$i] ['img'] = file_exists ( './vendors/device_pix/' . $device ['wurfl_actual_root_device'] . '.gif' ) ? '/vendors/device_pix/' . $device ['wurfl_actual_root_device'] . '.gif' : '/web/images/unknown_phone_icon.png';
					
					$results [$i] ['phone'] = $device ['brand'] . " " . $device ['model'];
					
					$i ++;
				}
				
				
				$this->view->devices = ($this->enablePagenator ( $results ));
			
			}
			else
			    $this->view->devices = ($this->enablePagenator ( $devices ));
			 
      
       $controllerName = $this->getRequest()->getControllerName();
       $actionName = $this->getRequest()->getActionName();
       $this->view->keyword = $keyword;
       $this->view->baseUrlfo = $_SERVER ['SERVER_NAME'] . '/' . $controllerName . '/' . $actionName;
      
      
    }
  }

    public function selectAction() {
        if (isset($this->_request->id) && !empty($this->_request->id)) {
            $deviceSession = new Zend_Session_Namespace('Device');
            $deviceSession->unsetAll();
            // remove cookie
            setcookie('device_id', NULL, time() - 3600, '/');
            setcookie('device_name', NULL, time() - 3600, '/');  
            
            $deviceId     = $this->_request->id;
            $this->_setDevice($deviceId);
            
            // set cookie
            setcookie('device_id', $deviceId, time() + (3600 * 12 * 36), '/');
            setcookie('device_name', $deviceName, time() + (3600 * 12 * 36), '/');
            $deviceSession = new Zend_Session_Namespace('Device');
            $deviceSession->deviceManuallySelected    = true;
        }
        
        $requestUri = new Zend_Session_Namespace('RequestUri');
        $this->_redirect($requestUri->uri . '/#content');
    }
    
    /**
     * Given a device ID, the device is loaded into the session
     */
    private function _setDevice($deviceId) {
        $device         = new Model_Device();
        $deviceName     = $device->getDeviceNameById($deviceId);
        $attribs        = $device->getDeviceAttributes($deviceId);
        
        //loop through them to get their IDs
        $groupedAttrib  = array();
        foreach ($attribs as $attrib) {
            $groupedAttrib[$attrib->device_attribute_definition_id] = $attrib->value;
        }
        
        $deviceSession = new Zend_Session_Namespace('Device');
        //definition id 3 is os_version
        $deviceSession->osVersion   = isset($groupedAttrib[3]) ? $groupedAttrib[3] : null;
        $this->setDeviceName($deviceName);
        $this->setDeviceId($deviceId);
    }

  /**
   * Search devices
   */
  private function searchDevices($keyword) {
    $deviceMdl = new Model_Device();
    $resultSet = $deviceMdl->findDevicesByKeyword($keyword);
    $devicesAttributes = array();
    foreach ($resultSet as $device) {
      $deviceAttributes = array();
      $deviceAttributes = $deviceMdl->getDeviceAttributesById($device->device_id);
      $devicesAttributes = array_merge($devicesAttributes, $deviceAttributes);
    }
    return $devicesAttributes;
  }

}

?>
