<?php
class Admin_LanguageController extends Nexva_Controller_Action_Admin_MasterController {
    
    function indexAction() {
        $langModel  = new Model_Language();
        $langs      = $langModel->fetchAll();
        
        $language   = $langModel->getPopulatedObject();
        
        if ($id = $this->_request->getParam('langid', false)) {
            $row        = $langModel->find($id); 
            $row        = $row->toArray();
            $language   = (!empty($row[0])) ? (object) $row[0] : $language;
        }
        
        
        
        $this->view->languages  = $langs; 
        $this->view->language   = $language;
    }
    
    function saveAction() {
        
        $data   = array(
            'name'      => $this->_request->getParam('name', false),
            'common_name'      => $this->_request->getParam('common_name', false),
            'code'      => $this->_request->getParam('code', false),
            'status'    => $this->_request->getParam('status', 0),
        );
        
        if (empty($data['name']) || empty($data['code'])) {
            $this->__addErrorMessage('Name and Code are both required');
            
            if ($id = $this->_getParam('id', 0)) {
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'language/index/langid/' . $id); 
            } else {
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'language/'); 
            }
        }
        
        $languageModel  = new Model_Language();
        if ($id = $this->_getParam('id', 0)) {
            $languageModel->update($data, 'id = ' . $id); 
        } else {
            $languageModel->insert($data); 
        }
        
        $this->__addMessage('Language saved');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'language/index');
    }
    
    function deleteAction() {
        
        $id     = $this->_request->getParam('langid', 0);
        $langModel  = new Model_Language();
        $langModel->delete('id = ' . $id);
        $this->__addMessage('Language deleted');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'language/index');
    }
}