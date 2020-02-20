<?php

/**
 *
 * @copyright   neXva.com
 * @author      chathura <heshan at nexva dot com>
 * @package     Web/Mobile
 * @version     $Id$
 */

class Model_Language extends Nexva_Db_Model_MasterModel {

    protected  $_id = 'id';
    protected  $_name='languages';

    function  __construct() {
        parent::__construct();
        $db     = Zend_Registry::get('db');
        $db->query('SET NAMES "utf8"')->execute();
    }
	
	/**
	 * Get language detials 
	 * @param int $enabled = 1, if the language needed enabled from table
	 * @return Obj Language Rowset 
	 * Chathura
	 */
	
	public function getLanguageList($enabled = null) {
        if (! is_null($enabled)) {
            $resultSet = $this->fetchAll('status = ' . $enabled, ' FIELD(languages.id, 1) DESC, languages.common_name ASC, languages.name ASC');
        } else {
            $resultSet = $this->fetchAll(array(), 'FIELD(languages.id, 1) DESC, languages.common_name ASC, languages.name ASC');
        }
			
	
			
		return $resultSet;
	}
	
	/**
	 * Get the language based on enterd language code {'en','fr' }
	 * @param str $code language code 
	 * @return Obj language rowset
	 * Chathura
	 */
	
	public function getLanguageIdByCode($code) {
		   $languageRow  = $this->fetchRow($this->select()->where("code=?",$code));
		   return $languageRow;
	}
	
	
	/**
	 * Get the language based on enterd language id
	 * @param str $id language id
	 * @return Obj language rowset
	 * Chathura
	 */
	
	public function getLanguageByid($id) {
		   $languageRowset = $this->find($id)->current();
		   return  $languageRowset;
	}

	public function getDefaultLanguage() {
	    $cache      = Zend_Registry::get('cache');
	    $key        = 'DEFAULT_LANGUAGE';
	    if (($lang = $cache->get($key)) !== false) {
	        return $lang;
	        
	    }
	    
	    $stmnt = $this->select();
	    $stmnt->from('languages', array('*'))
	       ->where('status = 1')
	       ->limit(1);
	    $data  = $this->fetchAll($stmnt);
	    if ($data) {
	        $row   = $data->toArray();
	        if (!empty($row[0])){
	           return (object) $row[0];    
	        }
	    }
	    $std       = new stdClass();
	    $std->id   = 1;
	    $std->code = 'en';
	    
	    $cache->set($lang, $key);
	    return $std;
	}
   
}

?>
