<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/5/13
 * Time: 5:44 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_View_Helper_ThemeMeta extends Zend_View_Helper_Abstract {

    public function ThemeMeta($userId, $metaName) {

        $themeMetaModel = new Model_ThemeMeta();
        $metaValue = $themeMetaModel->getMetaValue($userId,$metaName);
        if(count($metaValue)>0)
        {
            return $metaValue;
        }
        else
        {
            return NULL;
        }
    }
}