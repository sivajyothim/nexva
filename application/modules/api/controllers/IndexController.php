<?php

/*
 * 
 * this is for the paythru testing for this can be deleted 
 * 
 */

class Api_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

     public function indexAction()
    {
    //	$this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

    	   	
    	
    }
    
	public function buyAction() {

	    $this->_helper->viewRenderer->setNoRender(true);
	    $this->_helper->getHelper('layout')->disableLayout();
	
	    $paymentGateway = new Nexva_PaymentGateway_PayPal();
	    // get user details
	
   
	    // IPN
	    $postback = "http://" . $_SERVER['SERVER_NAME'] . '/app/postback';
	    
	    // Success URL
	    $success = "http://" . $_SERVER['SERVER_NAME'] . '/app/';

	    $config = Zend_Registry::get('config');
	    $email = $config->paypal->business_email;
	    $endPoint = $config->paypal->endpoint_url;
	    $paymentGateway->SetServiceEndPoint($endPoint);
	    $vars = array(
	      'business' => $email,
	      'return' => $success,
	      'cancel_return' => $success,
	      'notify_url' => $postback,
	      'item_name' => 'test',
	      'amount' => '10',
	      'custom' => '25'. '&' . '50',
	      'no_shipping' => 1
	    );
	
	    $paymentGateway->Prepare($vars);
	    $paymentGateway->Execute();
	  }
  
}

