<?php
class Nexva_GeoData_Ip2Country_Factory {
    
    static function getProvider($type = 'local') {
        $type       = ucfirst(strtolower($type));
        $className  = 'Nexva_GeoData_Ip2Country_' . $type;
        $instance   = new $className();
        return $instance;         
    }
}