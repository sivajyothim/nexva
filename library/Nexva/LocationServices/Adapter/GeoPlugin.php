<?php

class Nexva_LocationServices_Adapter_GeoPlugin implements Nexva_LocationServices_Interface_Interface {

  protected $_locationDetils;

  public function __construct($adapter = null) {
    $locationDetils = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $_SERVER['REMOTE_ADDR']));
    $this->_locationDetils = $locationDetils;
//    return $locationDetils;
  }

  public function getLocationDetails() {
    return $this->_locationDetils;
  }

  public function getCountry() {
    return $this->_locationDetils['geoplugin_countryName'];
  }

  public function getCountryCode() {
    return $this->_locationDetils['geoplugin_countryCode'];
  }
}

?>
