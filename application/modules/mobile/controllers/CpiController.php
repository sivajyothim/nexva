<?php

class Mobile_CpiController extends Nexva_Controller_Action_Mobile_MasterController  {

  public function init() {

  }

  public function urlAction() {
      
      $this->_helper->viewRenderer->setNoRender(true);
      $this->_helper->getHelper('layout')->disableLayout();
      
      if(!is_null($this->_request->chap_id) and !is_null($this->_request->app_id)) {
     
          
          $themeMeta = new Model_ThemeMeta();
          $chapInfo = $themeMeta->getThemeMetaForPartner($this->_request->chap_id);
          $chapInfo['WHITELABLE_URL_WEB'];
          
          
          $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
          $country  =   $geoData->getCountry($_SERVER['REMOTE_ADDR']);
          
          $statisticCpi = new Model_StatisticCpi();
          $statisticCpi->insert(array(
                  'ip' => $_SERVER['REMOTE_ADDR'],
                  'source' => $_SERVER['HTTP_REFERER'],
                  'iso' => $country['code'],
                  'user_agent' =>$_SERVER['HTTP_USER_AGENT'],
                  'url_redirect' => 'http://'.$chapInfo['WHITELABLE_URL_WEB'].'/'.$this->_request->app_id
                  )
                  );
          
          $this->_redirect('http://'.$chapInfo['WHITELABLE_URL_WEB'].'/'.$this->_request->app_id);
      }
      
      
  }
  
  public function urlnpAction() {
  
  	$this->_helper->viewRenderer->setNoRender(true);
  	$this->_helper->getHelper('layout')->disableLayout();
  
  	if(!is_null($this->_request->chap_id) and !is_null($this->_request->app_id)) {
  		 
  
  		$themeMeta = new Model_ThemeMeta();
  		$chapInfo = $themeMeta->getThemeMetaForPartner($this->_request->chap_id);
  		$chapInfo['WHITELABLE_URL_WEB'];
  
  
  		$geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
  		$country  =   $geoData->getCountry($_SERVER['REMOTE_ADDR']);
  
  		$statisticCpi = new Model_StatisticCpi();
  		$statisticCpi->insert(array(
  				'ip' => $_SERVER['REMOTE_ADDR'],
  				'source' => $_SERVER['HTTP_REFERER'],
  				'iso' => $country['code'],
  				'user_agent' =>$_SERVER['HTTP_USER_AGENT'],
  				'url_redirect' => 'http://'.$chapInfo['WHITELABLE_URL_WEB'].'/'.$this->_request->app_id
  		)
  		);
  
  		$this->_redirect('http://'.$chapInfo['WHITELABLE_URL_WEB'].'/'.$this->_request->app_id);
  	}
  
  
  }
  
  public function urlcampaignAction() {
  
  	$this->_helper->viewRenderer->setNoRender(true);
  	$this->_helper->getHelper('layout')->disableLayout();

  	    
  	
  	$session = new Zend_Session_Namespace("devices_nexva_mobile");
  	$isWireless = $session->is_mobile_device;
  	
  	if(!$isWireless and $session->is_check == false) {

  		$deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
  		$deviceInfo = $deviceDetection->getNexvaDeviceId("Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2 -H");
  		//If this is not a wireless device redirect to the main site
  		$isWireless = $deviceInfo->is_mobile_device;
  		$session->is_mobile_device = $isWireless;
  		$session->is_check = true;
  		
  	}
  	
  	
  	

  
  		$geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
  		$country  =   $geoData->getCountry($_SERVER['REMOTE_ADDR']);
  
  		$statisticCpi = new Model_StatisticCpi();
  		$statisticCpi->insert(array(
  				'ip' => $_SERVER['REMOTE_ADDR'],
  				'source' => $_SERVER['HTTP_REFERER'],
  				'iso' => $country['code'],
  				'user_agent' =>$_SERVER['HTTP_USER_AGENT'],
  				'url_redirect' => 'http://nexva.mobi/np/443039/cam/'.$this->_request->cam
  		)
  		);
  

  		$this->_redirect('http://nexva.mobi/np/443039');
  }

}





