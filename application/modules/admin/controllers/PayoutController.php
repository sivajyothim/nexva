<?php

/**
 * PayoutController
 * 
 * @author Chathura 
 * @version 
 * 
 */

class Admin_PayoutController extends Zend_Controller_Action {
	
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
		
		$this->_redirect ( '/payout/list' );
	}
	
	function createAction() {
		
		$payouts = new Admin_Model_Payout ( );
		
		if($this->_request->id)   {
			
		  $this->view->payout = $payouts->getPayoutRoyaltiesById($this->_request->id);

		}

		if ($this->getRequest ()->isPost ()) {
			
			$validity = true;
			if (empty ( $this->_request->payoutNexva )) {
				$this->view->error = 'Payout Nexva is empty';
				$validity = false;
			} else {
				$payout ['payout_nexva'] = $this->_request->payoutNexva;
			}
			
			if (empty ( $this->_request->payoutCp )) {
				
				$this->view->error = 'Payout CP is empty';
				$validity = false;
			} else {
				
				$payout ['payout_cp'] = $this->_request->payoutCp;
			
			}
			
			if (empty ( $this->_request->payoutDescription )) {
				
				//$this->view->error = 'Payout Description is empty';
			
			} else {
				
				$payout ['description'] = $this->_request->payoutDescription;
			
			}
			
			if (empty ( $this->_request->payoutName )) {
				$this->view->error = 'Payout name is empty';
				$validity = false;
			} else {
				$payout ['name'] = $this->_request->payoutName;
			}

            if(empty($this->_request->payout_chap)){
                $this->view->error = 'Payout Chap is empty';
                $validity = false;
            }else{
                $payout ['payout_chap'] = $this->_request->payout_chap;
            }

            /*if(empty($this->_request->cp_payout_description)){
                $this->view->error = 'CP Payout Description is empty';
                $validity = false;
            }else{
                $payout ['cp_payout_description'] = $this->_request->cp_payout_description;
            }*/
            $payout ['cp_payout_description'] = $this->_request->cp_payout_description;
            //payout super chap is not mandatory
            $payout ['payout_super_chap'] = $this->_request->payout_superchap;

            //calculate the total of four fields
            $tot = $payout ['payout_cp'] + $payout ['payout_nexva'] + $payout ['payout_chap'] + $payout ['payout_super_chap'];

            if($tot < 100) //if total is less than 100 showing the error message
            {
                $this->view->error = 'Total Cannot be less than 100 %';
                $validity = false; //server side validation is failed will not call the model
            }
            else if($tot > 100) //if total is greater than 100 showing the error message
            {
                $this->view->error = 'Total Cannot be greater than 100 %';
                $validity = false; //server side validation is failed will not call the model
            }

            //setting the entered values for relevant fields so even if total of the fields not equal 100, last entered values of the form remains
            $this->view->payoutCp = $this->_request->payoutCp;
            $this->view->payoutNexva = $this->_request->payoutNexva;
            $this->view->payoutChap = $this->_request->payout_chap;
            $this->view->payout_superchap = $this->_request->payout_superchap;

			if ($validity) {
			
	
			if($this->_request->id)  {
				
				  $payouts->updatePayoutRoyalties($this->_request->id, $payout);
				  $this->_redirect ( '/payout/list' );
				
			}
			else
			{
				
            $payouts->createPayoutRoyalties ( $payout );
            $this->_redirect ( '/payout/list' );
				
			}
			
			 }
		
		}
	
	}
	
   
    function listAction(){

        $payout         =    new Admin_Model_Payout();
        $allPayout     =    $payout->fetchAll();

        $pagination = Zend_Paginator::factory($allPayout);
        $pagination->setCurrentPageNumber($this->_getParam('page',1));
        $pagination->setItemCountPerPage(10);

        $this->view->payout = $pagination;
    }
	
	



}
?>

