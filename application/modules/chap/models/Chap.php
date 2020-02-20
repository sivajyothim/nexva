<?php
/**
 *
 * @copyright   neXva.com
 * @author      chathura@nexva.com
 * @package     chap
 *
 */

class Chap_Model_Chap extends Zend_Db_Table_Abstract {

    protected $_name = "chaps";
    protected $_id  = "id";


    function __construct() {
        parent::__construct();
    }
    
    /**
     * 
     * update the last logged in date and time ...
     * @param int $id user id
     */
    
	function updateLastActivity($id)
    {
        $this->update(array(
        
        	"updated_at" =>  date( 'Y-m-d h:i:s' )
        
        	), "id = " . $id);
    }
    
    
    
}

?>
