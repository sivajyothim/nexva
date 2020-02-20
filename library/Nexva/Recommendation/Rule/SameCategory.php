<?php
/*
 * This rule implements the scoring system where if the product to be recommended is in the same category as the product
 * that is compared to.
 *
 * @author jahufar
 * 
 */

class Nexva_Recommendation_Rule_SameCategory extends Nexva_Recommendation_RecommendationAbstract {


    public function calculateScore($idToCompare, $idToRecommend) {

        

        $product = new Model_Product();
        $catIdToCompare = $product->getProductCategoryByProductId($idToCompare);
        $catIdRecommend = $product->getProductCategoryByProductId($idToRecommend);
		if($catIdToCompare == $catIdRecommend)    {
			
			return 15;
		}
		else 
		{
			return 0;
		}

    }

  
}
