<?php
class Nexva_Factory_Whitelabel_Model {

    /**
     * Returns a constructed  model with all the chap specific data 
     * injected into it. 
     * @param String $modelName Whitelabel_Model_ will be appended to the string
     * @param Array $opts
     * @return Whitelabel_Model
     */
    public static function getModel($modelName, $opts = array()) {
        
        $modelNameStr           = 'Whitelabel_Model_' . trim($modelName);
        $model                  = new $modelNameStr();
        $model->chapId          = empty($opts['chapId']) ? null : $opts['chapId'];
        $model->platformId      = empty($opts['platformId']) ? null : $opts['platformId'];
        //get chap rules
        
        return $model;                
    }
} 