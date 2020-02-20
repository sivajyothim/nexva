<?php
/**
 * A utility validation and sanitization class
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Feb 22, 2011
 */
class Nexva_Util_Validation_Validate {
    
    /**
     * Accpets 2 associated arrays and does validation on $data based on rules on $required 
     * @param $data
     * @param $required
     */
    static function validate($data, $required) {
        
        
        //just go through $required and add 'required' for keys that have not been set
        $rules  = array();
        foreach ($required as $field => $rule) {
            $rules[$field]  = ($rule == '') ? 'required' : $rule; 
        } 
        
        unset($field);
        $errors = array();
        foreach ($data as $field => $value) {
            if (isset($rules[$field]) && !self::checkField($value, $rules[$field])) {
                /**
                 * @todo make the messages customizable and dependent on rules 
                 */
                $errors[]   = self::normalize($field) . ' is a required field'; 
            }
        }
        
        return $errors;
    }
    
    /**
     * 
     * Returns TRUE if 
     * @param $value
     * @param $rule
     */
    static function checkField($value, $rule) {
        switch ($rule) {
            case 'required':
            default:
                return trim($value) != '';    
                break;
        }
    }
    
    static function normalize($str) {
        return ucwords(preg_replace('/[^a-zA-Z0-9]+/', ' ', $str));
    }
}