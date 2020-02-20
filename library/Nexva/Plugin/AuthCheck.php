<?php
/**
 * A simple zf plugin to check if a user is authed or not in various modules.
 *
 * If you want your module checked, write a function with the convention:
 *      [modulename]AuthCheck(Zend_Controller_Request_Abstract $request) e.g:
 *      protected function resellerAuthCheck(Zend_Controller_Request_Abstract $request) {}
 *
 * and your function will be automagically called when some one accesses the right [modulename].
 *
 * You are obviously in charge of implementing the logic within the [modulename]AuthCheck() function
 *
 * @author jahufar
 */
class Nexva_Plugin_AuthCheck extends Zend_Controller_Plugin_Abstract {

    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        $function =  $request->getModuleName().'AuthCheck';

        if( method_exists($this, $function) ) $this->$function($request);
    }


    protected function resellerAuthCheck(Zend_Controller_Request_Abstract $request) {
        if (! Zend_Auth::getInstance ()->hasIdentity ()) {
            //List of controller action pairs in the format of 'controller-action' that should not be authd
            $openActions            = array('user-login', 'user-resetpassword', 'user-impersonate');
            $controllerActionKey    = strtolower($request->getControllerName() . '-' . $request->getActionName());
            
            if (in_array($controllerActionKey, $openActions) === false) {
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();

                $request->setControllerName('user')
                        ->setActionName('login')
                        ->setDispatched(false);
            }
        }       
    }


}
?>
