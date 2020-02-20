<?php

/**
 * A helper to help access the request object from a view.
 * Useful when re-populating posted form values back into the inputs etc.
 *
 * @author jahufar
 * @version $id$
 *
 */
class Nexva_View_Helper_Request extends Zend_View_Helper_Abstract  {

    public function request() {
        return Zend_Controller_Front::getInstance()->getRequest();
    }
    
}
?>
