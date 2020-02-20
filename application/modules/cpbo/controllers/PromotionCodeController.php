<?php
class Cpbo_PromotionCodeController extends Nexva_Controller_Action_Cp_MasterController {
    
    public function indexAction() {
        $promoModel = new Model_PromotionCode();
        $codes      = $promoModel->getCodesByUser(Zend_Auth::getInstance()->getIdentity()->id);
        $this->view->promoCodes = $codes;
    }
    
    public function createAction() {
        $promoModel = new Model_PromotionCode();
        $promotion  = $promoModel->getPopulatedObject();
        $id     = $this->_getParam('id');
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        if ($this->_request->isPost()) {
            $promotionData  = $promoModel->getPopulatedArray($this->_getAllParams());
            $promotionData['chap_id']       = null;
            $promotionData['payout_id']     = null;
            $promotionData['enabled']       = 1;
            $promotionData['user_id']       = Zend_Auth::getInstance()->getIdentity()->id;
            $promotionData['promo_campaign_id'] = null;
            $promotionData['promo_type']    = Model_PromotionCode::PROMOCODE_TYPE_STANDARD;
            $promotionData['discount_type'] = "PERCENT";
            $numCodes                       = 1;
            $stub                           = 'CP' . $promotionData['user_id']; 
            $codePattern                    = $stub . '[CODE]';//combination of ID and code
            
            $maxCodeSize    = Zend_Registry::get("config")->promo->code->maxsize + strlen($stub);
            $promoModel->setMaxCodeSize($maxCodeSize);
            
            $state     = $promoModel->savePromotionCodes($codePattern, $numCodes, $promotionData, $this->_getParam('product_id'));
            if ($state === true) {
                $this->__addMessage($translate->translate("Promotion code created successfully"));
                $this->_redirect('/promotion-code/index');    
            } else {
                $this->view->errors  = array($translate->translate("The following error occured")." : " . $state);
                $promotion      = $promoModel->getPopulatedObject($promotionData);
            }
        } 
        
        $payoutModel    = new Model_Payout();
        $this->view->payouts    = $payoutModel->fetchAll();
        
        $promoCampaignModel     = new Admin_Model_PromotionCampaign();
        $this->view->campaigns  = $promoCampaignModel->fetchAll();
        
        $this->view->promotion  = $promotion;
    }
    
    function shareAction() {
        $code     = $this->_getParam('code', null);
        if (!$code) {
            $this->_redirect('/promotion-code');
        }
        $this->view->code   = $code;
    }
    
    function deleteAction() {
        $id = $this->_getParam('id');
        $promotionModel  = new Model_PromotionCode();
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');
        
        try {
            $promotionModel->delete('id = ' . $id);
            $this->__addMessage($translate->translate("Promotion code deleted"));
        } catch (Zend_Db_Statement_Mysqli_Exception $ex) {
            $this->__addErrorMessage($translate->translate("This promotion code cannot be deleted because it has already been used."));
        } catch (Exception $ex) {
            $this->__addErrorMessage($translate->translate("The following error occured while trying to delete this promotion code").' : ' . $ex->getMessage());
        }
        $this->_redirect('/promotion-code/index/');
    }
}