<?php
abstract class Nexva_PromotionCode_Type_Abstract {
    const USE_TYPE_GENERAL  = 'GENERAL';
    const USE_TYPE_SINGLE   = 'SINGLE';
    const USE_TYPE_LIMITED  = 'LIMITED';
    
    protected $promoCode    = null;
    
    public function __construct($promoCode) {
        $this->promoCode    = $promoCode; 
    }
    
    /**
     * @return Model_PromotionCode 
     */
    public function getPromoCode() {
        return $this->promoCode;
    }
    
    /**
     * Applies a price modification and returns the new price 
     * @param double $price
     */
    public function applyPriceModification($originalPrice) {
        $price      = $originalPrice;
        $discount   = 0; 
        if ($this->promoCode->discount_type == 'ABSOLUTE') {
            $discount   = $this->promoCode->amount;
        } else {
            $discount   = round($price * $this->promoCode->amount / 100, 2);
        }
        return max(0, number_format(round($price - $discount, 2), 2));
    }
    
    /**
     * 
     * Carries out the post process code on a promotion code
     * Usually increasing the usage counter. Each type can have its own post process code as well
     * @param $orderId 
     * @param $productId
     */
    public function doPostProcess() {
        $promoCodeModel     = new Model_PromotionCode();
        
        $promotionCode  = $this->promoCode;
        $data           = array();
        $currentUse     = $promotionCode->current_use;
        switch ($promotionCode['use_type']) {
            case self::USE_TYPE_LIMITED :
                $maxUse         = $promotionCode->use_limit;
                if ($maxUse <= ($currentUse + 1) ) {
                    $data['enabled']    = 0;
                }  
                break;
                
            case self::USE_TYPE_SINGLE :
                $data['enabled']    = 0; 
                break;
        }
        
        $data['current_use']    = ($currentUse + 1);
        $data['last_used_date']      = date('Y-m-d H:i:s');
        $promoCodeModel->update($data, 'id = ' . $promotionCode->id);

        //do the other stuff where you modify the order log and stuff
    }
}