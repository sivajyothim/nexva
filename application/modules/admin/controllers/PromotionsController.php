<?php
class Admin_PromotionsController extends Nexva_Controller_Action_Admin_MasterController {
    
    
    function indexAction(){ 
        
    }
    
    function createAction(){ 
        $promoModel = new Model_PromotionCode();
        $promotion  = $promoModel->getPopulatedObject();
        $id     = $this->_getParam('id');
        if ($this->_request->isPost()) {
            $promotionData  = $promoModel->getPopulatedArray($this->_getAllParams());
            $promotionData['chap_id']       = empty($promotionData['chap_id']) ? null : $promotionData['chap_id'];
            $promotionData['payout_id']     = empty($promotionData['payout_id']) ? null : $promotionData['payout_id'];
            $promotionData['enabled']    = 1;
            $numCodes       = min(2000, $this->_getParam('num_codes', 1000));
            $state     = $promoModel->savePromotionCodes($this->_getParam('code'), $numCodes, $promotionData, $this->_getParam('product_id'));
            if ($state === true) {
                $this->__addMessage("Promotion code created successfully");
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'promotion-campaign/index');    
            } else {
                $this->view->errors  = array("The following error occured : " . $state);
                $promotion      = $promoModel->getPopulatedObject($promotionData);
            }
        } 
        
        $payoutModel    = new Model_Payout();
        $this->view->payouts    = $payoutModel->fetchAll();
        
        $promoCampaignModel     = new Admin_Model_PromotionCampaign();
        $this->view->campaigns  = $promoCampaignModel->fetchAll();
        
        $this->view->promotion  = $promotion;
    }
    
    function viewByCampaignAction() {
        $id     = $this->_getParam('id', null);
        
        $promotionModel     = new Model_PromotionCode();
        $promoCodes         = $promotionModel->getCodesByCampaign($id);
        $this->view->promoCodes = array_reverse($promoCodes, true);
        
        if ($id) {
            $campaignModel     = new Admin_Model_PromotionCampaign();
            $campaign          = $campaignModel->fetchRow('id = ' . $id);
            if ($campaign) {
                $this->view->campaignName   = $campaign->title;    
            }
        }
         
    }
    
    
    function shareAction() {
        $code     = $this->_getParam('code', null);
        if (!$code) {
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'promotion-code');
        }
        $this->view->code   = $code;
    }
    
    function viewAction() {
        $id     = $this->_getParam('id', null);
        if (!$id) {
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'promotion-campaign/index');  
            return;
        }
        $promotionModel = new Model_PromotionCode();
        $promotionCode  = $promotionModel->getPromotionCode($id);
        
        $this->view->savedUser      = isset($promotionCode['user']->name) ? $promotionCode['user']->name : '';
        $this->view->savedProduct   = isset($promotionCode['products'][0]->name) ? $promotionCode['products'][0]->name : ''; //system currently only supports 1 product per code 
        $this->view->promotionCode  = (object) $promotionCode;
        
        $payoutModel    = new Model_Payout();
        $this->view->payouts    = $payoutModel->fetchAll();
        
        $promoCampaignModel     = new Admin_Model_PromotionCampaign();
        $this->view->campaigns  = $promoCampaignModel->fetchAll();
    }
    
    function deleteAction() {
        $id = $this->_getParam('id');
        $promotionModel  = new Model_PromotionCode();
        try {
            $promotionModel->delete('id = ' . $id);
            $this->__addMessage('Promotion code deleted');
        } catch (Zend_Db_Statement_Mysqli_Exception $ex) {
            $this->__addErrorMessage('This promotion code cannot be deleted because it has already been used.');
        } catch (Exception $ex) {
            $this->__addErrorMessage('The following error occured while trying to delete this promotion code : ' . $ex->getMessage());
        }
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'promotions/view-by-campaign/');
    }
}
