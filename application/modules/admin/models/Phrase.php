<?php
class Admin_Model_Phrase extends Nexva_Db_Model_MasterModel {
        
    protected  $_id     = 'id';
    protected  $_name   = 'phrases';

    function __construct() {
        parent::__construct();
        $db     = Zend_Registry::get('db');
        $db->query('SET NAMES "utf8"')->execute();
    }
    
    function getTranslations($phraseId) {
        $translations   = $this->select(true)->setIntegrityCheck(false);
        $fields = array('phrase_langs.id AS pl_id', 'phrase_id AS p_id', 'language_id', 'value', 'updated');
        $translations   ->joinLeft('phrase_langs', 'phrases.id = phrase_langs.phrase_id', $fields)
                        ->joinLeft('languages', 'phrase_langs.language_id = languages.id', array('languages.name AS lang_name'))
                        ->where('phrase_id = ' . $phraseId)
                        ->query();
        $results    = $this->fetchAll($translations)->toArray();
        return $results;        
    }
    
    function addTranslation($data, $id = null) {
        //clear the cache
        $query      = $this->select()
            ->setIntegrityCheck(false)
            ->from('phrases', array())
            ->joinLeft('sections', 'phrases.section_id = sections.id', 'code')
            ->where('phrases.id = ' . $data['phrase_id']);
            
        $row        = $this->fetchRow($query)->toArray();    
        $cacheModel = new Model_CacheUtil();
        $cacheModel->clearPhrases($row['code'], $data['language_id']);
        
        
        $phraseLangModel    = new Admin_Model_PhraseLang();
        if ($id) {
            unset($data['phrase_id']); //we really don't want them to change these. 
            unset($data['language_id']);
            $phraseLangModel->update($data, 'id = ' . $id);
        } else {
            $row    = $phraseLangModel->fetchRow("phrase_id = {$data['phrase_id']} AND language_id = {$data['language_id']}");
            if (!empty($row)) {
                $row    = $row->toArray();
            }
            if (empty($row)) {
                $phraseLangModel->insert($data);    
            }
        }
    }
    
    function getPhraseList($sectionId, $langId) {
        $select     = $this->select(true)->setIntegrityCheck(false);
        $select ->joinLeft('phrase_langs', 'phrases.id = phrase_id AND language_id = ' . $langId, array('phrase_langs.value', 'phrase_langs.updated'))
                ->where('section_id = ' . $sectionId)
                ->order('name ASC')
                ->query();
        $data       = $this->fetchAll($select);
        return $data;
    }
    
    
    /**
     * Requires the phrase code and the section code
     * @param $pcode
     * @param $sectionCode
     */
    function getPhraseByCode($pcode, $sectionCode) {
        $select = $this->select()->setIntegrityCheck(false);
        $select
            ->from('phrases', array('phrases.id', 'section_id', 'phrases.name'))
            ->joinLeft('sections', 'sections.id = phrases.section_id', array())
            ->where('phrases.name = ?', $pcode)
            ->where('sections.code = ?', $sectionCode)
            ->limit(1);
        $row    = $this->fetchRow($select);
        if ($row) {
            return (object) $row->toArray();
        } else {
            return false;
        }
    }
    
    /**
     * Returns the phrase language object given the phrase ID and the language 
     * @param $phraseId
     * @param $langId
     */
    function getPhraseLangByPhrase($phraseId, $langId) {
    $select = $this->select()->setIntegrityCheck(false);
        $select
            ->from('phrase_langs', array('phrase_langs.id', 'phrase_id', 'language_id', 'value'))
            ->joinLeft('phrases', 'phrases.id = phrase_langs.phrase_id', array())
            ->where('phrases.id = ?', $phraseId)
            ->where('phrase_langs.language_id = ?', $langId)
            ->limit(1);
        $row    = $this->fetchRow($select);
        if ($row) {
            return (object) $row->toArray();
        } else {
            return false;
        }
    }
}