<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rooban
 * Date: 04/11/13
 * Time: 06:20 PM
 */

class Nexva_View_Helper_ImplodeAssociativeArray extends Zend_View_Helper_Abstract
{

    /**
     * @return imploded query string of associative array
     * */
    public function ImplodeAssociativeArray($array, $separator, $glue)
    {
        // separate the associative array into keys and values
        $keys = array_keys($array);
        $values = array_values($array);

        // build a new array with joined keys and values
        $newArray = null;
        foreach ($keys as $key => $value){
            //echo $value.' # '.$values[$key].' # ';
            //for ($i = 0; $i < count($keys); $i++) {
            $newArray[] = $value . $separator . $values[$key];
        }
 //die();
        // implode and return the new array
        return implode($glue, $newArray);
    }
}