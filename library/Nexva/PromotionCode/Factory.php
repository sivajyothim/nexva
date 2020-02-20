<?php
class Nexva_PromotionCode_Factory {

    /**
     * 
     * @param $promoType
     * @param Nexva_PromotionCode_Type_Abstract $promotionCode
     */
    public static function getPromotionCodeType($code) {
        
        $promoModel     = new Model_PromotionCode();
        $promoCode      = $promoModel->fetchRow('code = "' . $code . '"');
        if (!$promoCode) {
            throw new Exception('Promotion code does not exist');
        }
        
        switch ($promoCode->promo_type) {
            case 'DEBIT' :
                $promoTypeObject    = new Nexva_PromotionCode_Type_Debit($promoCode); 
                break;
                
            case 'STANDARD' :
            default : 
                $promoTypeObject    = new Nexva_PromotionCode_Type_Standard($promoCode);
                break;
        }
        
        return $promoTypeObject;
    }
}