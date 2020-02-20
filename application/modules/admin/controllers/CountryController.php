<?php

class Admin_CountryController extends Zend_Controller_Action {


    public function preDispatch() {
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
    function indexAction() {
        $countryModel   =   new Model_Country();
        $pagination =   Zend_Paginator::factory($countryModel->fetchAll());
        $pagination->setCurrentPageNumber($this->_request->getParam('page',0));
        $pagination->setItemCountPerPage(50);

        $this->view->countries =   $pagination;
        
        $currencyModel  = new Admin_Model_Currency();
        $currencies     = $currencyModel->fetchAll();
        $currencyList   = array();
        foreach ($currencies as $currency) {
            $currencyList[$currency->id]    = $currency->name;
        }
        $this->view->currencies = $currencyList;
    }

    function editAction() {
        $countryModel   =   new Model_Country();
        $country =   current($countryModel->find($this->_request->id)->toArray());
        $this->view->country    =   $country;
        
        $currencyModel  = new Admin_Model_Currency();
        $currencies     = $currencyModel->fetchAll();
        $this->view->currencies = $currencies;

    }

    function createAction() {
        $this->render('/edit');

    }
    function saveAction() {
        $countryModel   =   new Model_Country();

        if($this->_request->id != '') {
            $currencyId = (!$this->_getParam('currency_id', null)) ? null : $this->_getParam('currency_id', null);
            $countryModel->update(array(
                    'iso' => $this->_request->countryIso,
                    'name'=> $this->_request->countryName,
                    'printable_name'=> $this->_request->countryPrintName,
                    'numcode'       => $this->_request->countryCode,
                    'currency_id'   => $currencyId
                    ), 'id ='.$this->_request->id);
            $this->view->message    =   $this->_request->countryName ." details  are updated";
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'country');
        }else {

            $countryModel->insert(array(
                    'id' => NULL,
                    'iso' => $this->_request->countryIso,
                    'name'=> $this->_request->countryName,
                    'printable_name'=> $this->_request->countryPrintName,
                    'numcode'  => $this->_request->countryCode));
            $this->view->message    =   $this->_request->countryName ." is created.";

            $this->render('/edit');
        }
    }

    function deleteAction() {

        $countryModel   =   new Model_Country();
        $countryModel->delete("id = ".$this->_request->id);
        $this->view->message    =   "Country attached with id- {$this->_request->id} is deleted.";
        $this->indexAction();
        $this->render('/index');
    }


}

?>
