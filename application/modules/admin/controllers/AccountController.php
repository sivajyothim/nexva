<?php

class Admin_AccountController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function preDispatch(){
        if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
        }
    }
    
    
    public function indexAction()
    {
    	$this->view->title  =   "Received Payments";
    	$this->view->year   =   $this->getYear(2008);
        $this->view->selected_year = (''  == $this->_request->year)?date('Y'):$this->_request->year;
        $this->view->month =   $this->getMonth();
        $this->view->selected_month = (''  == $this->_request->month)?date('m'):$this->_request->month;
        
    	
    	$admin_account  =  new Admin_Model_AdminAccount();
    	$total          =  $admin_account->getPaymentDetails(date('m'), date('Y'), 0);
    	$admin_account_data   =  $admin_account->getPaymentDetails($this->_request->getParam("month", date('m')),$this->_request->getParam('year', date('Y')), 0);
   	         
        
    	if($admin_account->isNewGuy){            
          
            $this->view->empty_msg    =   "No activities are found.";
        }
        //fixed a logical issue - 30/01/2012 - Maheel
        else
        {          
           
            $page       =   $this->_request->getParam('page',1);
            $paginator  =   Zend_Paginator::factory($admin_account_data);
            $paginator->setItemCountPerPage(20);
            $paginator->setCurrentPageNumber($page);
            $this->view->admin_account_data = $paginator;

            if(isset ($total[0]['total']) and '' != $total[0]['total']){

                $local  =   new Zend_Locale();
                $total_payable_formatted =   Zend_Locale_Format::toNumber($total[0]['total'], array( 'number_format' => '#,##,##0.00','locale' => 'en'));
                $this->view->account_datax = $total_payable_formatted;
            }else{

                $this->view->account_datax = '0.00';
            } 
        }	
    	
        
    }
    
    /**
     * give year starting from $year_start date to this year.
     * @return array mix
     * @access public
     * @author cherankrish cheran@nexva.com
     *
     */
     private  function getYear($year_start = 2008){

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
     private function getMonth(){

        for($a = 1;$a <= 12;$a++){

           //$timestamp  =   mktime(date('h'), date('i'),date('s'), $a, 0, date('Y'));
           $timestamp  =   mktime(date('h'), date('i'),date('s'), $a, 1, date('Y'));
            
           $months[$a] = date('F',$timestamp);

        }
        
 
        return  $months;
    }


}

