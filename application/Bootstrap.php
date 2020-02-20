<?php
ini_set('display_errors', 'off');
        class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    public function __construct($application) {

        parent::__construct($application);    

        
    }

  protected function _initAutoload() {

  	Zend_Controller_Action_HelperBroker::addPrefix('Nexva_Controller_Action_Helper');

    $autoloader = new Zend_Application_Module_Autoloader(
            array(
              'namespace' => '',
              'basePath' => APPLICATION_PATH,
              'resourceTypes' => array(
                'form' => array(
                  'path' => 'forms',
                  'namespace' => 'Form',
                ),
                'model' => array(
                  'path' => 'models',
                  'namespace' => 'Model',
                ),
              )
            )
    );
		


    return $autoloader;
  }
  
       

  protected function _initLayoutHelper() {
    $this->bootstrap('frontController');
    $layout = Zend_Controller_Action_HelperBroker::addHelper(new Nexva_Controller_Action_Helper_LayoutLoader());
  }

  protected function _initApplication() {
      
      $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH. "/../logs/nexva_ua.log");
      $logger = new Zend_Log($writer);
      
      $logger->info(@$_SERVER['HTTP_USER_AGENT'] . ' --- '.@$_SERVER['REMOTE_ADDR'].' || ');     
      
    // @todo: use Zend_Cache to cache $config (to disk? memory?) to gain performance
    $config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);

    Zend_Registry::set("config", $config);

    /**
     * Legacy Issues:
     *
     * Issue 1: v1 mobile site was at m.nexva.com. This is no longer the case in v2 (nexva.mobi) and therefore
     *          we will transparenly redirect them to nexva.mobi.
     *
     * Issue 2: Legacy launchers for white-labels was in the format: m.nexva.com?theme=bb (bestbuy)
     *          We will catch this and send the request to new mobile site: (bb.nexva.com)
     *
     * @todo: Since the request object is not yet initialized at this point, I'm splitting HTTP_HOST.
     *        Check if there is a better way to do this.
     *
     */
    if (isset($_SERVER['HTTP_HOST'])) {
      $subdomain = explode(".", $_SERVER['HTTP_HOST']);
      $subdomain = strtolower($subdomain[0]);

      if ($subdomain == "m") {
        $url = $config->nexva->application->mobile->url;

        if ($_SERVER['QUERY_STRING'] != "") {
          $parts = explode("=", $_SERVER['QUERY_STRING']);
          if ("theme" == $parts[0])
            $url = $parts[1] . "." . $config->nexva->application->mobile->url;
        }

        header("location: http://" . $url);
        exit();
      }
    }
    
 

    $logger = new Zend_Log();

    $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH. "/../logs/nexva.".APPLICATION_ENV.".log");
      //Zend_Debug::dump($writer);die();
    $logger->setEventItem('ua', @$_SERVER['HTTP_USER_AGENT'] );
    $logger->setEventItem('ip', @$_SERVER['REMOTE_ADDR']);

    $format = "%timestamp% %priorityName% (%priority%) %ip% (%ua%): %message%" . PHP_EOL;

    $formatter = new Zend_Log_Formatter_Simple($format);
    $writer->setFormatter($formatter);

    $logger->addWriter($writer);

    Zend_Registry::set('logger', $logger);


    //Init local env setting.Zend locale will pick the local parameter from browser(language)
    $locale = new Zend_Locale();
    Zend_Registry::set('Zend_Locale', $locale);

    //init DB connection
    try {

        $this->bootstrap('multidb');
        $multiDb = $this->getPluginResource('multidb');
        
        
      //  if($_SERVER['SERVER_NAME'] == 'appstore.mtn.ci' or $_SERVER['SERVER_NAME'] == 'nextapps.mtnonline.com') {
        
        $db = $multiDb->getDb('default');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $db->getConnection();

        $dbWurfl = $multiDb->getDb('default');
        $dbWurfl->setFetchMode(Zend_Db::FETCH_OBJ);
        $dbWurfl->getConnection();
        
       // } else {
            
          //  $db = $multiDb->getDb('default2');
          //  $db->setFetchMode(Zend_Db::FETCH_OBJ);
          //  $db->getConnection();
            
         //   $dbWurfl = $multiDb->getDb('wurfl');
        //    $dbWurfl->setFetchMode(Zend_Db::FETCH_OBJ);
         //   $dbWurfl->getConnection();
      //  }

//        $dbCrawler = $multiDb->getDb('crawler');
//        $dbCrawler->setFetchMode(Zend_Db::FETCH_OBJ);
//        $dbCrawler->getConnection();
        
        $cache      = new Nexva_Cache_Base(); //setting the default cache object in session

        Zend_Registry::set('db', $db);
        Zend_Registry::set('db_wurfl', $dbWurfl);
        //Zend_Registry::set('db_crawler', $dbCrawler);
        Zend_Registry::set('cache',  $cache);

        //Just making everything UTF8. This is a hack, need to find the proper Zend way
        $db     = Zend_Registry::get('db');
        $db->query('SET NAMES "utf8"')->execute();
        
        

      //  
        

    } catch (Zend_Db_Adapter_Exception $e) {
      //@todo: die() sux. Come up with a better solution to handle this gracefully - Zend_Log to log to system and Zend_Mailer to fire off an email?
     // die("Error connecting to database: " . $e->getMessage());
      
      die('<!doctype html>
<title>Site Maintenance</title>
<style>
  body { text-align: center; padding: 150px; }
  h1 { font-size: 50px; }
  body { font: 20px Helvetica, sans-serif; color: #333; }
  article { display: block; text-align: left; width: 650px; margin: 0 auto; }
  a { color: #dc8100; text-decoration: none; }
  a:hover { color: #333; text-decoration: none; }
</style>
 
<article>
    <h1>We&rsquo;ll be back soon!</h1>
    <div>
        <p>Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. If you need to you can always <a href="mailto:chathura@nexva,com">contact us</a>, otherwise we&rsquo;ll be back online shortly!</p>
        <p>&mdash; The Team</p>
    </div>
</article>');
        }
    
    /**
     * Registering the whitelabel helper for the main web module
     */
    
    Zend_Controller_Action_HelperBroker::addHelper(
        new Nexva_Controller_Action_Helper_WebWhitelabelMasked()
    );
    

    
  }

  public function _initErrorHandlers() {
    $front = Zend_Controller_Front::getInstance();
    $front->registerPlugin(new Nexva_Controller_Plugin_Utilities());
    
    /*
      $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(
      array(
      'module'     => 'mobile',
      'controller' => 'error',
      'action'     => 'error'
      ),
      array(
      'module'        => 'cpbo',
      'controller'    => 'error',
      'action'        => 'error'
      )


      //CP, Web, Admin will use the default error template

      ));
     *
     */
  }

  public function _initRoutes() {


      
    $router = Zend_Controller_Front::getInstance()->getRouter();
    $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', APPLICATION_ENV);      
    $router->addConfig($config, 'resources');
      
      
    $routes['featured'] = new Zend_Controller_Router_Route(
            'featured/:id',
            array('controller' => 'index', 'action' => 'featured', 'id' => null)
    );


    $routes['category'] = new Zend_Controller_Router_Route_Regex(
            "category/([A-Za-z-0-9]+)/([0-9]+)(/page/([0-9]*))?",
            array('controller' => 'category', 'action' => 'index', 'device_id' => null), array(1 => 'slug', 2 => 'id', 4 => 'page'),
            'category/%s/%d/page/%d'
    );
    
   $routes['category_devices'] = new Zend_Controller_Router_Route_Regex(
            "category/([-A-Za-z-0-9]+)/([0-9]+)/([0-9]+)(/page/([0-9]*))?",
            array('controller' => 'category', 'action' => 'index', 'device_id' => null), array(1 => 'slug', 2 => 'id', 5 => 'page' , 3 => 'device_id'),
            'category/%s/%d/%d/page/%d'
    );


   $routes['app'] = new Zend_Controller_Router_Route_Regex(
   		"app/([A-Za-z-0-9]+).([0-9]+)(?:.([A-Za-z]+))?(?:.([A-Za-z]+))?(?:.([0-9]+))?",
   		array('controller' => 'app', 'action' => 'index', 'id' => null, 'language' => 'en', 'preview' => false),
   		array(1 => 'slug', 2 => 'id', 3 => 'language', 4 => 'preview', 5 => 'uid')
   );
   

    

    
    $routes['app_b'] = new Zend_Controller_Router_Route_Regex(
    		"app/show/id/([0-9]+)",
    		array('controller' => 'app', 'action' => 'index', 'id' => null, 'language' => 'en', 'preview' => false),
    		array(1 => 'id', 2 => 'slug')
    );
    


    

    /* http://nexva.com/1234.en */
    $routes['app_shorturl'] = new Zend_Controller_Router_Route_Regex(
            "([0-9]+)(?:.([A-Za-z-_]+))?",
            array('controller' => 'app', 'action' => 'index', 'id' => null, 'language' => 'en'),
            array(1 => 'id', 2 => 'language')
    );

    /*
    $routes['qrcode'] = new Zend_Controller_Router_Route_Regex(
            "qrcode/([0-9]+)(?:/h/([0-9]+))?(?:/w/([0-9]+))?",
            array('controller' => 'app', 'action' => 'qrcode', 'id' => null, 'height' => 125, 'width' => 125),
            array(1 => 'id', 2 => 'height', 3 => 'width')
    );
     *
     */

    $routes['qrcode'] = new Zend_Controller_Router_Route(
            "qrcode/:id/*",
            array('controller' => 'app', 'action' => 'qrcode', 'id' => null)
    );



    $routes['search'] = new Zend_Controller_Router_Route(
            'search/:q',
            array('controller' => 'search', 'action' => 'index', 'q' => null)
    );
    
    $routes['buy'] = new Zend_Controller_Router_Route(
            'buy/:id',
            array('controller' => 'app', 'action' => 'buy', 'id' => null, 'page' => null)
    );
    // Share
    $routes['share'] = new Zend_Controller_Router_Route(
            'share/:id',
            array('controller' => 'index', 'action' => 'share', 'id' => null)
    );
    // Badge
    $routes['badge'] = new Zend_Controller_Router_Route(
            'badge/:id/:s/:template/:chap',
            array('controller' => 'index', 'action' => 'badge', 'id' => null, 's' => null, 'template' => null, 'chap' => null)
    );

    // cpid.en/page/id
    $routes['cp'] = new Zend_Controller_Router_Route_Regex(
            "cp/([0-9]*)((\.([A-Za-z]*))*)(/page/([0-9]*))?",
            array('controller' => 'cp', 'action' => 'index', 'cpid' => null, 'language' => 'en', 'page' => '1'),
            array(1 => 'cpid', 2 => 'language', 6 => 'page'),
            'cp/%s.%s/page/%s'
    );


    // cpname.cpid.en/page/id
    $routes['cpbyname'] = new Zend_Controller_Router_Route_Regex(
            "cp/([A-Za-z0-9_-]*)\.([0-9]*)((\.([A-Za-z]*))?)(/page/([0-9]*))?",
            array('controller' => 'cp', 'action' => 'index', 'cpname' => null, 'cpid' => null, 'language' => 'en', 'page' => '1'),
            array(1 => 'cpname', 2 => 'cpid', 3 => 'language', 7 => 'page'),
            'cp/%s.%s.%s/page/%s'
    );

    // Facebook App
    $routes['facebook'] = new Zend_Controller_Router_Route(
            'facebook/tab',
            array('controller' => 'facebook', 'action' => 'index')
    );
    
    // nexlinker. :if indicates whether it's iframe or not
    $routes['nexlinker'] = new Zend_Controller_Router_Route_Regex(
            'nl/([0-9]+)(\.([a-z]*))?(?:/([0-9]+))?(?:/([0-9]+))?',
            array('controller' => 'nexlinker', 'action' => 'index', 'id' => null, 'chap' => null, 'if' => null),
            array(1 => 'id', 3 => 'lang', 4 => 'chap', 5 => 'if')
    );
    
    // nexlinker. :if indicates whether it's iframe or not
    $routes['nexlinkerDiscount'] = new Zend_Controller_Router_Route_Regex(
            'nld/([a-zA-Z0-9]+)(\.([a-z]*))?(?:/([0-9]+))?',
            array('controller' => 'nexlinker', 'action' => 'discount', 'id' => null, 'if' => null),
            array(1 => 'id', 3 => 'lang', 4 => 'if')
    );

    /* http://nexva.com/np/1234.companyname */
    $routes['nexpage'] = new Zend_Controller_Router_Route_Regex(
          "np/([0-9]+)(\.([A-Za-z0-9_-]*))*",
            array('controller' => 'nexpage', 'action' => 'index', 'id' => null),
            array(1 => 'cpid', 3 => 'lang')
    );
    
    
    Zend_Controller_Front::getInstance()->getRouter()->addRoutes($routes);
  }
  
  

  

    protected function _initZFDebug() { 
        
        
        // add this as init fucntion 
        
        if( !function_exists('apache_request_headers') ) {
        	///
        	function apache_request_headers() {
        		$arh = array();
        		$rx_http = '/\AHTTP_/';
        		foreach($_SERVER as $key => $val) {
        			if( preg_match($rx_http, $key) ) {
        				$arh_key = preg_replace($rx_http, '', $key);
        				$rx_matches = array();
        				// do some nasty string manipulations to restore the original letter case
        				// this should work in most cases
        				$rx_matches = explode('_', $arh_key);
        				if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
        					foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
        					$arh_key = implode('-', $rx_matches);
        				}
        				$arh[$arh_key] = $val;
        			}
        		}
        		return( $arh );
        	}
        	
        }
        
        
        
        
        
        
    //  Enabling this method seems to break autocomplete. Use only when needed  
  
/*         if($_SERVER['REMOTE_ADDR'] == '113.59.222.81'){
            

                $autoloader = Zend_Loader_Autoloader::getInstance ();
                $autoloader->registerNamespace ( 'ZFDebug' );

                $db = Zend_Registry::get ( 'db' );

                $cache = Zend_Cache::factory ( 'Core', 'File' );

                Zend_Controller_Front::getInstance()->getBaseUrl();
                //APPLICATION_PATH
                $options = array ('plugins' => array ('Variables', 'Database' => array ('adapter' => $db ), 'File' => array ('basePath' => Zend_Controller_Front::getInstance ()->getBaseUrl () ), 'Memory', 'Time', 'Registry', 'Cache' => array ('backend' => $cache->getBackend () ), 'Exception' ) );

                $debug = new ZFDebug_Controller_Plugin_Debug ( $options );

                $this->bootstrap ( 'frontController' );
                $frontController = $this->getResource ( 'frontController' );
                $frontController->registerPlugin ( $debug ); 
                }  
 */
       
        
    }
    
    
    
    protected function _initLanguages()
    {
    
    
        // Don't run this, if this is cli or api.nexva.com call
        // strcmp(substr($_SERVER['SERVER_NAME'],0,4), 'api.') !== 0  check for the api.nexva.com is requested..
    
        if (php_sapi_name() != 'cli') {
    
            if(strcmp(substr($_SERVER['SERVER_NAME'],0,4), 'api.') !== 0 ) {
    
                $sessionLanguage = new Zend_Session_Namespace('application');
                //Zend_Debug::dump($sessionLanguage);die();
                if (empty($sessionLanguage->language_id)) {
                    $languageDetection = new Nexva_LanguageDetection_LanguageDetection();
                    // returns the language code {en, fr, it etc.. }
                    $languageCode = $languageDetection->detectLanguage();
                    //Zend_Debug::dump($languageCode);die();
                    if (!$languageCode) {
                        $gap    = "\n===================LANG ERROR======================\n";
                        $message    = print_r($languageCode, true) . "\n\n" . print_r($_SERVER, true);
                        Zend_Registry::get('logger')->err($gap . $message . $gap);
                    }
    
                    // get the language id
                    $modelLanguage = new Model_Language();
                    $languageId = $modelLanguage->getLanguageIdByCode($languageCode);
                    $defaultLang = $modelLanguage->getDefaultLanguage();
                    $sessionLanguage->defaultLangId = $defaultLang->id;
                    $sessionLanguage->language_id   = (isset($languageId->id)) ? $languageId->id : $defaultLang->id;
                    $sessionLanguage->languageId    = $sessionLanguage->language_id; //Just so people coding with the correct standard aren't fucked
      
                }
            }
    
        }
    

    
    }
    
    /**
     * By : Sudha 
     * On : 2012-04-25
     * Purpose : Enable localized text display
     * Description : Introduced to load and share gettext based language translation text throughout the application.
     *               To use the translated text, declare a variable in any .phtml file e.g: $temp=Zend_Registry::get('browserLocale'); 
     *               and then use whereever required inside .phtml file. e.g: <?= $temp->_('ooooooooooo') ?>
     */
    
   /* 
    public function _initGetLocaleFromBrowserRequest() {
        
        try {
            $locale = new Zend_Locale('browser');
        } catch (Zend_Locale_Exception $e) {
            $locale = new Zend_Locale('en-US');
        }
        $registry = Zend_Registry::getInstance();
        $registry->set('Zend_Locale', $locale);
        try 
        {
            $t = new Zend_Translate('gettext', APPLICATION_PATH . '/../lang/',
                            null,
                            array('scan' => Zend_Translate::LOCALE_FILENAME,
                                'disableNotices' => true
                            )
            );
            
            Zend_Registry::set('browserLocale', $t);
        } 
        
        catch (Zend_Locale_Exception $e) 
        {
            $registry->set('Zend_Locale', 'en-US');
            $t = new Zend_Translate('gettext', APPLICATION_PATH . '/../lang/', null, array('scan' => Zend_Translate::LOCALE_FILENAME));
            Zend_Registry::set('browserLocale', $t);
        }
    }
    * */

    /**
     * Set up autoloading
     */
    public function _initMyDiffAutoloader()
    {
        //require_once "Zend/Loader/Autoloader.php";
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('MyDiff_');

    }
    
    private function is_bot($user_agent) {
    	return preg_match('/(abot|dbot|ebot|hbot|kbot|lbot|mbot|nbot|obot|pbot|rbot|sbot|tbot|vbot|ybot|zbot|bot\.|bot\/|_bot|\.bot|\/bot|\-bot|\:bot|\(bot|crawl|slurp|spider|seek|accoona|acoon|adressendeutschland|ah\-ha\.com|ahoy|altavista|ananzi|anthill|appie|arachnophilia|arale|araneo|aranha|architext|aretha|arks|asterias|atlocal|atn|atomz|augurfind|backrub|bannana_bot|baypup|bdfetch|big brother|biglotron|bjaaland|blackwidow|blaiz|blog|blo\.|bloodhound|boitho|booch|bradley|butterfly|calif|cassandra|ccubee|cfetch|charlotte|churl|cienciaficcion|cmc|collective|comagent|combine|computingsite|csci|curl|cusco|daumoa|deepindex|delorie|depspid|deweb|die blinde kuh|digger|ditto|dmoz|docomo|download express|dtaagent|dwcp|ebiness|ebingbong|e\-collector|ejupiter|emacs\-w3 search engine|esther|evliya celebi|ezresult|falcon|felix ide|ferret|fetchrover|fido|findlinks|fireball|fish search|fouineur|funnelweb|gazz|gcreep|genieknows|getterroboplus|geturl|glx|goforit|golem|grabber|grapnel|gralon|griffon|gromit|grub|gulliver|hamahakki|harvest|havindex|helix|heritrix|hku www octopus|homerweb|htdig|html index|html_analyzer|htmlgobble|hubater|hyper\-decontextualizer|ia_archiver|ibm_planetwide|ichiro|iconsurf|iltrovatore|image\.kapsi\.net|imagelock|incywincy|indexer|infobee|informant|ingrid|inktomisearch\.com|inspector web|intelliagent|internet shinchakubin|ip3000|iron33|israeli\-search|ivia|jack|jakarta|javabee|jetbot|jumpstation|katipo|kdd\-explorer|kilroy|knowledge|kototoi|kretrieve|labelgrabber|lachesis|larbin|legs|libwww|linkalarm|link validator|linkscan|lockon|lwp|lycos|magpie|mantraagent|mapoftheinternet|marvin\/|mattie|mediafox|mediapartners|mercator|merzscope|microsoft url control|minirank|miva|mj12|mnogosearch|moget|monster|moose|motor|multitext|muncher|muscatferret|mwd\.search|myweb|najdi|nameprotect|nationaldirectory|nazilla|ncsa beta|nec\-meshexplorer|nederland\.zoek|netcarta webmap engine|netmechanic|netresearchserver|netscoop|newscan\-online|nhse|nokia6682\/|nomad|noyona|nutch|nzexplorer|objectssearch|occam|omni|open text|openfind|openintelligencedata|orb search|osis\-project|pack rat|pageboy|pagebull|page_verifier|panscient|parasite|partnersite|patric|pear\.|pegasus|peregrinator|pgp key agent|phantom|phpdig|picosearch|piltdownman|pimptrain|pinpoint|pioneer|piranha|plumtreewebaccessor|pogodak|poirot|pompos|poppelsdorf|poppi|popular iconoclast|psycheclone|publisher|python|rambler|raven search|roach|road runner|roadhouse|robbie|robofox|robozilla|rules|salty|sbider|scooter|scoutjet|scrubby|search\.|searchprocess|semanticdiscovery|senrigan|sg\-scout|shai\'hulud|shark|shopwiki|sidewinder|sift|silk|simmany|site searcher|site valet|sitetech\-rover|skymob\.com|sleek|smartwit|sna\-|snappy|snooper|sohu|speedfind|sphere|sphider|spinner|spyder|steeler\/|suke|suntek|supersnooper|surfnomore|sven|sygol|szukacz|tach black widow|tarantula|templeton|\/teoma|t\-h\-u\-n\-d\-e\-r\-s\-t\-o\-n\-e|theophrastus|titan|titin|tkwww|toutatis|t\-rex|tutorgig|twiceler|twisted|ucsd|udmsearch|url check|updated|vagabondo|valkyrie|verticrawl|victoria|vision\-search|volcano|voyager\/|voyager\-hc|w3c_validator|w3m2|w3mir|walker|wallpaper|wanderer|wauuu|wavefire|web core|web hopper|web wombat|webbandit|webcatcher|webcopy|webfoot|weblayers|weblinker|weblog monitor|webmirror|webmonkey|webquest|webreaper|websitepulse|websnarf|webstolperer|webvac|webwalk|webwatch|webwombat|webzinger|wget|whizbang|whowhere|wild ferret|worldlight|wwwc|wwwster|xenu|xget|xift|xirq|yandex|yanga|yeti|yodao|zao\/|zippp|zyborg|\.\.\.\.)/i', $user_agent);
    }
    

}

