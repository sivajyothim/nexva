<?php
class Default_PromotionCodeController extends Nexva_Controller_Action_Web_MasterController {
    
    function viewCodesByCampaignAction() {
        $id     = $this->_getParam('id', null);
        $campaign   = null;
        if (!$id) { 
            $id = null;
        } else {
            $campaignModel     = new Admin_Model_PromotionCampaign();
            $campaign          = $campaignModel->fetchRow('id = ' . $id);    
        }
        
        $this->_helper->layout()->setLayout('web/clean');
        
        $promotionModel     = new Model_PromotionCode();
        $promoCodes         = $promotionModel->getPromotionCodeListForCampaign($id);
        $this->view->promoCodes = $promoCodes;
    }
    
    
    function viewPromotionCodeAction() {
        $code   = $this->_getParam('id', false);
        if (!$code) { 
            throw new Exception("Sorry, this page does not exist");
        }
        
        $promotionModel     = new Model_PromotionCode();
        $promocode          = $promotionModel->getPromotionCode($code, true);
        
        if (!$promocode) {
            throw new Exception("Sorry, this page does not exist");
        }
        
        $this->view->promoCodes = array($promocode);
        
        $this->_helper->layout()->setLayout('web/clean');
        $this->_helper->viewRenderer('view-codes-by-campaign');
    }
    
    function viewAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $code   = $this->_getParam('id', false);
        if (!$code) { 
            throw new Exception("Sorry, this page does not exist");
        }
        
        $promoBadge     = new Nexva_Badge_Promotion($code);
        $opts           = array();
        $promoBadge->displayBadge();
    }
}