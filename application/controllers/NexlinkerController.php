<?php
/**
 * Controller for all things nexlinker
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Jan 14, 2011
 */
class Default_NexlinkerController extends Zend_Controller_Action {

    function indexAction() { 
        

        
      
        
        $isIframe       = $this->_request->getParam('if', false);
        $id             = $this->_request->id;
       
        $chapId         = $this->_request->getParam('chap', false); 
        
        if (!$isIframe) {
            //redirect to product page
            $this->_redirect('/app/index/id/' . $id);
        }
        
        $this->__initLanguage();
        
        $isChap         = false;
        if ($chapId) {
            $themeMeta  = new Model_ThemeMeta();
            $themeMeta->setEntityId($chapId);
            $themeData  = $themeMeta->getAll();
            if (isset($themeData->WHITELABLE_SITE_NAME)) {
                $isChap     = true;
                $this->view->chapInfo   = $themeData;
                $this->view->chapId     = $chapId;        
            }
        }
        
        $this->_helper->layout->setLayout('web/popup');
        //load up product details
        $appModel   = new Model_Product();
        $sessionLanguage = new Zend_Session_Namespace('application');
        $product    = $appModel->getProductDetailsById($id, true, $sessionLanguage->language_id);
        
        if (!$product) {
            $this->_redirect('/');
        }
        
        /** analytics **/
        $stats          = new Nexva_Analytics_ProductView();
        $loggedDevice   = array();
        $loggedDevice['0'] = 'Web';
        $opts           = array(
            'app_id'        => $id, 
            'device_id'     => $loggedDevice,
            'cp_id'         => $product['uid']
        );
        $stats->log($opts);
        
        $this->view->isChap = $isChap;
        $this->view->prod   = $product;
        $this->view->flashMessages  = $this->_helper->FlashMessenger->getMessages();
        
    }
    
     public function discountAction() {
        $isIframe       = $this->_request->getParam('if', false);
        $code           = $this->_request->id;
        $chapId         = $this->_request->getParam('chap', false);
        if (!$isIframe) {
            //redirect to product page
            $this->_redirect('/app/index/id/' . $id);
        }
        $this->_helper->layout->setLayout('web/popup');
        
        $promocodeModel = new Model_PromotionCode();
        $promocode      = $promocodeModel->getPromotionCode($code, true, true);
        
        if (isset($promocode['products'][0])) {
            $appModel   = new Model_Product();
            $sessionLanguage = new Zend_Session_Namespace('application');
            $product    = $appModel->getProductDetailsById($promocode['products'][0]->id, true, $sessionLanguage->language_id);
            $this->view->prod   = $product;    
        }
        
        if (isset($promocode['chap_id'])) {
            $themeMeta      = new Model_ThemeMeta();
            $themeMeta->setEntityId($promocode['chap_id']);
            $this->view->chapData  = $themeMeta->getAll();
        }
        
        $this->view->promotion  = $promocode;
     }
    
    function sendmailAction() {
        $appId  = (int) $this->_request->getParam('appId', 0);
        $email  = $this->_request->getParam('email', false);
        $chapId = (int) $this->_request->getParam('chapId', 0);
        $config = Zend_Registry::get("config");
        $promoId    = $this->_request->getParam('promo', false);
        
        if (!($appId && $email)) {
            $this->__echoError('The email could not be sent. Please refresh your page and try again.');
        } 
        
        $appModel   = new Model_Product();
        $product    = $appModel->getProductDetailsById($appId);
        
        if (!$product) {
            $this->__echoError('This product does not exist. Please check the URL and try again.');
        } 
        
        $promocode      = null;
        if ($promoId) {
            $promocodeModel = new Model_PromotionCode();
            $promocode      = $promocodeModel->getPromotionCode($promoId, true, false);
        }
        
        
        
        
        $chapData       = null;
        if (isset($promocode['chap_id'])) {
            $themeMeta      = new Model_ThemeMeta();
            $themeMeta->setEntityId($promocode['chap_id']);
            $chapData  = $themeMeta->getAll();
        }
        //modified - 13/03/2012 
        else if($chapId > 0)
        {
            $themeMeta      = new Model_ThemeMeta();
            $themeMeta->setEntityId($chapId);
            $chapData  = $themeMeta->getAll();
            
            //set whitle label settings
            $whitelLabelSettings = (isset($chapData->WHITELABLE_SETTINGS)) ? json_decode($chapData->WHITELABLE_SETTINGS) : new stdClass();        
        }    
                
        $data   = array();
        $data['isShare']    = false;
        $data['message']    = 'An email has been sent with instructions on how to download this application.';    
        $template           = 'nexlinker_sendapp.phtml';
        $friend             = '';
        $subject            = 'Download options for ' . utf8_decode($product['name']);
        if ($this->_request->getParam('share', 'false') != 'false') {
            $data['isShare']    = true;
            $friend             = $this->_request->getParam('sender');     
            $template           = 'nexlinker_sendapp_share.phtml';
            $data['message']    = 'An email has been sent to your friend with instructions on how to download this application.';
            $subject            = $friend . ' shared ' . utf8_decode($product['name']) . ' with you';          
        }
        
        $mailer     = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject($subject);        
        $mailer->addTo($email)
            ->setMailVar("prod", $product)
            ->setMailVar("email", $email)
            ->setMailVar('friend', $friend)
            ->setMailVar('promotion', $promocode)
            ->setMailVar('company_name', 'neXva')
            ->setMailVar("baseurl", Zend_Registry::get('config')->nexva->application->base->url)
            ->setMailVar("mobileurl", Zend_Registry::get('config')->nexva->application->mobile->url)
            ->setMailVar("isChap", FALSE);
            
        if ($chapData) 
        {
            $mailer->setMailVar("mobileurl", $chapData->WHITELABLE_URL)
                    ->setMailVar("isChap", TRUE)
                    ->setMailVar('company_name', 'Momaco');
            
            if(!empty($whitelLabelSettings->from_email))
            {
                $mailer->setFrom($whitelLabelSettings->from_email);
            }
            
        } 
        
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);  
        //echo $mailer->getHTMLMail($template);
        
        
        $this->__echoResponse($data);
    }
    
   
    
    private function __echoResponse($data = array()) {
        //get callback and attach
        $data['callback']   = $this->_request->getParam('callback');
        $data['message']    = (isset($data['message'])) ? $data['message'] : '';
        $data['error']      = '';
        echo json_encode($data);
        die();
    }
    
    private function __echoError($error = '') {
        $data   = array();
        $data['callback']   = $this->_request->getParam('callback');
        $data['error']      = $error; 
        $data['message']    = '';
        
        echo json_encode($data);
        die();
    }
    
    /**
     * Updates language based on URL
     */
    private function __initLanguage() {
        
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

    public function oembedAction() {
        
        //figure out the size - if the 'size' param is not present, the default size would be 'large'
        switch(strtolower($this->_request->size)) {
            case "large":
            case "medium":
            case "small":            
            case "qr":
                $size = $this->_request->size;
                break;
            default:
                $size = "large";
        }

        //Assuming that 'url' is always a short url (http://nexva.com/XXXX), this is a hacky-wacky-smacky way of parsing for the id of the product.
        
        $url = parse_url(urldecode($this->_request->url));

        $pos = stripos($url['path'], '&');

        if( !$pos )
            $id = substr($url['path'], 1, strlen($url['path'])) ;
        else
            $id = substr($url['path'], 1, $pos-1) ;
                   
        $html = '
            <!-- neXlinker START -->
	            <span class="nexlinker" id="nexlinker_badge_'.$id.$size.'nexva"></span>
	            <script type="text/javascript">
	            	var _nxs = _nxs || [];var _nx = new Object();_nx.aid = "'.$id.'";_nx.s = "'.$size.'";_nx.b = "nexva";_nx.h = "nexva.com";
	            	_nxs.push(_nx);</script><script type="text/javascript" src="http://nexva.com/web/nexlinker/nl.js">
            	</script>
            <!-- neXlinker END -->
        ';

        $html = trim($html);
        $html  = str_replace('"', '\"', $html);
        
        $output = '
                {
                    "type":"rich",
                    "html":"'.$html.'"
                }
        ';

        $this->getResponse()->setHeader('Content-type', 'application/json"');
             
        echo $output;

        die();

        
    }
    
    
	function partnerAction() { 
    
       $id = $this->_request->id;
       $chapId = $this->_request->getParam('p', false); 
       
       $appId = $this->_request->getParam('productid', false); 
       $userId = $this->_request->getParam('userid', false); 

       $queueModel =  new Partner_Model_Queue();
       
       if($userId != 'none')
           $queueModel->addProducttoDownloadQueue($appId, $userId, $chapId);
             
       $this->__initLanguage();
        
        $isChap         = false;
        if ($chapId) {
            $themeMeta  = new Model_ThemeMeta();
            $themeMeta->setEntityId($chapId);
            $themeData  = $themeMeta->getAll();
            
            if (isset($themeData->WHITELABLE_SITE_NAME)) {
                $isChap     = true;
                $this->view->chapInfo   = $themeData;
                $this->view->chapId     = $chapId;        
            }
        }
        
        $this->_helper->layout->setLayout('web/popup');
        //load up product details
        $appModel   = new Model_Product();
        $sessionLanguage = new Zend_Session_Namespace('application');
        $product    = $appModel->getProductDetailsById($id, true, $sessionLanguage->language_id);
        
        if (!$product) {
            $this->_redirect('/');
        }
     
        
        $this->view->isChap = $isChap;
        $this->view->prod   = $product;
        $this->view->userId = $userId;
        $this->view->productId   = $appId;
        $this->view->flashMessages  = $this->_helper->FlashMessenger->getMessages();
        
    }
    
    
}
