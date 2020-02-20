<?php
/**
 * A factory to create and return device detection adapters. Configurable in application.ini
 *
 * @author jahufar
 */
class Nexva_DeviceDetection_Factory {

    public static function factory() {
        $detectionAdapter = "Nexva_DeviceDetection_Adapter_".Zend_Registry::get('config')->device->detection->adapter;        
        return call_user_func(array($detectionAdapter, 'getInstance'));               
    }
    
}
?>
