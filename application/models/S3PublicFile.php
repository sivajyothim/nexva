<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_S3PublicFile extends Zend_Db_Table_Abstract {
    protected $_name = 's3_public_files';
    protected $_id   = 'id';

    function  __construct() {
        parent::__construct();
    }

    public function save($data) {
        if(isset ($data['id']) && !empty ($data['id']))
            return $this->update($data, 'id = ' . $data['id']);
        else
            return $this->insert($data);
    }

    public function getIdByFilename($filename) {
        $row = $this->fetchRow(
                'filename = "'. $filename . '"'
        );
        if(isset ($row->id))
            return $row->id;
        else
            return false;
    }
}
?>
