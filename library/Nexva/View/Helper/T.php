<?php
/**
 * 
 * The translations helper. Short name and methods to keep the typing minimal
 * @author John
 *
 */
class Nexva_View_Helper_T extends Zend_View_Helper_Abstract {

    private $phrases    = array();
    private $page       = null;
    private $phraseTags = array();
    private $defaultPhrases   = array();
    
    public function __construct() { 
        $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $module = isset($params['module']) ? "{$params['module']}-" : '';
        $page   = "{$module}{$params['controller']}-{$params['action']}";
        $this->__loadPhrases($page);
        $this->page = $page;
        $this->defaultPhrases   = $this->getDefaultTranslations();
    }
    
    /**
     * 
     * Forces a page to use translations from a different page
     */
    public function setPage($page) {
        $this->__loadPhrases($page);
        $this->page = $page;
    }
    
    public function t($key = null) {
        if ($key) {
            return $this->_($key, true);    
        }
        
        return $this;
    }
    
    public function setPhraseTags($phraseTags = array()) {
        $this->phraseTags   = is_array($phraseTags) ? $phraseTags : array();
        $this->__doReplaceTags();
        return $this; //so we can still chain after setting the tags 
    }
    
    public function getPhraseTags() {
        return $this->phraseTags;
    }
    
    public function _($key = null, $disableHelper = false, $return = false) {
        $key    = trim($key);
        if (isset($this->phrases[$key])) {
            if ($return) {
                return $this->phrases[$key];    
            } else {
                if (!$disableHelper) {
                    echo $this->phrases[$key] . $this->__getTranslateLink($key);    
                } else {
                    echo $this->phrases[$key];
                }
            }
        }
        return '';
    }
    
    /**
     * convenience wrapper for __getTranslateLink
     */
    public function _h($key = '') {
        if (isset($this->phrases[$key])) {
            echo $this->__getTranslateLink($key);
        }
    }
    
    public function getDefaultTranslations() {
        $languageModel      = new Model_Language();
        $defaultLanguage    = $languageModel->getDefaultLanguage();
        $sectionModel   = new Admin_Model_Section();
        $section        = $sectionModel->getSectionByCode($this->page);
        if (!$section) {
            return array();
        }
        
        $phraseModel    = new Model_Phrase();
        return $phraseModel->getTranslation($section->id, $defaultLanguage->id, true);
    }
    
    public function getSection() {
        return $this->page;
    }
    
    private function __loadPhrases($page) {
        $ns             = new Zend_Session_Namespace('application');
        $phraseModel    = new Model_Phrase();
        $this->phrases  = $phraseModel->loadTranslations($page, $ns->language_id);
        
    }

    /**
     * Returns a translate link that translators can use to 
     * translate the phrases on the page itself. Checks the session
     * for a key that is set in TranslateController  
     */
    private function __getTranslateLink($key) { 
        $ns = new Zend_Session_Namespace('translation');
        if (isset($ns->translatorEnabled) && $ns->translatorEnabled === true) {
            $phrase = isset($this->phrases[$key]) ? htmlspecialchars($this->phrases[$key]) : '';
            $style  = 'z-index:999; margin-left: 10px;width: 16px; height: 16px; display:inline;position: absolute; cursor:help;';
            return '<span title="' . $phrase . '" language="' . $ns->languageName . '" 
                    style="' . $style . '" default="' . htmlspecialchars($this->defaultPhrases[$key]) . '"  
                class="ui-icon ui-icon-circle-plus translate-me" rel="' . $key . '"></span>';
        }
        return '';
    }
    
    private function __doReplaceTags() {
        foreach ($this->phrases as &$val) {
            $matches    = array();
            preg_match_all('/\{T_(.*?)\}/', $val, $matches);
            
            if (!empty($matches[0])) {
                foreach ($matches[0] as $matchNum => $match) {
                    if (isset($this->phraseTags[$matches[1][$matchNum]])) {
                        $val    = str_ireplace($match, $this->phraseTags[$matches[1][$matchNum]], $val);    
                    }
                }
            }
        }
    }
}