<?php
/**
 * 
 * A utility class for logging that uses Mongo as the store
 * @author John
 *
 */
class Nexva_Util_Log_Mongo {
    
    private $conn           = null;
    private $collection     = null;
    
    public function __construct($collection = null) {
        $host       = Zend_Registry::get('config')->mongo->host;
        $port       = Zend_Registry::get('config')->mongo->port;
        $dbname     = Zend_Registry::get('config')->mongo->log->dbname;
        
        try {
            $mongo      = new Mongo("mongodb://{$host}:{$port}", array("persist" => "nexva_loggin"));
            $this->conn = $mongo->selectDB($dbname);
            if (!$this->conn) {
                throw new Exception("Couldn't connect to mongodb, the connection attempt failed");
            }
            $this->collection = $this->conn->selectCollection($collection);
        } catch (Exception $ex) {
            $line       = "\n\n -------------------MONGO LOGGER ERROR - Couldn't connect---------------------------------- \n\n ";
            $message    = $line . ((string) $ex) . "\n" . print_r($ex, true);
            Zend_Registry::get('logger')->err($message);
            return false;
        }
    }
    
    public function setCollection($collection = null) {
        $this->collection = $this->conn->selectCollection($collection);
    }
    
    public function log($data = array()) {
        try {
            //add date and server info
            $time   = time();
            $data['_time']  = array(
                'timestamp' => $time,
                'date'      => date("d", $time),
                'month'     => date("m", $time),
                'year'      => date("Y", $time),
            );
            
            
            $request     = new Zend_Controller_Request_Http();
            $data['ip']     = $request->getClientIp(true);
            $data['_session']  = @session_id();
            $data['_vars']  = print_r($_SERVER, true); 
            
            $this->collection->insert($data);
        } catch(Exception $ex) {
            $line       = "\n\n -------------------MONGO LOGGER ERROR---------------------------------- \n\n ";
            $message    = $line . ((string) $ex) . "\n" . print_r($ex, true);
            Zend_Registry::get('logger')->err($message);
            return false;
        }     
    } 
} 