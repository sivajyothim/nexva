<?php

class Cpbo_PaymentController extends Nexva_Controller_Action_Cp_MasterController{

    function preDispatch() {
    $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/facebox/facebox.css');
    $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/facebox/facebox.js');
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            $skip_action_names =
                    array(
                    "login",
                    "register",
                    "forgotpassword",
                    "resetpassword"

            );
            if(!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();
            }

            if(!in_array($this->getRequest()->getActionName(), $skip_action_names)) {
                $this->_redirect('/user/login');
            }
        }
    }
    
    public function init ()
    {
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
        //$this->view->headScript()->appendFile( PROJECT_BASEPATH.'admin/assets/ketchup/js/jquery.min.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        if(!in_array($chapId, array('585474','585480'))){
            $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
        }else{
            $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages_fr.js');
        }
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/cp/assets/js/cp.js');

    }

   public function indexAction(){
   	
        $this->view->title  =   "My Payments";
        $user_account       =   new Cpbo_Model_UserAccount();

        //  retrive cp Payble 
        $total          =   $user_account->getPaymentDetails($this->_request->getParam("from_view", date('Y-m-d')), $this->_request->getParam('to_view', date('Y-m-d')), Zend_Auth::getInstance()->getIdentity()->id);
        $account_data   =   $user_account->getPaymentDetails($this->_request->getParam("from_view", date('Y-m-d')), $this->_request->getParam('to_view', date('Y-m-d')), Zend_Auth::getInstance()->getIdentity()->id, 'WEB');
        $this->view->from_date=$this->_request->getParam("from_view", date('Y-m-d'));
        $this->view->to_date=$this->_request->getParam("to_view", date('Y-m-d'));
        if(count($account_data) <= 0){
            $this->view->emptyMsgPayble    =   "No activities found.";
        }

        $page       =   $this->_request->getParam('page',1);
        $paginator  =   Zend_Paginator::factory($account_data);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($page);
        $this->view->account_data = $paginator;
        $this->view->year   =   $this->getYear(2008);
        $this->view->selected_year = (''  == $this->_request->year)?date('Y'):$this->_request->year;
        $this->view->month =   $this->getMonth();
        $this->view->selected_month = (''  == $this->_request->month)?date('m'):$this->_request->month;

        $totalval   =   next($total);
        
        if(isset($total[0]))	{
            if(isset($total[0]['total'])){
        	
        	    if($total[0]['total'] > 0)	{
            
                    $local  =   new Zend_Locale();
                    $total_payable_formatted =   Zend_Locale_Format::toNumber($total[0]['total'], array( 'number_format' => '#,##,##0.00','locale' => 'en'));
                    $this->view->account_datax = $total_payable_formatted;
        	    }    else    {
        		
        		    $this->view->account_datax = '0.00';
        	    }
            }else{
           
            $this->view->account_datax = '0.00';
            
            }

         } else{
           
            $this->view->account_datax = '0.00';
         }
        
        
        // retrive cp credits 
        $totalLeftCpCredit      =   $user_account->getPaymentDetailsForCpCreditLeft(Zend_Auth::getInstance()->getIdentity()->id, 'CP');
        $accountDataLeftCpCredit   =   $user_account->getPaymentDetails($this->_request->getParam("month", date('m')), $this->_request->getParam('year', date('Y')), Zend_Auth::getInstance()->getIdentity()->id, 'CP');
       
        if(count($accountDataLeftCpCredit) <= 0){
            $this->view->emptyMsgCpCredits    =   "No activities found.";
        }
        

        $pageCp       =   $this->_request->getParam('page_cp',1);
        $paginator  =   Zend_Paginator::factory($accountDataLeftCpCredit);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($pageCp);
        $this->view->accountCpCreditLeftData = $paginator;
        
        

        $this->view->year   =   $this->getYear(2008);
        $this->view->selected_year = (''  == $this->_request->year)?date('Y'):$this->_request->year;
        $this->view->month =   $this->getMonth();
        $this->view->selected_month = (''  == $this->_request->month)?date('m'):$this->_request->month;

        $totalval   =   next($total);
        
        if(isset($totalLeftCpCredit[0]))	
        {
        	if(isset($totalLeftCpCredit[0]['total'])){
	            if($totalLeftCpCredit[0]['total'] > 0)	{
	                $local  =   new Zend_Locale();
	                $total_payable_formatted =   Zend_Locale_Format::toNumber($totalLeftCpCredit[0]['total'], array( 'number_format' => '#,##,##0.00','locale' => 'en'));
	                $this->view->accountDataxCredit = $total_payable_formatted;
	            }    else    {
	            	$this->view->accountDataxCredit = '0.00';
	            }
	        }else{
	           
	            $this->view->accountDataxCredit = '0.00';
	        }
	        
	        $this->view->accountDataxCredit = '0.00';
	        
        }
        
   		//  retrive cp invoices
   		
        $invoices = new Model_CpInvoices();
        $accountCpInvoiceData   =   $invoices->getInvoiceByUserId(Zend_Auth::getInstance()->getIdentity()->id);
       
        if(count($accountCpInvoiceData) <= 0){
            $this->view->emptyMsgCpInvoices    =   "No activities found.";
        }

        $pageInvoice     =   $this->_request->getParam('page_invoice',1);
        $paginator  =   Zend_Paginator::factory($accountCpInvoiceData);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($pageInvoice);
        $this->view->accountCpInvoiceData = $paginator;    

        if($this->_request->status == 'success')	
        	$this->view->paymentStatus = 'success';
        	
     	if($this->_request->status == 'failed')
        	$this->view->paymentStatus = 'failed';
        	

        	
        	
        	
        	

        	

    }
    
    public function submitAction() {
	
    	$url  =  '/payment/index/from_view/'.$this->_request->from_view.'/to_view/'.$this->_request->to_view.'/tab/'.$this->_request->tab;

    	$this->_redirect($url);
    
    }
    
    
	public function paynowAction() {

		$this->_helper->viewRenderer->setNoRender(true);
	    $this->_helper->getHelper('layout')->disableLayout();
	
	    if ($this->getRequest()->isPost()) {
	
	    $payementGatewaysModel = new Model_PaymentGateway();
	    //$pgID = 3 paypal 
	    $pgRow = $payementGatewaysModel->find(3);
	    $pgRow = $pgRow->current();
	    $factoryProduct = $pgRow->gateway_id;
	    $paymentGateway = Nexva_PaymentGateway_Factory::factory($factoryProduct, $factoryProduct);
	
	    // get user details
	    $userId = Zend_Auth::getInstance()->getIdentity()->id;
			if (empty ( $userId )) {
				$session = new Zend_Session_Namespace ( 'lastRequest' );
				$session->lastRequestUri = $_SERVER ['REQUEST_URI'];
				$this->_redirect ( '/user/login' );
			
			}
	
	    $userMeta = new Model_UserMeta();
	    $userMeta->setEntityId($userId);
	
	    $session = new Zend_Session_Namespace('payments');
	
	    $session->product_id = 1;
	    $session->payment_gateway_id = 3;
	    // session Id
	    $sessionId = Zend_Session::getId();
	    
	    $postback = "http://" . $_SERVER['SERVER_NAME'] . '/payment/';
	    
	    // set the notification Url for paypal IPN
	    if(APPLICATION_ENV == 'production')	
	    	$paypalNotify = "http://nexva.com/app/postbackpaypalcp";
	    else 
			$paypalNotify = "http://mobilereloaded.com/app/postbackpaypalcp";
			
	    $success = "http://" . $_SERVER['SERVER_NAME'] . '/payment/paymentstatus/status/1';
	    $failed  = "http://" . $_SERVER['SERVER_NAME'] . '/payment/paymentstatus/status/0';
	
	    $vars = array(
	      'return' => $success,
	      'cancel_return' => $failed,
	      'notify_url' => $paypalNotify,
	      'success_url' => $paypalNotify,
	      'item_name' => 'Payment for nexva',
	      'item_desc' => 'Payment for usage of neXva services .',
	      'item_price' => $this->_request->amount,
	      'item_reference' => ' ',
	      'amount' => $this->_request->amount,
	      'custom' => $userId.'&'.$this->_request->amount,
	      'no_shipping' => 1
	    );
	    
	    $paymentGateway->Prepare($vars);
	    $paymentGateway->Execute();
    
    }
    
  }
    public function paymentstatusAction ()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        if ($this->_request->status == 1) {
            $this->_redirect('/payment/index/status/success');
        } else {
            $this->_redirect('/payment/index/status/failed');
        }
    }
    
    public function invoiceAction ()
    {
        //$this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        $invoiceId = $this->_request->id;
        
        $invoiceModel = new Model_CpInvoices();
        $invoice = $invoiceModel->getInvoice($invoiceId);
		$userModel = new Model_User();
        $userRow = $userModel->getUserDetailsById($invoice->user_id);
    	$userMeta = new Model_UserMeta();
        $userMeta->setEntityId($invoice->user_id);
        $this->view->email = $userRow['email'];
        $this->view->name = $userMeta->FIRST_NAME . ' ' . $userMeta->LAST_NAME;
        $this->view->address = $userMeta->ADDRESS;
        $this->view->city = $userMeta->CITY;
        $this->view->country = $userMeta->COUNTRY;
        $this->view->mobile = $userMeta->MOBILE;
        $this->view->transid = $invoice->transaction_id;
        $this->view->invoice_id = $invoice->id;
        $this->view->amount = $invoice->amount;
        $this->view->date = $invoice->date;
        $this->view->paymentgateway = $invoice->payment_gateway;
       
    }

 
    
    
    

    /**
     * give year starting from $year_start date to this year.
     * @return array mix
     * @access public
     * @author cherankrish cheran@nexva.com
     *
     */
    public static  function getYear($year_start = 2008){

        if(is_null($year_start) or '' == $year_start){

            throw new Zend_Exception('Start year must be provided');
        }

        $current_year = date('Y');

        for($a = $year_start;$a <= $current_year;$a++){

            $years[$a]  =   $a;
        }
        return $years;
    }


    
    /**
     * give all month as array $key=month number and $value=month string(name).
     * @return array month
     * @access public
     * @author cherankrish cheran@nexva.com
     *
     */
    public static  function getMonth(){

        for($a = 1;$a <= 12;$a++){

            $timestamp  =   mktime(date('h'), date('i'),date('s'), $a, date('d'), date('Y'));
            $months[$a] = date('F',$timestamp);
        }
        return $months;
    }
}
?>
