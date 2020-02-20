<?php
class Nexva_Controller_Action_Helper_CheckTheme extends Zend_Controller_Action_Helper_Abstract
{

    
    public function checkTheme()
    {

       
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) and $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        $parsedUrl  =   parse_url($pageURL);
        $host   =   $parsedUrl['host'];

        $whiteLabel =   substr($host, 0,strpos($host,'.'));


        $userModel      =    new Model_User();
        $whiteLabelUser =   current($userModel -> fetchAll("username = '".$whiteLabel."'")->toArray());



        if(($whiteLabelUser == true) and (count($whiteLabelUser)>0)){


            $theme  =   new Model_ThemeMeta();
            $theme->setEntityId($whiteLabelUser['id']);



            if($theme->WHITELABEL_ENABLED){
                
                    if(''!= $theme->LAYOUT){
                        return $theme;
                    }else{
                        return false;
                        
                    }
                }
            }
            return false;
    }

}


?>