<?php

class Default_CpiController extends Nexva_Controller_Action_Web_MasterController {

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
  
  		$this->_redirect('http://nexva.mobi/cpi/urlcampaign/cam/'.$this->_request->cam);

  	
  }

}





