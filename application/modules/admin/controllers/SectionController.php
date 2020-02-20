<?php
class Admin_SectionController extends Nexva_Controller_Action_Admin_MasterController {
    
    function indexAction() {
        $sectionModel   = new Admin_Model_Section();
        $section    = $sectionModel->getPopulatedObject();

        if ($id = $this->_getParam('id', false)) {
            $row        = $sectionModel->find($id);
            $row        = $row->toArray();
            $section    = (object) $row[0];
        }
        
        $sections   = $sectionModel->fetchAll();
        
        $this->view->sections   = $sections;
        $this->view->section    = $section;
    }
    
    function saveAction() {
        $sectionModel   = new Admin_Model_Section();
        $data       = array(
            'name'  => $this->_getParam('name', ''),
            'code'  => strtolower($this->_getParam('code', '')),
            'scope' => $this->_getParam('scope', 'LOCAL')
        );
        if ($id = $this->_getParam('id', 0)) {
            $sectionModel->update($data, 'id = ' . $id); 
        } else {
            $sectionModel->insert($data); 
        }
        
        $this->__addMessage('Section saved');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'section/index');
    }
    
    
    function deleteAction() {
        
        $id     = $this->_getParam('id', 0);
        $sectionModel   = new Admin_Model_Section();
        $sectionModel->delete('id = ' . $id);
        $this->__addMessage('Section deleted');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'section/index');
    }
    
    function viewAction() {
        $id = $this->_getParam('id', false);
        if (!$id) {
            $this->__addMessage('Invalid section id');
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'section/index');
        }
        
        $sectionModel   = new Admin_Model_Section();
        $phraseModel    = new Admin_Model_Phrase();
        $langModel      = new Model_Language();
        
        $defaultLang    = $langModel->getDefaultLanguage();
        
        $row            = $sectionModel->find($id);
        $row            = $row->toArray();
        $section        = (object) $row[0];
        
        $phrases        = $phraseModel->fetchAll('section_id = ' . $id, 'name ASC');
        $phrases        = $phraseModel->getPhraseList($id, $defaultLang->id);
        $phrase         = $phraseModel->getPopulatedObject();
        
        if ($phraseId = $this->_getParam('phraseid', false)) { 
            $row            = $phraseModel->find($phraseId);
            $row            = $row->toArray();
            $phrase         = (object) $row[0];              
        }
        
        $this->view->phrase     = $phrase;
        $this->view->phrases    = $phrases;
        $this->view->section    = $section;
    }

    function savePhraseAction() {
        $phraseModel    = new Admin_Model_Phrase();
        $sectionId      = $this->_getParam('section_id', '');
        $data       = array(
            'name'  => $this->_getParam('name', ''),
            'section_id'  => $this->_getParam('section_id', ''),
        );
        if ($id = $this->_getParam('id', 0)) {
            $phraseModel->update($data, 'id = ' . $id); 
        } else {
            $phraseModel->insert($data); 
        }
        
        $this->__addMessage('Phrase saved');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'section/view/id/' . $sectionId);
    }
    
    function addLanguageAction() {
        $this->_helper->layout->setLayout('web/popup');
        $phraseId       = $this->_getParam('id', false);
        
        $phraseModel        = new Admin_Model_Phrase();
        $phraseLangModel    = new Admin_Model_PhraseLang();
        $langModel          = new Model_Language();
        
        $languages      = $langModel->fetchAll('status = 1');
        $phraseLang     = $phraseLangModel->getPopulatedObject();
        $translations   = $phraseModel->getTranslations($phraseId);
        
        $row            = $phraseModel->find($phraseId);
        $row            = $row->toArray();
        $phrase         = isset($row[0]) ? (object) $row[0] : $phrase; 
        
        if ($this->getRequest()->isPost()) {
            $data   = array(
                'phrase_id'     => $this->_getParam('id', null),
                'language_id'   => $this->_getParam('lang_id', null),
                'value'         => $this->_getParam('value', '')
            );
            $id     = $this->_getParam('phraseLangId', null);
            $phraseModel->addTranslation($data, $id);
            $this->_redirect("/section/add-language/id/{$phraseId}/");
        }

        if ($plId = $this->_getParam('plId')) {  
            $row            = $phraseLangModel->find($plId);
            $row            = $row->toArray();
            $phraseLang     = isset($row[0]) ? (object) $row[0] : $phraseLang;
        }
        
        $this->view->phraseLang     = $phraseLang;
        $this->view->translations   = $translations;
        $this->view->languages      = $languages;
        $this->view->phrase         = $phrase;
    }
    
    function deletePhraseAction() {
        $id         = $this->_getParam('id', 0);
        $sectionId  = $this->_getParam('sectionid', 0);
        $sectionModel   = new Admin_Model_Phrase();
        $sectionModel->delete('id = ' . $id);
        $this->__addMessage('Phrase deleted');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'section/view/id/' . $sectionId);
    }
    
    function deleteTranslationAction() {
        $id     = $this->_getParam('id', 0);
        $plId   = $this->_getParam('plId', 0);  
        $model  = new Admin_Model_PhraseLang();
        $model->delete('id = ' . $plId);
        $this->__addMessage('Translation deleted');
        $this->_redirect("/section/add-language/id/{$id}/");
    }
 
}