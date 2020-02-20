<?php
/**
 * A generic class to model the common EAV (Entity-Attribute-Value) DB pattern used extensively in v2
 *
 * @package Nexva
 * @see http://en.wikipedia.org/wiki/Entity-attribute-value_model
 * @author jahufar
 */

class Nexva_Db_EAV_EAVModel extends Zend_Db_Table_Abstract
{
    /**
     * The class name of the meta model
     *
     * @var string
     */
    protected $_metaModel = null;

    /**
     * The name of entity id column.
     * 
     * @var string
     */
    protected $_entityIdColumn = '';

    /**
     * A value that uniquely identifies an entity (e.g. user_id, product_id etc)
     *
     * @var int
     */
    protected $_entityId = null;

    /**
     * Sets the entity id value. Call this before you do any magic calls
     *
     * @param int $id
     */
    public function setEntityId($id)
    {
        $this->_entityId = $id;
    }


    /**
     * Get the entity id value.
     *
     *
     */
    public function getEntityId()
    {
        
            return $this->_entityId;
        
    }
    
    /**
     * 
     * Returns all attributes assoicated with an object
     */
    public function getAll() {
       if( is_null($this->_entityId) ) throw new Zend_Exception('Entity Id not set.');

        //check for complete entity model, otherwise load it into cache
        $cache  = Zend_Registry::get('cache');
        $key    = 'EAV_FULL_' . $this->_metaModel . '_' . trim($this->_entityId);
        $key    = str_ireplace('-', '_', $key);
        if (($obj = $cache->get($key)) === false) {
            $model      = new $this->_metaModel;
            $metadata   = $model->fetchAll($this->_entityIdColumn . ' = ' . $this->_entityId);
            $obj        = array();
            foreach ($metadata as $metadatum) {
                $obj[$metadatum['meta_name']]  = $metadatum['meta_value']; 
            }
            $obj    = (object) $obj;
            $cache->set($obj, $key);
        }
        return $obj;
    }
    
    /**
     * Retrieves a value for a specified attribute
     *
     * @param int $id
     * @param string $attributeName
     * @return string
     */
    public function getAttributeValue($id, $attributeName){
        $attributeName  = strtoupper($attributeName);   
        $this->_entityId    = $id;
        if( is_null($id) ) throw new Zend_Exception('Entity Id not set.');
        $obj    = $this->getAll();
        
        return (isset($obj->$attributeName)) ? $obj->$attributeName : '';

/*        
        $cache  = Zend_Registry::get('cache');
        $key    = 'EAV_' . $this->_metaModel . '_' . trim($id) . '_' . strtoupper(trim($attributeName));
        $key    = str_ireplace('-', '_', $key);
        
        if (($result = $cache->get($key)) === false) {
            $this->setEntityId($id);
            $model  = new $this->_metaModel;
            $entity = $model->fetchRow($this->_entityIdColumn.'='.$id." AND UPPER(meta_name) = '".strtoupper($attributeName)."'");
            $val    = '';
            if (!is_null($entity)) {
                $val    = $entity->meta_value;   
            }
            $cache->set($val, $key);
            return $val;    
        } else {
            return $result;
        }
*/        
    }

    /**
     * 
     * Reloads the cache for a given Entity. 
     * The model is automatically changed according to the instance.
     * @param unknown_type $id entity id
     */
    public function reloadCache($id) {
        $cache  = Zend_Registry::get('cache');
        $key    = 'EAV_FULL_' . $this->_metaModel . '_' . trim($id);
        $key    = str_ireplace('-', '_', $key);
        $model      = new $this->_metaModel;
        $metadata   = $model->fetchAll($this->_entityIdColumn . ' = ' . $id);
        $obj        = array();
        foreach ($metadata as $metadatum) {
            $obj[$metadatum['meta_name']]  = $metadatum['meta_value']; 
        }
        $obj    = (object) $obj;
        $cache->set($obj, $key);
    }
    
    /**
     * Saves a single attribute value by attribute name
     *
     * @param int $id
     * @param string $attributeName
     * @param string $attributeValue
     * @return mixed
     */
    public function setAttributeValue($id, $attributeName, $attributeValue)
    {  
        
       $model = new $this->_metaModel;

       $entity = $model->fetchRow($this->_entityIdColumn.'='.$id." AND UPPER(meta_name) = '".strtoupper($attributeName)."'");

       $data = array(
         $this->_entityIdColumn => $id,
         'meta_name'            => strtoupper($attributeName),
         'meta_value'           => $attributeValue
       );

       if( is_null($entity) ) //save new attribute                
           return $model->insert($data);                 
       else //update existing                
           $model->update($data, $this->_entityIdColumn.'='.$id." AND UPPER(meta_name) = '".strtoupper($attributeName)."'");      
       
        $this->reloadCache($id);
    }

    /**
     * Magical __get - make sure you call setEntityId() before making magic calls
     * 
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return $this->getAttributeValue($this->_entityId, $name);
    }

    /**
     * Magical __set - make sure you call setEntityId() before making magic calls
     *
     * @param string $name
     * @param string $value
     * @return null
     */
    public function __set($name, $value)
    {
        $this->setAttributeValue($this->_entityId, $name, $value);
    }
}
?>
