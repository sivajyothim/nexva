<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 4/29/13
 * Time: 4:15 PM
 * PricePoint Controller
 */

class Admin_PricePointController extends Zend_Controller_Action {

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

        $this->view->headScript ()->appendFile ( '/admin/assets/js/admin.js' );

        $this->view->headLink ()->appendStylesheet ( '/admin/assets/css/admin.css' );


        // Flash Messanger
        $this->_flashMessenger = $this->_helper->getHelper ( 'FlashMessenger' );
        $this->view->flashMessenger = $this->_flashMessenger;
    }

    function indexAction() {

        $this->_redirect ( '/pricepoint/list' );
    }

    function listAction(){

        //passing the values for payment gateway drop down
        $paymentGateway = new Model_PaymentGateway();
        $paymentGateways = $paymentGateway->fetchAll();
        $this->view->paymentGateways = $paymentGateways;

        if($this->getRequest()->filter_gateway)
        {
            //echo Zend_Session::namespaceIsset('filter_session');

            $filterGateway = new Zend_Session_Namespace('filter_session');
            $filterGateway->selected_gateway = $this->getRequest()->filter_gateway;
            //$filterGateway->filter_keyword = $this->getRequest()->filter_price_point;
        }
        else
        {
            //Zend_Session::namespaceUnset('filter_session');
        }


        if($this->getRequest()->filter_price_point)
        {
            //echo '<br/>','filter price point';
            $filterGateway = new Zend_Session_Namespace('filter_session');
            //$filterGateway->selected_gateway = $this->getRequest()->filter_gateway;
            $filterGateway->filter_keyword = $this->getRequest()->filter_price_point;
        }
        else
        {
            //Zend_Session::namespaceUnset('filter_session');
        }
        /*if((empty($this->getRequest()->filter_gateway)) && (empty($this->getRequest()->filter_price_point)))
        {
            echo 'both empty';
            $filterGateway = new Zend_Session_Namespace('filter_gateway');
            $filterGateway->selected_gateway = NULL;
            $filterGateway->filter_keyword = NULL;
        }*/

        //passing values for list data
        $pricePoint        =    new Model_PricePoint();
        $allPricePoints     =    $pricePoint->getPricePointList();

        $pagination = Zend_Paginator::factory($allPricePoints);
        $pagination->setCurrentPageNumber($this->_getParam('page'),1);
        $pagination->setItemCountPerPage(10);

        $this->view->pricepoints = $pagination;
    }

    function createAction()
    {
        Zend_Session::namespaceUnset('filter_session');

        //passing the values for payment gateway drop down
        $paymentGateway = new Model_PaymentGateway();
        $paymentGateways = $paymentGateway->fetchAll();

        $this->view->paymentGateways = $paymentGateways;
    }

    function saveAction()
    {
        //passing the values for payment gateway drop down
        $paymentGateway = new Model_PaymentGateway();
        $paymentGateways = $paymentGateway->fetchAll();

        $this->view->paymentGateways = $paymentGateways;

        $pricePointModel = new Model_PricePoint();
        /*$pricePoint['price'] = 10.00;
        $test = $pricePointModel->checkAvailability($pricePoint);
        $this->view->tests = $test;*/

        if ( $this->getRequest()->isPost() ) {
            $validity = true;

            //check payment gateway value is empty
            if (empty($this->_request->payment_gateway)) {
                $this->view->error = 'Payment Gateway is empty';
                $validity = false;
            } else {
                $pricePoint['gateway_id'] = $this->_request->payment_gateway;
            }

            //check price value is empty
            if (empty($this->_request->price)) {
                $this->view->error = 'Price is empty';
                $validity = false;
            } else {
                $pricePoint['price'] = $this->_request->price;
            }

            //check price point value is empty
            if (empty($this->_request->price_point)) {
                $this->view->error = 'Price Point is empty';
                $validity = false;
            } else {
                $pricePoint['price_point'] = $this->_request->price_point;
            }

            //check whether input price point already exists
            $exists = $pricePointModel->checkExists($pricePoint);

            //if all field values are valid
            if ($validity) {
                //if there is a id updating the database
                if($this->_request->id) {
                    //if input price point already exists poping up a message
                    if($exists)
                    {
                        $pricePointModel = new Model_PricePoint();
                        $this->view->pricePoint   =   current($pricePointModel->find($this->_request->id)->toArray());

                        $this->view->error = 'Given Price Point Already Exists';
                        $this->render('/create');
                    }
                    else
                    {
                        $pricePointModel->update($pricePoint,"id = ".$this->_request->id);
                        $this->_redirect ('/pricepoint/list');
                    }
                }
                //if there isn't a id inserting the database
                else
                {
                    //if input price point already exists poping up a message
                    if($exists)
                    {
                        $this->view->error = 'Given Price Point Already Exists';
                        $this->render('/create');
                    }
                    else
                    {
                        /*$this->view->pricePoint = $pricePoint->toArray();
                        Zend_Debug::dump($this->view->pricePoint);*/
                        $pricePointModel->insert($pricePoint);
                        $this->_redirect ('/pricepoint/list');
                    }

                }
            }
        }
    }

    function editAction() {

        //passing values for payment gateway drop down
        $paymentGateway = new Model_PaymentGateway();
        $paymentGateways = $paymentGateway->fetchAll();

        $this->view->paymentGateways = $paymentGateways;

        //passing values for a particular record to the form
        $pricePointModel = new Model_PricePoint();
        $this->view->pricePoint   =   current($pricePointModel->find($this->_request->id)->toArray());

        $this->render('/create');
    }

    function deleteAction(){

        $pricePointModel = new Model_PricePoint();

        //set the deleted column value to zero
        $pricePoint['deleted'] = 0;
        $pricePointModel->update($pricePoint,"id = ".$this->_request->id);

        $this->view->message    =   "Price Point attached with id - {$this->_request->id} is deleted.";
        $this->listAction();
        $this->render('/list');

    }
}