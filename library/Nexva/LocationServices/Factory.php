<?php

//$Id$
/*
 * @file
 * Class file used to wrap the locaiton services functions.
 *
 */

class Nexva_LocationServices_Factory {

  private $__adapter;

  public function __construct($adapter = null) {
    $serviceAdapter = "Nexva_LocationServices_Adapter_" . Zend_Registry::get('config')->location->service->adapter;
    $adapter = $serviceAdapter;
    if (class_exists($adapter)) {
      $this->__adapter = new $adapter;
    } else {
      // no adapter class found
    }
  }

  public function getLocationDetails() {
    return $this->__adapter->getLocationDetails();
  }

  public function getCountry() {
    return $this->__adapter->getCountry();
  }

  public function getCountryCode() {
    return $this->__adapter->getCountryCode();
  }

}