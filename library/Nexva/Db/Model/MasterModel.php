<?php
/**
 * Base model that should be extended by other models 
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Jan 31, 2011
 */
class Nexva_Db_Model_MasterModel extends Zend_Db_Table_Abstract {
    
    public function getColumns() {
        return $this->info(Zend_Db_Table_Abstract::COLS);
    }

    /**
     * 
     * When you need to match up the object with some data (e.g. POST)
     * Pass in an empty array to just get the structure
     * @param associated array $data
     * @return array
     */
    public function getPopulatedObject($data = array()) {
        return (object) $this->getPopulatedArray($data);
    }
    
    /**
     * 
     * When you need to match up the object with some data (e.g. POST)
     * Pass in an empty array to just get the structure
     * @param associated array $data
     */
    public function getPopulatedArray($data = array()) {
        $columns    = $this->getColumns();
        
        $arr        = array();
        foreach ($columns as $column) {
            $arr[$column]   = isset($data[$column]) ? $data[$column] : null;
        }
        return $arr;
    } 
    
    
}