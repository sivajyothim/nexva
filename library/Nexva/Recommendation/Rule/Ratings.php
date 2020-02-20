<?php
/*
 * This rule implements the scoring system to check the recomended product's average ratings 
 * 
 *The rules are as follows:
 *    - AverageRating is   >= 0 and <= 1.5 , -5 points 
 *    - AverageRating is   >= 1.6 and <= 3 , 2 points 
 *    - AverageRating is   >= 3.1 and <= 4 , 4 points 
 *    - AverageRating is   >= 4 , 6 points 
 *    
 *    
 * @author Chathura jayasekara
 * 
 */

class Nexva_Recommendation_Rule_Ratings extends Nexva_Recommendation_RecommendationAbstract {


    public function calculateScore($idToCompare, $idToRecommend) {

        $product = new Model_Review();
        $AverageRating = $product->getAverageRating($idToRecommend);
        
        switch($AverageRating)
        {
	        case ($AverageRating ==NULL):
	        return 0;
	        break;
	
	        case ($AverageRating >= 0 and $AverageRating <= 1.5):
	        return -5;
	        break;
	
	        case ($AverageRating >= 1.6 and $AverageRating <= 3):
	        return 2;
	        break;
	
	        case ($AverageRating >= 3.1 and $AverageRating <= 4):
	        return 4;
	        break;
	        
	        default:
	        return 6;
	        break; 
        }
        
        

    }

  
}
