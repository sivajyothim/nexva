<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_productDeviceAttributes extends Zend_Db_Table_Abstract {
    protected $_name    = 'product_device_saved_attributes';
    protected $_id      = 'id';

    public function  __construct() {
        parent::__construct();
    }

    public function getSelectedDeviceAttributesById($id) {
        $resultSet = $this->fetchAll(
                $this->select()
                ->where('build_id = ?', $id)
        );
        $entries = '';
        foreach ($resultSet as $row) {
            $entries[$row->device_attribute_definition_id] = $row->value;
        }
        return $entries;
    }

    public function save($data) {
        if (null === ($id = $data['id'])) {
            unset($data['id']);
            return $this->insert($data);
        }
        else {
            $this->update($data, array('id = ?' => $id));
            return false;
        }
    }
}
?>
