<?php
class Nexva_PromotionCode_PromotionCode {
    
    /**
     * Loads a given promotion code and applies to the session. Returns true on success/false otherwise
     * @param $promoCodeId
     */
    public function applyCode($promoCode) {
        $codeModel      = new Model_PromotionCode();
        
        //LK11 VWN BJR
        $promoCode      = $this->normalizeCode($promoCode);  
        $code           = $codeModel->getPromotionCode($promoCode, true);
        if ($code) {
            $promoSession   = new Zend_Session_Namespace('Promocode');
            $promoCodeState = new stdClass();
            
            $promoCodeState->codeApplied  = true;
            $promoCodeState->codeObject   = $code;  
            $promoCodeState->codeActive   = true;
            if ( ($code['enabled'] != 1) || (strtotime($code['valid_from']) > time()) ||  (strtotime($code['valid_to']) < time()) ) {
                $promoCodeState->codeActive   = false;
            }  
            $promoSession->appliedCode  = $promoCodeState;
            
            if ($promoCodeState->codeActive) {
                return true;
            } 
        }
        return false;
    }
    
    /**
     * Returns the currently applied code or an empty object on failure
     */
    public function getAppliedCode() {
        $promoSession       = new Zend_Session_Namespace('Promocode');
        if ($promoSession->appliedCode) {
            return $promoSession->appliedCode;
        } else {
            $promoCodeStateArr  = array(
                'codeApplied'   => false,
                'codeObject'    => false,
                'codeActive'    => false
            );
            return (object) $promoCodeStateArr;
        }
    }
    
    /**
     * Checks the validity of a promotion code for a given product
     * Promotion code must already be applied
     * @param $productId
     */
    public function checkPromotiocodeValidityForProduct($productId, $currentCode = null) {
        if (!$currentCode) {
            $currentCode    = $this->getAppliedCode();    
        }
        
        if ($currentCode->codeActive) {
            $db             = Zend_Registry::get('db');
            $validProduct   = $db->fetchOne("SELECT COUNT(id) FROM product_promocodes WHERE promo_id = ? AND product_id = ?", array(
                $currentCode->codeObject['id'], $productId));
            
            if ($validProduct) {
                return true;
            }
        }
        
        return false;
    }
    
    public function clearPromotionCode() {
        $promoSession   = new Zend_Session_Namespace('Promocode');
        $promoSession->unsetAll();
    }
    
    public function normalizeCode($promoCode = null) {
        return strtoupper(preg_replace("/[^a-zA-Z0-9-]/", "", $promoCode));
    }
}