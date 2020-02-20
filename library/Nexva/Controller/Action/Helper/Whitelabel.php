<?php
class Nexva_Controller_Action_Helper_Whitelabel extends Zend_Controller_Action_Helper_Abstract {
    
    /**
     * Default keys that were taken from the old WLT theme system
     * These MUST be present
     */
    private $__defaultKeys  = array(
            'basic'     => array(
                'sitename'  => '', 
                'css'       => ''
            ),
            'header'    => array(
                'logo'  => array(
                    'path'  => '',
                    'alt'  => ''
                )
            ),
            'content'   => array(
                'menu'  => ''
            ),
            'footer'    => array(
                'site'  => '',
                'menu'  => ''
            ),
            'buttons'   => array(
                'buy'  => '',
                'download'  => ''
            )
        );
    
    /**
     * Constructor: initialize plugin loader
     * @return void
     */
    public function __construct() {
        $this->pluginLoader = new Zend_Loader_PluginLoader();
    }
    
    /**
     * Initially whitelabels were stored in .wlt files and read through Zend_Config_Ini()
     * Whitelabels are now stored in DB and this plugin exposes the same data from a different 
     * source so that old code doesn't break.
     * 
     * This method will always try to return a proper conf file. If it fails, 
     * it means all avenues have been tried and failed so it die()'s
     * 
     * @param $wltName
     */
    public static function getConf($wltName = null) {
        if (!$wltName) {
            /**
             * @todo make this a catchable exception that redirects to a nice page _JP 
             */ 
            throw new Exception('Could not load theme file', 'WL_000');   
        }
        
        $pathToFile     = APPLICATION_PATH . '/modules/mobile/whitelabels/' . $wltName . '.wlt';
        $conf           = null;
        //see if you can load from DB first
        if (($conf = self::loadConfFromDb($wltName)) !== false) {
            return $conf;
        }
        if (is_file($pathToFile)) {
            $conf   = new Zend_Config_Ini($pathToFile, null);
        } else {
            if (($conf = self::loadConfFromDb('nexva')) !== false) { //last ditch attempt
                self::sendThemeLoadFailureMail($wltName, false);
                return $conf; 
            }
            
            $pathToFile     = APPLICATION_PATH . '/modules/mobile/whitelabels/nexva.wlt';
            if (is_file($pathToFile)) {
                $conf       = new Zend_Config_Ini($pathToFile, null);
                self::sendThemeLoadFailureMail($wltName, false);
            } else {
                // if even nexva.wlt doesn't load we're done for
                self::sendThemeLoadFailureMail($wltName, true);
            }
        }
        return $conf;
    }
    
    /**
     * Loads the conf from settings in the DB. This is the preferred way for newwer themes.  
     */
    public static function loadConfFromDb($themeName) {
        $themeMeta  = new Model_ThemeMeta();       
        $row        = $themeMeta->fetchRow('meta_name = "WHITELABLE_THEME_NAME" AND meta_value = "' .  mysql_escape_string($themeName) . '"');
        if (!is_null($row) || !empty($row)) {
            $userId     = $row['user_id'];
            $themeMeta->setEntityId($userId);
            $themeData  = $themeMeta->getAll();//this is a cached call so it's fine. We need this later on
            $themeData->USER_ID = $userId;
            if (empty($themeData->WHITELABEL_THEME)) {
                return false;
            } else {
                //try to load the serialized array to simulate _conf
                $conf   = unserialize($themeData->WHITELABEL_THEME);
                if (empty($conf)) {
                    return false;
                } else {
                    //set the paths and the defaults
                    $wlPath     = "/mobile/whitelables/{$themeName}/";
                    $nxPath     = "/mobile/whitelables/nexva/"; //it's a nice default
                    $conf['basic']['css']               = $wlPath . $conf['basic']['css'];
                    $conf['header']['logo']['path']     = $wlPath . $conf['header']['logo']['path'];
                    //$conf['buttons']['download']        = (trim($conf['buttons']['download'])) ? $wlPath . $conf['buttons']['download'] : $nxPath . 'nexva_download.gif';
                    //$conf['buttons']['buy']             = (trim($conf['buttons']['buy'])) ? $wlPath . $conf['buttons']['buy'] : $nxPath . 'nexva_buy.gif';
                    return new Zend_Config($conf);
                }
            }
        } else {
            return false;
        }
    }
    
    private function sendThemeLoadFailureMail($themeName = '', $exit = false) {
        $msg    = print_r($_SERVER, true);
        $msg    .= "Tried to load theme {$themeName} but couldn't find theme definitition. Defaulted to nexva" . $msg;
        $mailer     = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('CRITICAL! Loading mobile theme failed. Whitelabel.php');
        $mailer->addTo(explode(',', Zend_Registry::get('config')->nexva->application->dev->contact))
            ->setLayout("generic_mail_template")     
            ->setMailVar("error", $msg);
            
        if (APPLICATION_ENV == 'production') {
            $mailer->sendHTMLMail('error_report.phtml');  
        } else {
            echo $mailer->getHTMLMail('error_report.phtml');
            echo "die is set to ";
            var_dump($exit);
            die();
        }
        
        if ($exit) {
            die("Something seems to be wrong, our developers have already been notified.");
        }
    }
}