<?php

class Pbo_PaymentGatewayController extends Zend_Controller_Action
{
    public function preDispatch() 
    {        
         if( !Zend_Auth::getInstance()->hasIdentity() ) 
         {

            $skip_action_names = array ('login', 'register', 'forgotpassword', 'resetpassword', 'claim', 'impersonate');
            
            if (! in_array ( $this->getRequest()->getActionName (), $skip_action_names)) 
            {            
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
                $this->_redirect ( '/user/login' );
            } 
        }    
    
        $this->_helper->layout->setLayout('pbo/pbo');
    }
    
    //List down all the Payment gateways
    public function indexAction()
    {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        
        //Get payment gateways available for CHAP
        $pgModel = new Pbo_Model_PaymentGateways();
        $paymentGateways = $pgModel->getAllPaymentGateways($chapId);
        
        $this->view->gateways = $paymentGateways;
        
        //Get payment gateway of this CHAP
        $pgUsersModel = new Pbo_Model_PaymentGatewayUsers();
        $chapGateway = $pgUsersModel->getGatewayByChap($chapId);
        
        $this->view->chapGateway = $chapGateway;
        
        $this->view->title = "Payment Gateways : Manage Payment Gateways";
        
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
    
    //Make a payement gateway as the default
    public function setDefaultAction()
    {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        
        $gatewayId = trim($this->_request->id);
        
        //Get payment gateway of this CHAP
        $pgUsersModel = new Pbo_Model_PaymentGatewayUsers();
        $isDisabled = $pgUsersModel->disableGatewayByChap($chapId);
        
//        if($isDisabled == TRUE)
//        {
            $status = 1;
            $pgUsersModel->addChapGateway($chapId, $gatewayId, $status);
            
            $this->_helper->flashMessenger->addMessage('Default payment gateway successfully set.');
//        }
//        else
//        {
//            $this->_helper->flashMessenger->addMessage('Task saved');
//        }
        
        $this->_redirect(PBO_PROJECT_BASEPATH.'payment-gateway');
        
        
    }
}
