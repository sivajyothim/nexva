<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class Model_PageLanguages extends Nexva_Db_Model_MasterModel {

    protected   $_id = 'id';
    protected   $_name = 'page_languages';

    public function __construct(){

        parent::__construct();
    }

    /**
     * 
     * Looks up a page by its slug
     * @param $slug
     * @param $lang_id
     */
    public function getPageBySlug($slug = false, $langId = 1) {
        return $this->getPageByColumn('pages.slug', $slug, $langId);
    }
    
    /**
     * 
     * Returns the page when you give it the ID of the page.  
     * @param $pageId
     * @param $lang_id
     */
    public function getPageById($pageId = false, $langId = 1) {
        return $this->getPageByColumn('pages.id', $pageId, $langId);
    }

    /**
     * Returns the page when you have a pageLanguage id 
     * Enter description here ...
     * @param $pageLangId
     * @param $langId
     */
    public function getPageByPageLanguageId($pageLangId = false, $langId = 1) {
        return $this->getPageByColumn('page_languages.id', $pageLangId, $langId);
    }
    
    /**
     * Returns the page when you have a page title 
     * Enter description here ...
     * @param $pageTitle
     * 
     */
    public function getPageByPageTitle($pageTitle = false, $langId = 1) {
        return $this->getPageByColumn('page_languages.title', $pageTitle, $langId);
    }
    
    /**
     * 
     * Common function to get the page by a column name 
     * @param unknown_type $columnName
     * @param unknown_type $columnValue
     * @param unknown_type $langId
     */
    private function getPageByColumn($columnName = false, $columnValue = false, $langId = 1) {
        if (!($columnName && $columnValue)) {
            return null;
        }
        
        $pageLanguage   = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);  
        $pageLanguage
            ->setIntegrityCheck(false)
            ->joinLeft('pages', 'pages.id = page_languages.page_id', array('id AS pid', 'slug', 'status'))
            ->where($columnName . ' = ?', $columnValue)
            ->where('page_languages.language_id = ?', $langId)
            ->limit(1);
        
        
    //    Zend_Debug::dump($pageLanguage->assemble());
      //  die();
        $results    = $this->fetchRow($pageLanguage);
        if ($results != null) {
            return $results->toArray();
        } 
        return null;
    }
}

?>
