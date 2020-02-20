<?php

/**
 *  This will validate url if the request came http then thiw will filter url and append https:// redirect same request
 * @copyright   neXva.com
 * @author      chathura 
 * @version     $Id$
 */



class Nexva_Controller_Action_Helper_SslValidate extends Zend_Controller_Action_Helper_Abstract {
	
	public function direct() {
	
        return; //put in place because our dammed SSL certs were expired and no one wants to renew it.
		if ( APPLICATION_ENV == 'production') {
			
		  if ($_SERVER ['SERVER_PORT'] == 80 ) {
				$request = $this->getRequest ();
				$url = 'https://' . $_SERVER ['HTTP_HOST'] . $request->getRequestUri ();
				$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
				$redirector->gotoUrl ( $url );
				
			
			}
		}
	}
}
