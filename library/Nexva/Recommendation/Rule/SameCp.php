<?php
/*
 * This rule checks if the product is from the same CP and assigns a score accordinly.
 *
 * @author cheran
 *
 */

class Nexva_Recommendation_Rule_SameCp extends Nexva_Recommendation_RecommendationAbstract {

    public function calculateScore($idToCompare, $idToRecommend) {


        $productModel   =   new Model_Product();
        $cache     = Zend_Registry::get('cache');
        $key        = 'PRODUCT_CP_ID_' . $idToCompare;
        if (($userIdOfCompareProduct = $cache->get($key)) === false) {
            $userIdOfCompareProduct    =   $productModel->select()
                     ->setIntegrityCheck(false)
                     ->from('products','user_id')
                     ->where("id = $idToCompare")
                     ->query()
                     ->fetch()
                     ->user_id;
            $cache->set($userIdOfCompareProduct, $key);
        }
        
        
        $cache     = Zend_Registry::get('cache');
        $key        = 'PRODUCT_CP_ID_' . $idToCompare;
        if (($userIdOfRecommendedProduct = $cache->get($key)) === false) {
            $userIdOfRecommendedProduct    =   $productModel->select()
                     ->setIntegrityCheck(false)
                     ->from('products','user_id')
                     ->where("id = $idToRecommend")
                     ->query()
                     ->fetch()
                     ->user_id;  
            $cache->set($userIdOfRecommendedProduct, $key);
        }

        

      
        if(0==strcmp($userIdOfCompareProduct, $userIdOfRecommendedProduct)){
            return 5;
        }else{
            return 0;
        }
        

    }



   
}
?>
