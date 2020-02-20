<?php
/*
 * This rule implements the scoring system to check the recomended product is featured or not 
 * if the product is featured, 15 points 
 *
 * @author Chathura
 * 
 */

class Nexva_Recommendation_Rule_IsFeatured extends Nexva_Recommendation_RecommendationAbstract {


    public function calculateScore($idToCompare, $idToRecommend) {

        $product = new Model_Product();
        $isFeatured = $product->getProductFeaturedProductId($idToRecommend);
		if($isFeatured == 1)    {
			
			return 15;
		}
		else 
		{
			return 0;
		}

    }

  
}
