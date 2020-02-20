<?php
class Reseller_PromoCodeController extends Zend_Controller_Action {
    
    function indexAction() {
        $campaignModel          = new Admin_Model_PromotionCampaign();
        $cId        = $this->_getParam('id', false);
        
        $campaigns      = $campaignModel->fetchAll('user_id = ' . Zend_Auth::getInstance()->getIdentity()->id);
        
        $this->view->campaigns  = $campaigns;
    }
    
    function viewByCampaignAction() {
        $id     = $this->_getParam('id', null);
        
        if (!$id) {
            $this->_redirect('/promo-code/index');
        } 
        
        $campaignModel     = new Admin_Model_PromotionCampaign();
        $campaign          = $campaignModel->fetchRow('id = ' . $id . ' AND user_id = ' . Zend_Auth::getInstance()->getIdentity()->id);
        if (!$campaign) {
            $this->_redirect('/promo-code/index');
        }
        
        $this->view->campaignName   = $campaign->title;
        $promotionModel     = new Model_PromotionCode();
        $promoCodes         = $promotionModel->getCodesByCampaign($id);
        $this->view->promoCodes = array_reverse($promoCodes, true);
        
    }
    
    function shareAction() {
        $code     = $this->_getParam('code', null);
        if (!$code) {
            $this->_redirect('/promotion-code');
        }
        $this->view->code   = $code;
    }
    
    function csvAction() {
        $id     = $this->_getParam('id', null);
        if (!$id) {
            $this->_redirect('/promotion-campaign/index');  
            return;
        }
        
        $campaignModel     = new Admin_Model_PromotionCampaign();
        $campaign          = $campaignModel->fetchRow('id = ' . $id . ' AND user_id = ' . Zend_Auth::getInstance()->getIdentity()->id);
        if (!$campaign) {
            $this->_redirect('/promo-code/index');
        }
        
        $promotionModel     = new Model_PromotionCode();
        $promoCodes         = $promotionModel->getCodesByCampaign($id);
        $qrcodeHelper       = new Nexva_View_Helper_Qr();
        
        $config         = Zend_Registry::get("config");
        $base           = $config->nexva->application->base->url;
        $chapId         = false;
        foreach ($promoCodes as $code) {
            $chapId     = $code['chap_id'];
            break;
        }
        if ($chapId) {
            $themeMeta      = new Model_ThemeMeta();
            $themeMeta->setEntityId($chapId);
            $base           = $themeMeta->WHITELABLE_URL;
        }
        $fileName   = $config->nexva->application->tempDirectory  . '/promoCSV-' . $id . '.csv';
        $fd     = fopen($fileName, 'w');
        $data   = array(
            'CODE',
            'DISCOUNT TYPE',
            'DISCOUNT AMOUNT',
            'USED',
            'USED DATE',
            'QR CODE LINK',
            'QR CODE HTML'
        );
        fputcsv($fd, $data);
        foreach ($promoCodes as $code) {
            $qrImage    = $qrcodeHelper->qr('http://' . $base . '/app/apply-code/id/' . $code['products'][0]->id . '/code/' . $code['code'], 90, 90);
            $data       = array(
                $code['code'],
                $code['discount_type'],
                $code['amount'],
                ($code['enabled'] == 1) ? 'No' : 'Yes' , //whether it's used or not
                $code['last_used_date'],
                $qrImage,
                '<img src="' . $qrImage . '" alt="scan this QR code to download the app">'   
            );
            fputcsv($fd, $data);
        }
        fclose($fd);
        header('Content-Disposition: attachment; filename="promocodes.csv"');
        header("Content-Type: text/csv");
        readfile($fileName);
        die();
    }
    
    function viewAction() {
        $id     = $this->_getParam('id', null);
        if (!$id) {
            $this->_redirect('/promotion-campaign/index');  
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
}