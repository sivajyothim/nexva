<?php
/**
 * A simple helper to return the application's configuration object.
 *
 * @author jahufar
 */
class Nexva_View_Helper_Config extends Zend_View_Helper_Abstract {

    public function config() {
        return Zend_Registry::get('config');       
    }
   
}
?>
