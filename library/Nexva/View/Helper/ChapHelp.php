<?php

class Nexva_View_Helper_ChapHelp extends Zend_View_Helper_Abstract {

    public function chapHelp()
    {
        $sessionChapDetails = new Zend_Session_Namespace('partnermobile');
        $sessionChapDetails->id;
        
        if($sessionChapDetails->id) {
            $themeMeta   = new Model_ThemeMeta();
            $themeMeta->setEntityId($sessionChapDetails->id);
           return $themeMeta->WHITELABLE_SITE_CONTACT_US_EMAIL;
        }
    }
}