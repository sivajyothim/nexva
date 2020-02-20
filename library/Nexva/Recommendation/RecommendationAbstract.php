<?php
/**
 * Dear Implementor,
 * 
 * Please make *sure* your implemented class has a docblock header that describes what the rule does so everyone understands without
 * reading into the code too much. It is **very** important that you do this.
 *
 * E.g.
 *
 * This rule implements the scoring system where if the product to be recommended is in the same category as the product
 * that is compared to.
 *
 * @author jahufar
 *
 */


abstract class Nexva_Recommendation_RecommendationAbstract {

    abstract public function calculateScore($idToCompare, $idToRecommend);


    /**
     * Reads recommendations_ruleset.ini to figure out if the rule is enabled or not.
     * Returns TRUE if it's enabled, else returns FALSE
     *
     * @return boolean
     */
    public function ruleEnabled() {

        if( !Zend_Registry::isRegistered('config_recommendations_ruleset') ) {
            //@todo: cache this!
            $config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/recommendations_ruleset.ini", APPLICATION_ENV);
            Zend_Registry::set('config_recommendations_ruleset', $config);
        }
        
        $config = Zend_Registry::get('config_recommendations_ruleset');
        
        $className = get_class($this);

        if( !isset($config->rule->$className->enabled) ) 
            return false;
        else
            return $config->rule->$className->enabled;
                                      
    }

   
}
?>
