<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rooban
 * Date: 19/11/13
 * Time: 01:00 PM
 */

class Nexva_View_Helper_LimitWords extends Zend_View_Helper_Abstract
{

    /**
     * @return imploded query string (reduce the string word count by given length)
     * */
    public function LimitWords($string, $wordLimit)
    {
        $words = explode(" ",$string);
        return implode(" ",array_splice($words,0,$wordLimit));
    }
}