<?php
class Model_Phrase extends Nexva_Db_Model_MasterModel {
    
    protected  $_id     = 'id';
    protected  $_name   = 'phrases';
    
    function __construct() {
        parent::__construct();
        $db     = Zend_Registry::get('db');
        $db->query('SET NAMES "utf8"')->execute();
    }
    
    /**
     * Loads the translations and uses a default language as fallback 
     * @param $key
     * @param $langId
     */
    function loadTranslations($key, $langId = null) {
        
        $cache      = Zend_Registry::get('cache');
        $ckey       = 'PHRASES__' . str_ireplace('-', '', $key) . $langId;

        if (($phrases = $cache->get($ckey)) !== false) {
            return $phrases;
        }
        
        //get default language
        $langModel      = new Model_Language();
        $defaultLangObj = $langModel->getDefaultLanguage();
        $defaultLang    = $defaultLangObj->id; 
        $langId     = ($langId) ? $langId : $defaultLang;
        
        $db         = Zend_Registry::get('db');
        $sql        = "        
            SELECT P.name, PL.value, PL.language_id, S.scope, S.code 
            FROM sections S
            RIGHT JOIN phrases P ON P.section_id = S.id
            RIGHT JOIN phrase_langs PL ON P.id = PL.phrase_id AND PL.language_id IN ({$defaultLang}, {$langId})
            WHERE 
                S.code = '{$key}' OR 
                S.scope = 'GLOBAL'  
            ORDER BY scope DESC, language_id ASC";
        $results    = $db->query($sql)->fetchAll();

        $phrases    = array();
        foreach ($results AS $result) {
            $phrases[$result->name] = $result->value;
        }
        
        $cache->set($phrases, $ckey);
        return $phrases;
    }
    
    /**
     * Loads the translations for a given language
     */
    public function getTranslation($sectionId, $langId = null, $list = false) {
        $select  = $this->select(false)->setIntegrityCheck(false);
        $select
            ->from('phrases', array('section_id', 'name'))
            ->joinLeft('phrase_langs', 'phrase_langs.phrase_id = phrases.id', array('phrase_id', 'value'))
            ->where('section_id = ?', $sectionId)
            ->where('language_id = ?', $langId)
            ->query();
        $rows   = $this->fetchAll($select);
        $data   = array();
        if ($rows) {
            $data   = $rows->toArray();
        }
        
        if ($list) {
            $listData   = array();
            foreach ($data as $row) {
                $listData[$row['name']] = $row['value'];
            }
            return $listData;
        } else {
            return $data;
        }
    }
    
    
    private function __normalizeKey($key) {
        $key    = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $key    = strtolower($key);
        return $key;
    }
    
    public function getPhraseCount($langId) {
        $select = $this->select();
        $select
            ->setIntegrityCheck(false)
            ->from('phrase_langs', array('count(id) as count'))
            ->where('language_id = ?', $langId);
        $rows = $this->fetchAll($select);
        
        return($rows[0]->count); 
    }
}