<?php
/**
 * User: viraj
 * Date: 8/23/13
 * Time: 3:46 PM 
 */

class Nexva_Plugin_Translate extends Zend_Controller_Plugin_Abstract
{

     //Load translation based on the module
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {   
        /*error_reporting(E_ALL);
        ini_set('display_errors', 1);*/
        
        //Get module name        
        $module = $request->getModuleName();    
 
        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
             echo 'test';
             Zend_Debug::dump($module);
             die();
        }*/
        
        switch ($module) 
        {
            case 'partner':

                //Get CHAP ID
                $themeMeta = new Model_ThemeMeta();
                $chapId = $themeMeta->getThemeByHostNameForPartner($_SERVER['SERVER_NAME']);
              
                
                //Get CHAP locale
                // $modelUserLanguages = new Partner_Model_LanguageUsers();

                $modelUserLanguages = new Model_LanguageUsers;
                $locale = $modelUserLanguages->getLanguageCodeByChap($chapId);

                //If chap is multilingual
                $sessionLanguage = new Zend_Session_Namespace('languagesession');
                if($sessionLanguage->code){
                    $locale = $sessionLanguage->code;
                }
                else{
                    if($chapId == 81604 || $chapId == 274515 || $chapId == 585480 || $chapId == 585474 || $chapId == 282227){ //This is for temporary for qelasy
                        $locale  = 'fr';
                    }
                    //The French translations for Airtel Niger and Airtel Gabon is different than other french chap translations. So new language is created as fr2.
                    if($chapId == 274515 || $chapId == 110721 || $chapId == 280316 || $chapId ==324405 || $chapId ==326728 || $chapId ==320343){
                        $locale  = 'fr_NE';
                    }
                    
                    if($chapId == 276531 || $chapId ==320345){
                    	$locale  = 'fr_CI';
                    }
               
                    if($chapId == 136079){
                    	$locale  = 'pt';
                    }
                    
                    
                    if($chapId == 283006){
                    	$locale  = 'sr';
                    }
                    
                    if($chapId == 726355){
                    	$locale  = 'fa';
                    }
                    
                    if($chapId == 115189){ //For YCoins
                        $sessionCounty = new Zend_Session_Namespace('county');
                        $locale = ($sessionCounty->code == 'JP') ? 'jp' : 'en';  
                        
                    }
                }

                //If CHAP's language is not ENGLISH execute below
                //if (!empty($locale) && !is_null($locale) && $locale != 'en') {
                if (!empty($locale) && !is_null($locale)) {
                    $translate = new Zend_Translate(
                                    array(
                                        'adapter' => 'gettext',
                                        'content' => '../application/modules/partner/languages/' . $locale . '/' . $locale . '.mo',
                                        'locale' => $locale
                                    )
                    );

                    $registry = Zend_Registry::getInstance();
                    $registry->set('Zend_Translate', $translate);
                    $translate->setLocale($locale);
                }
                
                break;

            case 'partnermobile':
                
                //Get CHAP ID
                $themeMeta = new Model_ThemeMeta();
                $chapId = $themeMeta->getThemeByHostNameForPartner($_SERVER['SERVER_NAME']);
                


                //Get CHAP locale
                $modelUserLanguages = new Model_LanguageUsers;
                $locale = $modelUserLanguages->getLanguageCodeByChap($chapId) ? $modelUserLanguages->getLanguageCodeByChap($chapId) : 'en';               

                //If chap is multilingual
                $sessionLanguage = new Zend_Session_Namespace('languagesession');
                if($sessionLanguage->code){
                    $locale = $sessionLanguage->code;
                }
                else{
                    if($chapId == 81604 || $chapId == 274515 || $chapId == 585480 || $chapId == 585474  || $chapId == 282227 ){ //This is for temporary for qelasy
                        $locale  = 'fr';
                    }
                    //The French translations for Airtel Niger and Airtel Gabon is different than other french chap translations. So new language is created as fr2.
                    if($chapId == 274515 || $chapId == 110721 || $chapId == 280316 || $chapId ==324405 || $chapId ==326728 || $chapId ==320343 ){
                        $locale  = 'fr_NE';
                    }
                    
                    
                    if($chapId == 276531  || $chapId == 320345){
                    	$locale  = 'fr_CI';
                    }
                    
                    
                    
                    if($chapId == 136079){
                    	$locale  = 'pt';
                    }
                    
                    
                    if($chapId == 283006){
                    	$locale  = 'sr';
                    }
                    
                    
                    if($chapId == 115189){ //For YCoins
                        $sessionCounty = new Zend_Session_Namespace('county');
                        $locale = ($sessionCounty->code == 'JP') ? 'jp' : 'en';  
                        
                    }
                    
                    if($chapId == 726355){
                    	$locale  = 'fa';
                    }
                    
                }
                
                //If CHAP's language is not ENGLISH execute below
                if (!empty($locale) && !is_null($locale) && $locale != 'en') 
                {                   
                    $translate = new Zend_Translate(
                                                    array(
                                                            'adapter' => 'gettext',
                                                            'content' => '../application/modules/partnermobile/languages/' . $locale . '/' . $locale . '.mo',
                                                            'locale' => $locale
                                                        )
                                                    );

                    $registry = Zend_Registry::getInstance();
                    $registry->set('Zend_Translate', $translate);
                    $translate->setLocale($locale);
                } 
                else 
                {  
                    if($locale == 'en')
                    {
                        $translate = new Zend_Translate(
                                                        array(
                                                                'adapter' => 'gettext',
                                                                'content' => null,
                                                                'disableNotices' => true,
                                                                'locale' => $locale
                                                        )
                                                    );
                                           
                        $registry = Zend_Registry::getInstance();
                        $registry->set('Zend_Translate', $translate); 
                        $translate->setLocale($locale);
                    }
                                       
                }
                break;
            case 'cpbo':
                
                  //Get CHAP ID
                $themeMeta = new Model_ThemeMeta();
                $chapId = $themeMeta->getThemeByHostNameForPartner($_SERVER['SERVER_NAME']);
                 

                $modelUserLanguages = new Model_LanguageUsers;
                $locale = $modelUserLanguages->getLanguageCodeByChap($chapId);

                //If chap is multilingual
                $sessionLanguage = new Zend_Session_Namespace('languagesession');
                if ($sessionLanguage->code) {
                    $locale = $sessionLanguage->code;
                }
                    
                /* get language file */
                if (!empty($locale) && !is_null($locale)) {
                    $needle='';
                    $haystack1='';
                    $haystack2='';
                    $session = new Zend_Session_Namespace('chap'); 
                        if(isset($session->chap->id) && !empty($session->chap->id)) {
                                $needle = $session->chap->id; 
                       	   if( $_SERVER['SERVER_NAME'] == "testorangecp.nexva.com")  {
                                
                                $haystack1='585474';
                                $haystack2='585480';
                                
                       	   }
                                
                        }

                        $site=substr($_SERVER['REQUEST_URI'], 3);
                        if(isset($site) && !empty($site) && in_array($site, array('guinea_orange','drc_orange'))){
                            $needle=$site;
                            
                            if( $_SERVER['SERVER_NAME'] == "testorangecp.nexva.com")  {
                            
                               $haystack1='drc_orange';
                               $haystack2='drc_orange';
                            
                            }
                            
                      
                            
                        }
                       
                    if ((!empty($needle) && !empty($haystack1)) && in_array($needle, array($haystack1,$haystack2))) {
                        
                        $locale='fr';                        
                            $translate = new Zend_Translate(
                                array(
                                    'adapter' => 'gettext',
                                    'content' => '../application/modules/cpbo/languages/' . $locale . '/' . $locale . '.mo',
                                    'locale' => $locale
                                )
                            );
                        
                    }else{
                        if ($locale == 'en') {
                            $translate = new Zend_Translate(
                                    array(
                                        'adapter' => 'gettext',
                                        'content' => null,
                                        'disableNotices' => true,
                                        'locale' => $locale
                                        )
                            );
                        }
                    }
                    
                }
                 
                $registry = Zend_Registry::getInstance();
                $registry->set('Zend_Translate', $translate);
                $translate->setLocale($locale);
                Zend_Form::setDefaultTranslator($translate);
                break;
        }
    }
}