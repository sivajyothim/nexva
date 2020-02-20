<?php

class Partner_TicketingController extends Nexva_Controller_Action_Partner_MasterController {

    public function init()
    {
        parent::init();
    }

    public function indexAction() 
    {
        if($this->_request->isPost())
        {
            
                $subject = trim($this->_getParam('txtSubject'));
                $type = trim($this->_getParam('selectType'));
                $priority = trim($this->_getParam('selectPriority'));            
                $description = trim($this->_getParam('txtDescrition'));
                $attachment = trim($this->_getParam('txtAttachment'));
        }

    }  
   
    

}