<?php
class Admin_Model_Language extends Model_Language {

    protected  $_id     = 'id';
    protected  $_name   = 'languages';

    public function  __construct() {
        parent::__construct();
    }

    /**
     *  @return available languages
     */

    function getAvailableLanguages()
    {
       $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from( 'languages AS l' ,array('l.id','l.name','l.common_name'))
                    ->where('l.status = ?', '1')
                    ;

        $languages = $this->fetchAll($sql)->toArray();
        return $languages;
    }
    
    /**
     *  @return language name by cat id
     */
    
    public function getLanguageNameById($langId) {
        $rowset = $this->find($langId);
        return $rowset->current()->name;
    }
}