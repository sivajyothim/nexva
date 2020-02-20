<?php

/**
 * BuildDevices Model
 *
 * @author jahufar
 */
class Model_BuildDevices extends Zend_Db_Table_Abstract {
    protected $_id = 'id';
    protected $_name = 'build_devices';

    function __construct() {
        parent::__construct();
    }

    
}
?>
