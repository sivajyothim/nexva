<?php

class Admin_PlatformController extends Zend_Controller_Action{

    public function preDispatch(){
        if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
        }
    }
    function indexAction(){
        
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'platform/list');
    }

    function listAction(){

        $platform       =   new Model_Platform();
        $all_platforms  =   $platform->fetchAll();

        $pagination = Zend_Paginator::factory($all_platforms);
        $pagination->setCurrentPageNumber($this->_getParam('page',1));
        $pagination->setItemCountPerPage(10);

        $this->view->platforms = $pagination;
    }

    function saveAction(){
        $platformModel  =   new Model_Platform();
        
        if('' == $this->_request->id){
            #Do insert
            
            $platformModel->insert(array(
              "id"    => NULL,
              "name"  => $this->_request->platformName,
              "description" => $this->_request->platformDescription,
              "status"  => $this->_request->platformStatus

            ));

            $this->view->message    =   "Platform {$this->_request->platformName} is created.";
            $this->render('/create');
            

        }else{
            #update existing
            
            $platformModel->update(array(

              "name"  => $this->_request->platformName,
              "description" => $this->_request->platformDescription,
              "status"  => $this->_request->platformStatus

            ),"id = ".$this->_request->id);

            $this->view->message    =   "Platform {$this->_request->platformName} is updated.";
            $this->render('/create');

        }

    }

    function createAction(){
        
            
        

    }

    function editAction(){


        $platformModel  =   new Model_Platform();
        $this->view->platform   =   current($platformModel->find($this->_request->id)->toArray());
        $this->render('/create');
    }


    function deleteAction(){
        $platformModel  =   new Model_Platform();
        $platformModel->delete("id = ".$this->_request->id);
        $this->view->message    =   "Platform attached with id - {$this->_request->id} is deleted.";
        $this->listAction();
        $this->render('/list');

    }
}

?>