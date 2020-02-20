<?php
class Mobile_DownloadController extends Nexva_Controller_Action_Mobile_MasterController 
{
        public function init() 
        {

            $config = Zend_Registry::get("config");
            if(substr($_SERVER['HTTP_HOST'], 0, 3) == 'www')
                $this->_redirect("http://" . $config->nexva->application->mobile->url);

            parent::init();
            $this->view->showToplinks = TRUE;
        }
    
        public function indexAction()
        {
            
        }
        
        public function googleLoginAction()
        {
                        
            $type   = $this->_request->getParam('type', false);
            
            
            if (!$type) {
                $this->_redirect('/user/login');
            }

            $openIdUrl  = $this->_request->getParam('url', '');
            $openIdUrl  = base64_decode($openIdUrl);

            $opts       = array(
                'openIdUrl' => $openIdUrl //we only support openid for now, so this is fine
            );

            //get the provider instace
            $auth   = Nexva_Auth_AuthenticateFactory::getAuth($type, $opts);
            
            if (!$auth) {
                $this->_redirect('/user/login');
            }
            
            
            $response   = $auth->beginLogin();
            
            //Zend_Debug::dump($response);die();
            //check out what type of response it is
            if ($response['TYPE'] == 'REDIRECT')
            {
                $this->_redirect($response['VALUE']);
            }              
            else if ($response['TYPE'] == 'FORM') 
            {
                $this->view->openIdForm =  $response['VALUE'];
            }
                
        } 
        
        
        /**
     * This is where the openID response comes back to
     */
    public function postbackAction() {
        
        $params = $this->_getAllParams();
        Zend_Debug::dump($params);die();
       
        
            $data   = Nexva_Auth_Provider_OpenId::parse($params, array('email'));
            
            Zend_Debug::dump($data);
            die();
            $user   = new Model_User();
            $chapId = isset ( $this->themeMeta->USER_ID ) ? $this->themeMeta->USER_ID : null;
            
            
        
    }
        
}
