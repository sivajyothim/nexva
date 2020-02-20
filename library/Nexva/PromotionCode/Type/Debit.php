<?php
class Nexva_PromotionCode_Type_Debit extends Nexva_PromotionCode_Type_Abstract {
    
    public function doPostProcess($productId) {
        parent::doPostProcess();
        
        //do debiting stuff
        $userAccount        = new Model_UserAccount();
        $promoCodeModel     = new Model_PromotionCode();
        $productModel       = new Model_Product();
        
        $promotionCode          = $this->promoCode;
        $product                = $productModel->getProductDetailsById($productId, true);
        
        $params     = array(
            'uid'   => $promotionCode->user_id,
            'desc'  => "{$product['name']} purchased. Promocode : {$promotionCode->code}"
        );
        $subsidizedPrice    = 0;
        if (((float)$promotionCode->debit_amount) > 0) {
            $subsidizedPrice    = $promotionCode->debit_amount;
        } else {
            $newPrice   = $this->applyPriceModification($product['cost']);
            $subsidizedPrice    = $product['cost'] - $newPrice;     
        }
        
        $userAccount->debit($subsidizedPrice, $params);
    }
}