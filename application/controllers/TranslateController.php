<?php
class Default_TranslateController extends Nexva_Controller_Action_Web_MasterController {
    
    function init() {
        $params = $this->_getAllParams();
        if (in_array($params['action'], array('enable', 'convert', 'load')) === false) {
            $this->__authorize();
            $this->_helper->layout->setLayout('admin/generic'); //uses an admin layout but isn't actually an admin component
        }
    }
    
    function indexAction() {
        $sectionModel   = new Admin_Model_Section();
        $sections       = $sectionModel->fetchAll();
        
        $ns         = new Zend_Session_Namespace('translation');
        $this->view->langCode   = $ns->languageCode;
        $this->view->langId     = $ns->languageId;
        $this->view->langName   = $ns->languageName;
        $this->view->sections   = $sections;
        $this->view->titleForLayout = 'Translation Panel for ' . $ns->languageName;
        
        $this->__getStats();
    }
    
    function sectionAction() {
        $id = $this->_getParam('id', false);
        if (!$id) {
            $this->__addMessage('Invalid section id');
            $this->_redirect('/section/index');
        }
        
        $phraseModel    = new Admin_Model_Phrase();
        $langModel      = new Model_Language();
        $phraseLang     = new Model_Phrase();
        $ns             = new Zend_Session_Namespace('translation');
        
        $defaultLang    = $langModel->getDefaultLanguage();
        
        $phrases            = $phraseModel->getPhraseList($id, $defaultLang->id);
        
        $translatedPhrases  = $phraseLang->getTranslation($id, $ns->languageId, true);
        $fullCount          = $phraseLang->getPhraseCount($defaultLang->id);
        $langCount          = $phraseLang->getPhraseCount($ns->languageId);
        
        $this->view->fullCount  = $fullCount;
        $this->view->langCount  = $langCount;
        
        $this->view->translations   = $translatedPhrases;
        $this->view->langCode   = $ns->languageCode;
        $this->view->phrases    = $phrases;
        $this->view->langId     = $ns->languageId;
        $this->view->langName   = $ns->languageName;
        $this->view->titleForLayout = 'Translation Panel for ' . $ns->languageName;
    }
    
    function addLanguageAction() {
        $this->_helper->layout->setLayout('web/popup');
        $phraseId       = $this->_getParam('id', false);
        
        $phraseModel        = new Admin_Model_Phrase();
        $phraseLangModel    = new Admin_Model_PhraseLang();
        $langModel          = new Model_Language();
        
        $ns         = new Zend_Session_Namespace('translation');
        $langId     = $ns->languageId;
        $langName   = $ns->languageName;
        
        $phraseLang     = $phraseLangModel->getPopulatedObject();
        $translations   = $phraseModel->getTranslations($phraseId);
        
        $row            = $phraseModel->find($phraseId);
        $row            = $row->toArray();
        $phrase         = isset($row[0]) ? (object) $row[0] : $phrase; 
        
        if ($this->getRequest()->isPost()) {
            $data   = array(
                'phrase_id'     => $this->_getParam('id', null),
                'language_id'   => $langId,
                'value'         => $this->_getParam('value', '')
            );
            $id     = $this->_getParam('phraseLangId', null);
            $phraseModel->addTranslation($data, $id);
            $this->_redirect("/translate/add-language/id/{$phraseId}/");
        }

        if ($plId = $this->_getParam('plId')) {  
            $row            = $phraseLangModel->find($plId);
            $row            = $row->toArray();
            $phraseLang     = isset($row[0]) ? (object) $row[0] : $phraseLang;
        }
        
        $this->view->phraseLang     = $phraseLang;
        $this->view->translations   = $translations;
        $this->view->languageName   = $langName;
        $this->view->languageId     = $langId;
        $this->view->phrase         = $phrase;
    }
    
    function deleteTranslationAction() {
        $id     = $this->_getParam('id', 0);
        $plId   = $this->_getParam('plId', 0);  
        $model  = new Admin_Model_PhraseLang();
        $model->delete('id = ' . $plId);
        $this->_redirect("/translate/add-language/id/{$id}/");
    }
    
    
    function addAction() {
        
        $ns = new Zend_Session_Namespace('translation'); //include this only if the translation utility is enabled
        if (!(isset($ns->translatorEnabled) && $ns->translatorEnabled === true)) {
            die();//no need to let anyone in here
        }
        
        $ns = new Zend_Session_Namespace('translation');
         
        $section    = $this->_request->getParam('s', '');
        $phrase     = $this->_request->getParam('p', '');
        $language   = $ns->languageId;
        $translation    = $this->_request->getParam('t', '');
        
        $phraseModel    = new Admin_Model_Phrase();
        $phrase         = $phraseModel->getPhraseByCode($phrase, $section);
        $response       = array();   
        if ($phrase) {
            $phraseLang     = $phraseModel->getPhraseLangByPhrase($phrase->id, $language);
            $plId           = null;
            if ($phraseLang) {
                $plId   = $phraseLang->id;
            }
            
            $data   = array(
                'phrase_id' => $phrase->id,
                'language_id'   => $language,
                'value'         => $translation
            );
 
            
            $phraseModel->addTranslation($data, $plId);
            $response['phrase']         = $phrase->name;
            $response['translation']    = $translation;
        } else {
            $response['error']  = 'Translation could not be added - ' . $translation;
        }
        echo json_encode($response);
        die();
    }
    
     /**
     * You have to call this method with the correct values 
     * for the front end translation system to kick in
     */
    
    function enableAction() {
        $salt            = Zend_Registry::get('config')->nexva->translator->salt;
        
        $langId         = $this->_request->getParam('id', false);
        $hash           = $this->_request->getParam('h', false);
        
        //Be careful when changing this value. This is used in the Translate helper as well (T.php)
        $ns = new Zend_Session_Namespace('translation');
        
        if (md5($salt . $langId) == $hash) {
            
            $timeHash   = $this->_request->getParam('th', false);
            $time       = $this->_request->getParam('t', false);
            if ($timeHash != md5($salt . $time)) {
                //time hash doesn't match the time + salt
                $this->_redirect('/');
            }
            
            $fourteenDays   = 60 * 60 * 24 * 14;
            if ($time + $fourteenDays < time()) {
                //link works only for the 14 days
                $this->_helper->layout->setLayout('admin/generic');
                $this->render('expired');
                return;
            }
            
            $langModel              = new Model_Language();
            $row                    = $langModel->find($langId);
            $language               = array();
            $language['name']       = '';
            if ($row) {
                $row    = $row->toArray();
                $language   = $row[0];
            } else {
                die('Translation Utility could not be enabled. Language not present');
            }
            $ns->translatorEnabled  = true;
            $ns->languageId         = $langId;
            $ns->languageName       = $language['name'];
            $ns->languageCode       = $language['code'];
            $this->_redirect('/translate');
            
        } else {
            $ns->translatorEnabled  = false;
            $ns->languageId         = 0;
            $ns->languageName       = '';
            $ns->languageCode       = '';
            $this->_redirect('/'); 
        }
    }
    
    function disableAction() {
        $ns = new Zend_Session_Namespace('translation');
        $ns->translatorEnabled  = false;
        $ns->languageId         = 0;
        //redirect to mobile
        $url    = 'http://' . Zend_Registry::get('config')->nexva->application->mobile->url;
        $url    .= '/translate/disable/';
        $this->_redirect($url);
    }
    
    function loadAction() {
        $db             = Zend_Registry::get('db');
        $sql            = 'SELECT * FROM languages';
        $languages        = $db->fetchAll($sql);
        $this->view->languages    = $languages;
    }
    
    function convertAction() {
        $db             = Zend_Registry::get('db');
        
        $db->query('SET NAMES "utf8"')->execute();
        $data   = '';
        foreach ($_POST['languageId'] as $id) {
            $val        = $_POST['languageValue'][$id];
            $val        = $db->quote($val);
            $sql        = "UPDATE languages SET name ={$val} WHERE id = '{$id}'";
            $db->query($sql)->execute();
            //$data       .= $sql . '<br>';
        }
        $this->view->data   = $data;
    }
    
    private function __authorize() {
        $ns = new Zend_Session_Namespace('translation');
        
        if ($ns->translatorEnabled !== true) {
            $this->_redirect('/');
        }
    }
    
    private function __getStats() {

    }
}