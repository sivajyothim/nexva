<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Admin_HandsetController extends Zend_Controller_Action{

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


    }

    function listAction(){

        $devices = new Model_Device();
        $all_devices    =   $devices->fetchAll();

        $pagination =   Zend_Paginator::factory($all_devices);
        $pagination->setItemCountPerPage(10);
        $pagination->setCurrentPageNumber($this->_getParam('page', 1));
        $this->view->handsets = $pagination;

    }


}
?>
