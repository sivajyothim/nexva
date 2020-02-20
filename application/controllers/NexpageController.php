<?php
class Default_NexpageController extends Nexva_Controller_Action_Web_MasterController{

    function init() {
        parent::init();
        $this->_helper->layout->setLayout('web/nexpage/default');
        
    }

    function indexAction() {
        
        $session = new Zend_Session_Namespace('np');
        if(isset($session->np))	{
        }
            else  {
            $neXpagerStats = new Model_StatisticNexpager();
            $neXpagerStats->updateNpStats($this->_request->cpid, 'web', null, null );
            $session->np = 1;
        }
        
        $this->__initLanguage();    
        
        $userMeta       = new Model_UserMeta();
        $compnayName    = $userMeta->getAttributeValue($this->_request->cpid, 'COMPANY_NAME');
        $activeNexpage = $userMeta->getAttributeValue($this->_request->cpid, 'ACTIVE_NEXPAGE');
     
		if ($activeNexpage) {
			$baseUrl = 'http://' . Zend_Registry::get ( 'config' )->nexva->application->base->url . "/" . $this->_request->id;
			$sessionLanguage = new Zend_Session_Namespace('application');

			$user = new Cpbo_Model_UserMeta ( );
			$user->setEntityId ( $this->_request->cpid );
			$this->view->cpDetails = $user;
			
			$this->view->headTitle ( 'neXpage - ' . $user->COMPANY_NAME );
			
			$this->view->cpname = $compnayName;
			$this->view->cpid = $this->_request->cpid;
			
			if ($this->getRequest()->isPost()) {
				$sessionLanguage->language_id = $this->_request->lang;
			}
	
            $this->view->selectedLanguage = $sessionLanguage->language_id;

			$languageTable = new Model_Language();
            $this->view->language   = $languageTable->getLanguageList(1);
            $defaultLanguage        = $languageTable->getDefaultLanguage();
            $this->view->isDefaultLang    = ($defaultLanguage->id == $sessionLanguage->language_id);
			
			// fetch products 
			$productsModel = new Model_Product ( );
			$products = $productsModel->getProductsOfSameUser ( $this->_request->cpid, 10, true );
			
			$productsCount = $productsModel->getProductsCountOfUser ( $this->_request->cpid, true );
			
			$productsDetails = '';
			$productNames    = 'neXva, nexva.com, ';
			$productIds      = array();
			
			$ns = new Zend_Session_Namespace('application');
			
			if (! is_null ( $products )) {
				foreach ( $products as $row ) {
					$productsDetails[]    = $productsModel->getProductDetailsById($row->id, false, $ns->language_id);
					$productIds[]          = $row->id;
				}
				
				if ($productsDetails) {
					foreach ( $productsDetails as $productDescription ) {
						$productNames .= ', ' . $productDescription ['name'];
					}
				}
				
				$rating     = new Model_Rating();
				$ratings    = $rating->getAverageRatingForManyProducts($productIds);
			}
			
			$data    = array(
			    'CP_NAME'       => $user->COMPANY_NAME,
                'CP_APP_COUNT'  => $productsCount 
			);
			$tags    = $this->__getPhraseTags($data);
			
	
            $this->view->headMeta()->appendName('keywords', $compnayName .', '.$productNames );
			$this->view->headMeta()->appendName('description', $compnayName);
			
			if ($products) {
				$this->view->products = $productsDetails;
			}
			$this->view->productsCount      = $productsCount;
            $this->view->nexpageStatus      = 1;
            $this->view->ratings            = $ratings; 
            $this->view->phraseTags         = $tags;
            
            
            /** Statistics **/
            $stats          = new Nexva_Analytics_NexpagerView();
            $language       = $languageTable->fetchRow('id = ' . $sessionLanguage->language_id);
            $opts   = array(
                'cp_id'         => $this->_request->cpid,
                'device_id'     => '0',
                'device_name'   => 'Web',
                'language_id'   => $sessionLanguage->language_id,
                'language_name' => $language->name,
            );    
            $stats->log($opts);
		
		} else    {
			$this->view->nexpageStatus = 0;
			$this->_redirect('http://'. Zend_Registry::get ( 'config' )->nexva->application->base->url . "/cp/".
                                $this->view->slug($compnayName).'.'. $this->_request->cpid.'.en');
			
		}
              
        $this->render('default/index');
    }
    
    /**
     * Updates language based on URL
     */
    private function __initLanguage() {
        //params key #3 holds the language
        $langCode     = $this->_getParam('lang', false);
        if ($langCode) {
            $langModel  = new Model_Language();
            $language   = $langModel->getLanguageIdByCode($langCode);
            if (isset($language['id'])) {
                $ns     = new Zend_Session_Namespace('application');
                $ns->language_id= $language['id'];
            }
        }      
    }
    
    function changelangAction() {       
        $id     = $this->_getParam('cpid', false);
        $lang   = $this->_getParam('lang', false);
        
        $langModel  = new Model_Language();
        $lang       = $langModel->find($lang);
        $code       = '';
        if (isset($lang[0]['id'])) {
            $ns             = new Zend_Session_Namespace('application');
            $ns->language_id= $lang[0]['id'];
            $code           = '.' . $lang[0]['code'];    
        }
        
        if ($id) {
            $this->_redirect('/np/' . $id . $code);           
        } else {
            $this->_redirect('/');
        }
    }
    
    
    
    function contactAction() {

        
        if($this->getRequest()->isXmlHttpRequest()) {
        	
        	$userModel = new Model_User();
            $userId = $this->_request->cpid;
            $user = $userModel->getUserDetailsById($userId);
            $userMeta = new Model_UserMeta();
            $userMeta->setEntityId($userId);

            $fisrtName = $userMeta->getAttributeValue($userId, 'FIRST_NAME');
            $lastName = $userMeta->getAttributeValue($userId, 'LAST_NAME');
        	
            if (empty($fisrtName)) {
                  $name = $user->email;
              } else {
                  $name = $fisrtName . ' ' . $lastName;
              }
             
            $mailer = new Nexva_Util_Mailer_Mailer();
            
            $config = Zend_Registry::get('config');
			
			$mailTemlateContentsStr = $str = file_get_contents ( '../application/views/layouts/mail-templates/generic_mail_template.phtml' );
			$mailer->addTo ( $user->email, $name );
			
			if (APPLICATION_ENV != 'production')
				$mailer->addBcc ( explode ( ',', $config->nexva->application->dev->contact ) );
			else
				$mailer->addBcc ( explode ( ',', $config->nexva->application->content_admin->contact ) );
			
			$mailer->setSubject ( "You've received feedback.." )
			       ->setBodyHtml ( $this->getMailBdy ( $name, $this->_request->name, $this->_request->email, $this->_request->msg, $mailTemlateContentsStr ) )
			       ->send ();
                   
                  
                   die();
        }
        $cpId   = $this->_request->cpid;
        $this->_redirect('/nexpage/index/' . $cpId);
    }
    
    function getResourceAction () {
        $resource   = $this->_request->resource;
        
        switch ($resource) {
            case 'twitter' :
                $cpid   = $this->_request->cpid;
                $this->__getTwitterStatus($cpid);
                break;
            default :
                
                break;
        }
        die(); //do nothing no view.
    }
    
    /**
     * This function returns the replaceable tags for a controller
     * 
     * @todo maybe move the storage of the values out of the controller
     */
    private function __getPhraseTags($data = array()) {
        $tags   = array(
            'CP_NAME'         => $data['CP_NAME'],
            'CP_APP_COUNT'    => $data['CP_APP_COUNT']
        );
        return $tags;
    }
    
    private function __getTwitterStatus($cpid) {
        $userMeta       = new Model_UserMeta();
        $tid    = $userMeta->getAttributeValue($cpid, 'TWITTER');


        //see if it's available in cache. otherwise get from site
        $frontendOptions = array(
           'lifetime' => 3600, // cache lifetime of 1 hours
           'automatic_serialization' => true
        );
        $tmp    = Zend_Registry::get('config')->nexva->application->tempDirectory;
        
        $backendOptions = array(
            'cache_dir' => $tmp
        );
        
        $cache = Zend_Cache::factory('Core',
                             'File',
                             $frontendOptions,
                             $backendOptions);
                             
        $cachekey       = 'CP_TWIT_STATUS_' . $cpid;
        if( ($tweettext = $cache->load($cachekey)) === false ) {
            $format     = 'json'; // set format
            $dataJson   = @file_get_contents("http://api.twitter.com/1/statuses/user_timeline/{$tid}.{$format}?count=1&include_rts=1"); 
            $tweet      = json_decode($dataJson); 
            $tweettext  = isset($tweet[0]->text) ? $tweet[0]->text : 'Could not retrieve tweets'; // show latest tweet
            $cache->save($tweettext, $cachekey);    
        } 
        
        $tweettext = preg_replace('/http:\/\/([a-z0-9_\.\-\+\&\!\#\~\/\,]+)/i', '<a href="http://$1" target="_blank">http://$1</a>', $tweettext);
        $tweettext = preg_replace('/@([A-Za-z0-9_]+)/', '<a href="http://twitter.com/$1" target="_blank">@$1</a>', $tweettext);
        echo $tweettext;
        
    }
    
    private function getMailBdy($cpname, $name, $email, $message,  $mailTemlateContentsStr) {
		
		$key [] = "<?=Zend_Registry::get('config')->nexva->application->base->url?>";
		$key [] = '<?=$this->layout()->content ?>';
		$imageUrl = Zend_Registry::get ( 'config' )->nexva->application->base->url;
		
		$mailContents = <<<EOD
 <br />
    Hi $cpname,  <br /><br />
    
    You have recieved a message from $name <b>( $email )</b> :<br /><br />
                            
    $message
                            
EOD;
		
		$bodytag = str_replace ( $key, array ($imageUrl, $mailContents ), $mailTemlateContentsStr );
		
		return $bodytag;
	}

	
	
	
}
