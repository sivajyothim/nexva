<?php
class Nexva_Auth_Provider_Google extends Nexva_Auth_Provider_OpenId implements Nexva_Auth_Provider_Interface {
    
    private $consumer   = null;
    private $openid     = null;
    
    function __construct($consumer, $openIdUrl) {
        $this->consumer     = $consumer;
        $this->openid       = $openIdUrl;
    }
    
    /**
     * (non-PHPdoc)
     * @see library/Nexva/Auth/Provider/Nexva_Auth_Provider_IAuthenticate::doLogin()
     */
    function doLogin() {
        $consumer   = $this->consumer;
        $openid     = $this->openid;
        
        $auth = $consumer->begin($openid);
    
        // No auth request means we can't begin OpenID.
        if (!$auth) {
            $this->throwError("Authentication error; not a valid OpenID.");
        }
    
        // Create attribute request object
        // See http://code.google.com/apis/accounts/docs/OpenID.html#Parameters for parameters
        // Usage: make($type_uri, $count=1, $required=false, $alias=null)
        $attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email',2,1, 'email');
        $attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/first',1,1, 'firstname');
        $attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/last',1,1, 'lastname');
        
        // Create AX fetch request
        $ax = new Auth_OpenID_AX_FetchRequest;
        
        // Add attributes to AX fetch request
        foreach($attribute as $attr){
            $ax->add($attr);
        }
        
        // Add AX fetch request to authentication request
        $auth->addExtension($ax);
        
        // Redirect the user to the OpenID server for authentication.
        // Store the token for this authentication so we can verify the
        // response.
    
        $response   = array();
        
        // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
        // form to send a POST request to the server.
        if ($auth->shouldSendRedirect()) {
            $redirect_url = $auth->redirectURL($this->getTrustRoot(),
                                                       $this->getReturnTo());
    
            // If the redirect URL can't be built, display an error
            // message.
            if (Auth_OpenID::isFailure($redirect_url)) {
                $this->throwError("Could not redirect to server: " . $redirect_url->message);
            } else {
                // Send redirect.
                
                $response['TYPE']       = 'REDIRECT';
                $response['VALUE']      = $redirect_url;
                return $response;
            }
        } else {
            // Generate form markup and render it.
            $form_id = 'openid_message';
            $form_html = $auth->htmlMarkup($this->getTrustRoot(), $this->getReturnTo(),
                                                   false, array('id' => $form_id));
    
            // Display an error if the form markup couldn't be generated;
            // otherwise, render the HTML.
            if (Auth_OpenID::isFailure($form_html)) {
                $this->throwError("Could not redirect to server: " . $form_html->message);
            } else {
                $response['TYPE']       = 'FORM';
                $response['VALUE']      = $form_html;
                return $response;
            }
        }
    }
    
    //recently added - 23/04/2012
    function beginLogin() {
        $consumer   = $this->consumer;
        $openid     = $this->openid;
        
        $auth = $consumer->begin($openid);
    
        // No auth request means we can't begin OpenID.
        if (!$auth) {
            $this->throwError("Authentication error; not a valid OpenID.");
        }
    
        // Create attribute request object
        // See http://code.google.com/apis/accounts/docs/OpenID.html#Parameters for parameters
        // Usage: make($type_uri, $count=1, $required=false, $alias=null)
        $attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email',2,1, 'email');
        $attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/first',1,1, 'firstname');
        $attribute[] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/last',1,1, 'lastname');
        
        // Create AX fetch request
        $ax = new Auth_OpenID_AX_FetchRequest;
        
        // Add attributes to AX fetch request
        foreach($attribute as $attr){
            $ax->add($attr);
        }
        
        // Add AX fetch request to authentication request
        $auth->addExtension($ax);
        
        // Redirect the user to the OpenID server for authentication.
        // Store the token for this authentication so we can verify the
        // response.
    
        $response   = array();
        
        // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
        // form to send a POST request to the server.
        if ($auth->shouldSendRedirect()) {
            $redirect_url = $auth->redirectURL($this->getTrustRoot(),
                                                       "http://mobilereloaded.mobi/download/postback");
    
            // If the redirect URL can't be built, display an error
            // message.
            if (Auth_OpenID::isFailure($redirect_url)) {
                $this->throwError("Could not redirect to server: " . $redirect_url->message);
            } else {
                // Send redirect.
                
                $response['TYPE']       = 'REDIRECT';
                $response['VALUE']      = $redirect_url;
                return $response;
            }
        } else {
            // Generate form markup and render it.
            $form_id = 'openid_message';
            $form_html = $auth->htmlMarkup($this->getTrustRoot(), "http://mobilereloaded.mobi/download/postback",
                                                   false, array('id' => $form_id));
    
            // Display an error if the form markup couldn't be generated;
            // otherwise, render the HTML.
            if (Auth_OpenID::isFailure($form_html)) {
                $this->throwError("Could not redirect to server: " . $form_html->message);
            } else {
                $response['TYPE']       = 'FORM';
                $response['VALUE']      = $form_html;
                return $response;
            }
        }
    }
} 
