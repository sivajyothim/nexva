<?php
/**
 * This is a copy of the translate controller running on the Default module
 * If you don't have a copy, you're going to run into a lot of routing issues 
 * and cross domain AJAX call problems
 * 
 * @author John
 *
 */
class Mobile_TranslateController extends Nexva_Controller_Action_Mobile_MasterController {
    
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
        $url            = $this->_request->getParam('u', false);
        
        //Be careful when changing this value. This is used in the Translate helper as well (T.php)
        $ns     = new Zend_Session_Namespace('translation');
        $nsA    = new Zend_Session_Namespace('application');
        
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
        
        if (md5($salt . $langId) == $hash) {
            $ns->translatorEnabled  = true;
            $ns->languageId         = $langId;
            $ns->languageName       = $language['name'];
            $ns->languageCode       = $language['code'];
            $nsA->disableMobileDetection = true;
            if ($url) {
                $url    = base64_decode($url);
                $this->_redirect($url);    
            } else {
                die('Translation Utility Enabled');    
            }
            
        } else {
            $ns->translatorEnabled  = false;
            $nsA->disableMobileDetection = false;
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
        //remove the no redirect flag
        $url    = 'http://' . Zend_Registry::get('config')->nexva->application->mobile->url;
        $url    .= '/index/index/noredirect/2';
        $this->_redirect($url);
    }
}