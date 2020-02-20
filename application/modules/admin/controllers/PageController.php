<?php
class Admin_PageController extends Nexva_Controller_Action_Admin_MasterController {

    function indexAction() {
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/facebox/facebox.css');

        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/facebox/facebox.js');

        $pageLanguageModel  =   new Model_PageLanguages();
        $select  = $pageLanguageModel->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
        ->setIntegrityCheck(false);

        $select->join('pages',' pages.id = page_languages.page_id',array('status', 'slug'));

        $this->view->pages  =   $pageLanguageModel->fetchAll($select)->toArray();

    }

    function createAction() {
        if($this->getRequest()->isPost()) {
            $pageLangId     = $this->_request->existingPageId;
            
            $pageModel      =  new Model_PageLanguages();
            $pageLangModel  =  new Model_Page();
            
            if('' == $this->_request->content || '' == $this->_request->title ) {
                $data               = $this->_request->getPost();
                $data['status']     = isset($this->_request->status) ? 1 : 0;
                $errorMessages      = array();
                $errorMessages[]    = 'Body and Title are both required';
                $page               = array_merge(
                    $pageModel->getPopulatedArray($data), 
                    $pageLangModel->getPopulatedArray($data)
                );
                $page['id']         = $pageLangId;
                
                $this->view->page   = $page;
                $this->view->errors = $errorMessages;
            } else {
                $pageId         =  $pageLangModel->insert(
                    array(
                        'id'        => NULL, 
                        'status'    => (isset($this->_request->status)) ? 1 : 0, 
                        'slug'      => $this->_request->slug
                    )
                );

                $pageModel ->insert(
                    array (
                        'language_id' => $this->_request->language_id,
                        'title' => trim($this->_request->title),
                        'content' => trim($this->_request->content),
                        'page_id'   => $pageId
                    )
                );

                $this->__addMessage("Page {$this->_request->title} is saved.");
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'page/index');
            }
        }

        $modelLanguage  =   new Model_Language();
        $this->view->languages  =   $modelLanguage->fetchAll()->toArray();
    }


    function editAction(){
        if (!($this->_request->id || $this->getRequest()->isPost())) {
            $this->__addErrorMessage("Invalid request, please try clicking on the edit link again.");
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'page/index');
        }

        if($this->_request->id != ''){
            $modelPage          = new Model_PageLanguages();
            $this->view->page   = $modelPage->getPageByPageLanguageId($this->_request->id);
        }

        if($this->getRequest()->isPost()) {
            $pageLangId     = $this->_request->existingPageId;
            $pageId         = $this->_request->page_id;

            $pageModel      =   new Model_Page();
            $pageLangModel  =   new Model_PageLanguages();
            
            $hasErrors      = false;
            if('' == $this->_request->content || '' == $this->_request->title ) {
                $data               = $this->_request->getPost();
                $data['status']     = isset($this->_request->status) ? 1 : 0;
                $hasErrors          = true;
                $errorMessages      = array();
                $errorMessages[]    = 'Body and Title are both required';
                $page               = array_merge(
                    $pageModel->getPopulatedArray($data), 
                    $pageLangModel->getPopulatedArray($data)
                );
                $page['id']         = $pageLangId;
                
                $this->view->page   = $page;
                $this->view->errors = $errorMessages;
            }

            if (!$hasErrors) {
                $pageModel      =  new Model_Page();
                $pageId         =  $pageModel->update(
                    array(
                        'status' => isset($this->_request->status) ? 1 : 0, 
                        'slug' => $this->_request->slug
                    ),
                    'id = ' . $pageId
                );
                
                $pageLangModel ->update(
                    array(
                        'language_id' => $this->_request->language_id,
                        'title' => trim($this->_request->title),
                        'content' => trim($this->_request->content)
                    ),
                    'id = ' . $pageLangId
                );

                
                $pageModel->update(array(
                'slug'      => $this->_request->slug,
                'status'    => $this->_request->status
                ),"id = {$pageId}");

                $this->__addMessage("Page {$this->_request->title} has been updated.");
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'page/index');
            }
        }

        $modelLanguage  =   new Model_Language();
        $this->view->languages  =   $modelLanguage->fetchAll()->toArray();
    }

    function deleteAction(){
        $modelPage      =   new Model_PageLanguages();
        $modelPage->delete("id = {$this->_request->id}");
        $this->view->message = 'Page is deleted';
        $this->indexAction();
        $this->render('/index');

    }

    function viewAction(){
        $this->_helper->layout->disableLayout();
        $modelPage      =   new Model_PageLanguages();
         
        $this->view->page = current($modelPage->find($this->_request->id)->toArray());
         

    }
    function saveAction() {
        if($this->getRequest()->isPost()) {
            $flashMessenger = $this->_helper->getHelper('FlashMessenger');
            $pageLangId     = $this->_request->existingPageId;

            if('' == $this->_request->body or '' == $this->_request->title ) {
                $flashMessenger->setNamespace('ERROR');
                $flashMessenger->addMessage("Title and body are required");
                $flashMessenger->resetNamespace();

                if ($pageLangId) {
                    $this->_redirect(ADMIN_PROJECT_BASEPATH.'page/edit/id/' . $pageLangId);
                } else {
                    $this->_redirect(ADMIN_PROJECT_BASEPATH.'page/index');
                }
            }

            $pageidModel    =   new Model_Page();
            if( $this->_request->pageid == '0') {
                $pageId =  $pageidModel->insert(array('id' =>NULL,'status' => "1", 'status' => $this->_request->slug));
            }else {
                $pageId = $this->_request->pageid;
            }

            $pageModel  =   new Model_PageLanguages();
            if($this->_request->existingPageId == '' or $this->_request->pageid == '0') {
                $pageModel->insert(array(
                        'page_id' => $pageId,
                        'language_id' => $this->_request->languageid,
                        'title'   => strtoupper(trim($this->_request->title)),
                        'content' => trim($this->_request->body)
                )
                );
            }else {

                $pageModel ->update(array(
                    'page_id' => $pageId,
                    'language_id' => $this->_request->languageid,
                    'title' => strtoupper(trim($this->_request->title)),
                    'content' => trim($this->_request->body)
                ),"id = {$this->_request->existingPageId}");

                $pageidModel->update(array('slug' => $this->_request->slug));
            }

            $flashMessenger->addMessage("Page {$this->_request->title} is saved.");
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'page/index');
        }else{

            $flashMessenger->setNamespace('ERROR');
            $flashMessenger->addMessage("You've made and invalid request.
                Please click the edit link to try again.");
            $flashMessenger->resetNamespace();
        }
    }
}