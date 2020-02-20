<?php

/**
 * 
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package
 * @version     $Id$
 */
class Nexva_Notifier_NotifierSubject implements Nexva_Notifier_Interface_Subject {

  private $observers = array();
  private $watchdogMsg = NULL;
  private $severity = Zend_Log::INFO;

  function __construct() {

  }

  function attach(Nexva_Notifier_Interface_Observer $observer_in) {
    //could also use array_push($this->observers, $observer_in);
    $this->observers[] = $observer_in;
  }

  function detach(Nexva_Notifier_Interface_Observer $observer_in) {
    //$key = array_search($observer_in, $this->observers);
    foreach ($this->observers as $okey => $oval) {
      if ($oval == $observer_in) {
        unset($this->observers[$okey]);
      }
    }
  }

  function notify() {
    foreach ($this->observers as $obs) {
      $obs->notify($this);
    }
  }

  function notifyWatchdog($newWatchdogMsg, $severity) {
    $this->watchdogMsg = $newWatchdogMsg;
    $this->severity = $severity;
    $this->notify();
  }

  function updateProduct(){
    
  }

  function getMsg() {
    return $this->watchdogMsg;
  }

  function getSeverity() {
    return $this->severity;
  }

}

?>
