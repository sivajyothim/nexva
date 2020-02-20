<?php
/**
 * Created by PhpStorm.
 * User: viraj
 * Date: 10/9/14
 * Time: 4:48 PM
 */

class Nexva_View_Helper_FileSize extends Zend_View_Helper_Abstract
{

    function FileSize($bytes)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

}