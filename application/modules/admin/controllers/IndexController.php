<?php

class Admin_IndexController extends Zend_Controller_Action
{

    public function init()
    {


        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->view->sidebar_enabled =true;
        
        $query_cps    =   Zend_Registry::get('db')->select()
                                                  ->from('users',array("total" => "count(*)"))
                                                  ->where("type='CP'")
                                                  ->query();
         $cps         =   $query_cps->fetchAll();
         $this->view->cp_count=$cps[0]->total;


         
         $query_customers    =   Zend_Registry::get('db')->select()
                                                  ->from('users',array("total" => "count(*)"))
                                                  ->where("type='USER'")
                                                  ->query();
         $customers          =   $query_customers->fetchAll();
         $this->view->customer_count=$customers[0]->total;

          $query_handsets    =   Zend_Registry::get('db')->select()
                                                  ->from('devices',array("total" => "count(*)"))
                                                  ->query();
         $handsets   =   $query_handsets->fetchAll();
         $this->view->handsets_count=$handsets[0]->total;

         $query_platforms    =   Zend_Registry::get('db')->select()
                                                  ->from('platforms',array("total" => "count(*)"))
                                                  ->query();
         $platforms   =   $query_platforms->fetchAll();
         $this->view->platforms_count=$platforms[0]->total;


         $users             =   new Model_User();
         $last_ten_users    =   $users->select()
                                      ->order('id desc')
                                      ->limit(10,0);
         $this->view->users =  $users->fetchAll($last_ten_users);

         $products  =   new Model_Product();

         $this->view->last_ten_uploads = $products->select()
                                                  ->order('id desc')
                                                  ->limit(10,0)
                                                  ->query()
                                                  ->fetchAll();
                                                  
        $unApprovedlistReview = new Model_Review();
        $this->view->non_approve_count = $unApprovedlistReview->unApprovedlistReview();
   

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
    


}

