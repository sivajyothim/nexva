<?php

class Partnermobile_LanguageController extends Nexva_Controller_Action_Partnermobile_MasterController  {

    public function init()
    {
        parent::init();
    }

    public function index() {

    }
     
    public function setLanguageSessionAction() 
    {

        if($this->_request->isPost())
        {
            $languageCode = $this->_getParam('code');
            $languageId = $this->_getParam('id');
             
            $sessionLanguage = new Zend_Session_Namespace('languagesession');
            $sessionLanguage->code = $languageCode;
            $sessionLanguage->id = $languageId;
        }
        
        die();
    }  
   
    

}