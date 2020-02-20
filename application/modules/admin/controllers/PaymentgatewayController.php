<?php

/**
 * PayoutController
 * 
 * @author Chathura 
 * @version 
 * 
 */

class Admin_PaymentgatewayController extends Zend_Controller_Action {
    
    public function preDispatch() {
        if (! Zend_Auth::getInstance ()->hasIdentity ()) {
            
            if ($this->_request->getActionName () != "login") {
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
            
            }
            if ($this->_request->getActionName () != "login")
                $this->_redirect ( '/user/login' );
        }
    }
    
    public function init() {
        // include Ketchup libs
        $this->view->headLink ()->appendStylesheet ( '/common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css' );
        //$this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/ketchup/js/jquery.min.js');
        $this->view->headScript ()->appendFile ( '/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js' );
        $this->view->headScript ()->appendFile ( '/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js' );
        $this->view->headScript ()->appendFile ( '/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js' );
        // checkboxtree file for categories
        

        $this->view->headScript ()->appendFile ( '/admin/assets/js/admin.js' );
        
        // Flash Messanger
        $this->_flashMessenger = $this->_helper->getHelper ( 'FlashMessenger' );
        $this->view->flashMessenger = $this->_flashMessenger;
    }
    
    function indexAction() {
        
        $this->_redirect ( '/paymentgateway/list' );
    }
    
    function createAction() {
        
        $paymentGatewayModel         =    new Model_PaymentGateway();
        
        if($this->_request->id)   {
            
          $this->view->paymentGateway = $paymentGatewayModel->getPaymentGatewayById($this->_request->id);
            
            
            
        }
        
        if ($this->getRequest ()->isPost ()) {
            
            $validity = true;
            
            if (empty ( $this->_request->paymetgatewayName)) {
                $this->view->error = 'Paymet gateway name is empty';
                $validity = false;
            
            } else {
                $paymetgateway['paymetgatewayName'] = $this->_request->paymetgatewayName;
            }
            
            if (empty ( $this->_request->paymetgatewayCharge )) {
                
                $this->view->error = 'Paymet gateway charge is empty';
                $validity = false;
            
            } else {
                
                $paymetgateway ['paymetgatewayCharge'] = $this->_request->paymetgatewayCharge;
            
            }
            
            if (empty ( $this->_request->paymetgatewayFixedCost )) {
                
                $this->view->error = 'Paymet gateway Fixed Cost is empty';
            
            } else {
                
                $paymetgateway['paymetgatewayFixedCost'] = $this->_request->paymetgatewayFixedCost;
            
            }
            
            
            $paymetgateway ['supportsWeb'] = $this->_request->supportsWeb;
            $paymetgateway ['supportsMobile'] = $this->_request->supportsMobile;
            $paymetgateway ['supportsInapp'] = $this->_request->supportsInapp;
            $paymetgateway ['status'] = $this->_request->status;
            
            if ($validity) {
            
    
            if($this->_request->id)  {
                
                  $paymentGatewayModel->updatePaymentGateway($this->_request->id, $paymetgateway);
                  $this->_redirect ( '/paymentgateway/list' );
                
            }
            else
            {
                
            $paymentGatewayModel->createPaymentGateway( $paymetgateway );
            
            
            $this->_redirect ( '/paymentgateway/list' );
                
            }
            
             }
        
        }
    
    }
    
function editAction() {
        
        $paymentGatewayModel         =    new Model_PaymentGateway();
        
        if($this->_request->id)   {
            
          $this->view->paymentGateway = $paymentGatewayModel->getPaymentGatewayById($this->_request->id);
            
            
            
        }
        
        if ($this->getRequest ()->isPost ()) {
            
            $validity = true;
            
            if (empty ( $this->_request->paymetgatewayName)) {
                $this->view->error = 'Paymet gateway name is empty';
                $validity = false;
            
            } else {
                $paymetgateway['paymetgatewayName'] = $this->_request->paymetgatewayName;
            }
            
            if (empty ( $this->_request->paymetgatewayCharge )) {
                
                $this->view->error = 'Paymet gateway charge is empty';
                $validity = false;
            
            } else {
                
                $paymetgateway ['paymetgatewayCharge'] = $this->_request->paymetgatewayCharge;
            
            }
            
            if (empty ( $this->_request->paymetgatewayFixedCost )) {
                
                $this->view->error = 'Paymet gateway Fixed Cost is empty';
            
            } else {
                
                $paymetgateway['paymetgatewayFixedCost'] = $this->_request->paymetgatewayFixedCost;
            
            }
            
            
            $paymetgateway ['supportsWeb'] = $this->_request->supportsWeb;
            $paymetgateway ['supportsMobile'] = $this->_request->supportsMobile;
            $paymetgateway ['supportsInapp'] = $this->_request->supportsInapp;
            $paymetgateway ['status'] = $this->_request->status;
            
            if ($validity) {
            
    
            if($this->_request->id)  {
                
                  $paymentGatewayModel->updatePaymentGateway($this->_request->id, $paymetgateway);
                  $this->_redirect ( '/paymentgateway/list' );
                
            }
            else
            {
                
            $paymentGatewayModel->createPaymentGateway( $paymetgateway );
            
            
            $this->_redirect ( '/paymentgateway/list' );
                
            }
            
             }
        
        }
    
    }
    
   
    function listAction(){

        $paymentGateway         =    new Model_PaymentGateway();
        $allPaymentGateways     =    $paymentGateway->fetchAll();

        $pagination = Zend_Paginator::factory($allPaymentGateways);
        $pagination->setCurrentPageNumber($this->_getParam('page',1));
        $pagination->setItemCountPerPage(10);

        $this->view->paymetgateways = $pagination;
    }
    
    



}
?>

