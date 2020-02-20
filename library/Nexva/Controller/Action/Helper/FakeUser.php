<?php
/**
 * 
 * Detects the region and loads the appropriate currency
 * @author John
 *
 */
class Nexva_Controller_Action_Helper_FakeUser extends Zend_Controller_Action_Helper_Abstract {
    
    public function fake() {
        
        $request = $this->getRequest();
        
        if($request->getRequestUri() != '/user/fake-user') {

        $sessionChapDetails = new Zend_Session_Namespace('partnermobile');
        $chapId = $sessionChapDetails->id;
        
        if($chapId  ==  36015)    {
        

        			
        			if(@Zend_Auth::getInstance()->getIdentity()->id)
        			    return; 
        			else {      			
        			    
        			    $db = Zend_Registry::get('db');
        			    $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'username', 'username', "type='USER'");
        			    $authDbAdapter->setIdentity('test3');
        			    $authDbAdapter->setCredential('test3');
        			    
        			    $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
        			    
        			    if($result->isValid())
        			    {
        			    	Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));
        			    }
        			    
        			    //$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
        			    //$redirector->gotoUrl ('http://'. $_SERVER ['HTTP_HOST'].'/user/fake-user');
        			}
        			

        
        }
        
        }
        
    }
        

}
