<?php
/**
 * Singleton class to get a Zend Queue object. Supports multiple queues
 *
 * @author jahufar
 */

class Nexva_Util_Queue_ZendQueue {

    /**
     * An array of Zend queue objects
     * 
     * @var Array
     */
    protected static $_queue = null;

    private function  __construct() {}
    private function  __clone() {}

    /**
     * Factory method to get instance of Zend Queue.
     *
     * @param string $name Name of the queue
     * @param string $queueAdapter The adapter to use. Defaults to 'Db' (Zend_Queue_Adapter_Db)
     * @return Zend_Queue
     */
    public static function getInstance($name, $queueAdapter = 'Db') {

        if( is_null(self::$_queue) ) {

            if( !isset(self::$_queue[$name])) {
                //init zend queue
                $config = Zend_Registry::get('config');
                $options = array(
                    'name' => $name,
                    'driverOptions' => array(
                        'host' => $config->resources->multidb->default->host,
                        'username' => $config->resources->multidb->default->username,
                        'password' => $config->resources->multidb->default->password,
                        'dbname' => $config->resources->multidb->default->dbname,
                        'type' => 'mysqli'
                    )
                );

                self::$_queue[$name] = new Zend_Queue($queueAdapter, $options);
            }
        }

        return self::$_queue[$name];
    }

}
?>
