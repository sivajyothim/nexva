<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/7/14
 * Time: 1:25 PM
 * To change this template use File | Settings | File Templates.
 */

class Admin_Model_ThemeMeta extends Zend_Db_Table_Abstract {

    protected  $_name = "theme_meta";
    protected  $_id  = "id";

    protected $_metaModel = "Admin_Model_ThemeMeta";
    protected $_entityIdColumn = "user_id";

    /**
     * The class name of the meta model
     *
     * @var string
     */
    //protected $_metaModel = null;

    /**
     * The name of entity id column.
     *
     * @var string
     */
    //protected $_entityIdColumn = '';

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

    function  __construct() {
        parent::__construct();
    }

    /**
     *
     * Returns all attributes assoicated with an object
     */
    public function getAll() {
        if( is_null($this->_entityId) ) throw new Zend_Exception('Entity Id not set.');

        $model      = new $this->_metaModel;
        $metadata   = $model->fetchAll($this->_entityIdColumn . ' = ' . $this->_entityId);
        $obj        = array();
        foreach ($metadata as $metadatum) {
            $obj[$metadatum['meta_name']]  = $metadatum['meta_value'];
        }
        $obj    = (object) $obj;
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

    }

    /**
     *
     * Reloads the cache for a given Entity.
     * The model is automatically changed according to the instance.
     * @param unknown_type $id entity id
     */
    public function reloadCache($id) {

        $model      = new $this->_metaModel;
        $metadata   = $model->fetchAll($this->_entityIdColumn . ' = ' . $id);
        $obj        = array();
        foreach ($metadata as $metadatum) {
            $obj[$metadatum['meta_name']]  = $metadatum['meta_value'];
        }
        $obj    = (object) $obj;
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

        //$this->reloadCache($id);
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