<?php
class Cpbo_NexpageController extends Nexva_Controller_Action_Cp_MasterController {
    
    function indexAction() {
        $modelPage      = new Model_PageLanguages();
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        $languageId=1;
        if(in_array($chapId, array('585474','585480'))){
            $languageId=2;
        }
        
        $page           = $modelPage->getPageBySlug('neXpager',$languageId);
        
        $user           = $this->_getCp();
        $userMeta       = new Model_UserMeta();
        $userMeta->setEntityId($user->id);
        $userDetails    = $userMeta->getAll();
        
        $this->view->user   = $userDetails;
        $this->view->page   = $page; 
        $this->view->cp     = $user;
    }
    
    function updateStatusAction() {
        $cpId       = $this->_getCpId();
        $userMeta   = new Model_UserMeta();
        $userMeta->setEntityId($cpId);
        ($userMeta->ACTIVE_NEXPAGE == 1) ? $userMeta->ACTIVE_NEXPAGE = 0 : $userMeta->ACTIVE_NEXPAGE = 1; //toggle state 
        $this->_redirect('/nexpage/');
    }
}