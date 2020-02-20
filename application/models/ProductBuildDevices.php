<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_ProductBuildDevices extends Zend_Db_Table_Abstract {
    protected $_name = 'build_devices';
    protected $_id   = 'id';
    protected $_referenceMap    = array(
            'Model_ProductBuild' => array(
                            'columns'           => array('build_id'),
                            'refTableClass'     => 'Model_ProductBuild',
                            'refColumns'        => array('id')
            ),
            'Model_Device' => array(
                            'columns'           => array('device_id'),
                            'refTableClass'     => 'Model_Device',
                            'refColumns'        => array('id')
            ),
    );

    function  __construct() {
        parent::__construct();
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

    public function isDeviceExists($buildId, $deviceId) {
        $device = $this->fetchAll('build_id=' . $buildId . ' and ' . ' device_id=' . $deviceId);
        if($device->count() > 0) {
            return true;
        }
        return false;
    }
}
?>
