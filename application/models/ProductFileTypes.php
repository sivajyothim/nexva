<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_ProductFileTypes extends Zend_Db_Table_Abstract {


    protected $_name = 'product_file_types';
    protected $_id   = 'id';


    function  __construct() {
        parent::__construct();
    }

    public function getFileTypeByPlatform($platformId) {
        $resultSet = $this->fetchAll(
                $this->select()
                ->where('platform_id = ?', $platformId)
                ->order('id ASC')
        );
        $entries   = '';
        foreach ($resultSet as $row) {
            $entries .= trim($row->extension) . ',';
        }
        return substr($entries, 0, -1);
    }

    public function getMimeByFile($filename) {
//        $fileType = 'cod';
        $fileType = end(explode(".", $filename));
        $rows = $this->fetchAll(
                'extension = "' . $fileType . '"'
        );
        if(isset ($rows->current()->mime))
            return $rows->current()->mime;
        else
            return FALSE;
    }
}
?>
