<?php
/**
 * Recommendations class.
 *
 * @author jahufar
 */

define("RULE_CLASS_NAMESPACE", "Nexva_Recommendation_Rule_");
define("RULE_DIR", APPLICATION_PATH."/../library/Nexva/Recommendation/Rule");

class Nexva_Recommendation_Recommendation  {

    private $appFilter  = null;
    
    public function __construct($filer = null) {
        $this->appFilter    = $filer;
    }
    
    /**
     * Returns a set of recommended products.
     *
     * @param int $productId
     * @param int $buildId
     * @param int $numberOfRecommendations
     * @return array
     */
    public function getRecommendationsForProduct($productId,  $deviceId = null, $numberOfRecommendations = 5, $langId = false) {

        $productModel = new Model_Product();
        $productModel->appFilter = $this->appFilter; 

        $products = $productModel->getCompatibleProducts($productId,  $deviceId);
        $recommendationScores = array();

        foreach($products  as $product ) {

            $score = $this->processRules($productId, $product);

            $recommendationScores[$product] = $score;
        }

        arsort($recommendationScores, SORT_NUMERIC );

        $recommendationScores = array_slice($recommendationScores, 0, $numberOfRecommendations, true);
        
        $recommendedProducts = array();
        foreach($recommendationScores as $product => $score) {
            $recommendedProducts[] = $productModel->getProductDetailsById($product, false, $langId);
        }
        return $recommendedProducts;
    }

    public function getRecommendationsForUser($userId) {
        //@todo: implement this functionality

    }

    protected function processRules($productIdToCompare, $productIdToRecommend) {


        //open up the rules directory      
        $result = opendir(RULE_DIR);

        $score = 0;
        
        while (($file = readdir($result)) !== false) {

            if( is_file(RULE_DIR."/".$file) ) {

                include_once( RULE_DIR."/".$file ); //include the rule file so Zend_Loader doesnt fire

                $className = explode(".", $file);

                $className = RULE_CLASS_NAMESPACE.$className[0];
                
                if( ! class_exists($className) ) {
                    trigger_error('Invalid rule file: '.$file. ". Unable to find class ". $className);
                    continue;
                }

                $class = new $className;

                if( $class->ruleEnabled() )
                    $score += $class->calculateScore($productIdToCompare,$productIdToRecommend);
                                                       
            }
            

        }

        closedir($result);
        return $score;

        
    }


    



    
}
?>
