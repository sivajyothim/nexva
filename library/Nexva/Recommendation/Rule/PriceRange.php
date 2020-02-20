<?php
/*
 * This rule considers the prices of the Ids and assignes a score based on how close they are.
 *
 * The rules are as follows:
 *    - Prices are equal        : 10 points
 *    - Price difference >=1    : 8 points
 *    - Price difference >=1.5  : 6 points
 *    - Price difference >=2    : 4 points
 *    - Price difference >= 2.5 : 2 points
 *
 * @author jahufar
 *
 */

class Nexva_Recommendation_Rule_PriceRange extends Nexva_Recommendation_RecommendationAbstract {

    public function calculateScore($idToCompare, $idToRecommend) {

        $productModel   =   new Model_Product();
        
        $cache     = Zend_Registry::get('cache');
        $key        = 'PRODUCT_PRICE_' . $idToCompare;
        if (($priceOfCompareProduct = $cache->get($key)) === false) {
            $priceOfCompareProduct    =   $productModel->select()
                     ->setIntegrityCheck(false)
                     ->from('products','price')
                     ->where("id = $idToCompare")
                     ->query()
                     ->fetch()
                     ->price;
            $cache->set($priceOfCompareProduct, $key);
        }
        
        $cache     = Zend_Registry::get('cache');
        $key       = 'PRODUCT_PRICE_' . $idToRecommend;
        if (($priceOfRecommendedProduct = $cache->get($key)) === false) {
            $priceOfRecommendedProduct    =   $productModel->select()
                     ->setIntegrityCheck(false)
                     ->from('products','price')
                     ->where("id = $idToRecommend")
                     ->query()
                     ->fetch()
                     ->price;
            $cache->set($priceOfRecommendedProduct, $key);
        }
        
        

        

        if($priceOfCompareProduct <= 0.00){
            return 0;
        }
        
        if(0==strcmp($priceOfCompareProduct,$priceOfRecommendedProduct)){
            return 10;
        }

        $balance    =   $priceOfCompareProduct - $priceOfRecommendedProduct;

        if($balance <= 1){
            return 8;
        }

        if($balance <= 1.50){
            return 6;
        }
        if($balance <= 2.00){
            return 4;
        }
        if($balance <= 2.50){
            return 2;
        }
        if($balance > 2.50){
            return 0;
        }
    }

   


}
?>
