<?php
/**
 * A view helper that prints the user meta info for given userId and meta_key
 *
 * @author chathura
 */

class Nexva_View_Helper_GetRepliedUser extends Zend_View_Helper_Abstract {

    public function GetRepliedUser($id, $metaKey) {

        $userModel = new Model_User();
        $userMetaValue= $userModel->getMetaValue($id, $metaKey);
        
        if(is_null($userMetaValue) || empty($userMetaValue)){
             $TheamMetaModel = new Model_ThemeMeta();
             $userMetaValueArr=$TheamMetaModel->getMetaValue($id, 'WHITELABLE_SITE_NAME');
             $userMetaValue=$userMetaValueArr[0]['meta_value'];
             
        }
        return $userMetaValue;
    }
}
?>
