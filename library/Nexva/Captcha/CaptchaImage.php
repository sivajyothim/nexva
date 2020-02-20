<?php

/**
 * Library to generate CAPTCHA images using Zend_Captcha_Image
 *
 * @author jahufar
 */

class Nexva_Captcha_CaptchaImage
{

    /**
     * Validates a CAPTCHA
     *
     * @param string $id
     * @param string $input
     * @return boolean
     */
     public static function validate($id, $input) {

         $captchaId = $id;

         $captchaInput = $input;
         $captchaSession = new Zend_Session_Namespace('Zend_Form_Captcha_' . $captchaId);
         $captchaWord =   $captchaSession->word;

         if( $captchaWord )
             return $captchaInput == $captchaWord;
         return false;

     }

     /**
      * Generates a CAPTCHA image. Returns the filename sans the extention (which is .png)
      *
      * @param string $postPrefix
      * @param int $length
      * @param int $timeout
      * @param string $fontFace
      * @return string
      */
     public static function generate($postPrefix = "captcha", $length = 6, $timeout=600, $fontFace='impact.ttf') {

         $captcha = new Zend_Captcha_Image(
             array(
             'name' => $postPrefix,
             'wordLen' => $length,
             'timeout' => $timeout,
             'font' => "fonts/".$fontFace,
             'imgdir' => "./captcha",
             'imgurl' => "/captcha"
         ));

         return $captcha->generate();
     }

}



?>
