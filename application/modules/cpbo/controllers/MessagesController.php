<?php

/**
 * 
 * Controller for handling announcements. Currently only admin announcements are supported
 * but we will probably move toward a full fledged message center
 * @author Administrator
 *
 */
class Cpbo_MessagesController extends Nexva_Controller_Action_Cp_MasterController {
    
    function indexAction() {

        //Zend_Debug::dump($this->_getCHAPid());die();
        $announcementsModel         = new Model_Announcement();
        $this->view->announcements  = $announcementsModel->getMessages($this->_getCHAPid(),20);
        $announcementsModel->deleteUnreadMessages($this->_getCpId());
    }
    
}