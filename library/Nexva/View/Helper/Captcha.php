<?php
/**
 * A view helper to generate CAPTCHA images
 * 
 * @author jahufar
 */

class Nexva_View_Helper_Captcha extends Zend_View_Helper_Abstract {

    public function captcha()
    {
        return Nexva_Captcha_CaptchaImage::generate();
    }
}

?>