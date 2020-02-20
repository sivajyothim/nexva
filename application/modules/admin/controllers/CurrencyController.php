<?php
class Admin_CurrencyController extends Nexva_Controller_Action_Admin_MasterController {
    
    function indexAction() {
        $currencyModel  = new Admin_Model_Currency();
        $cId            = $this->_getParam('id', false);
        

        if ($this->_request->isPost()) {
            $params     = $this->_getAllParams();
            $data       = $currencyModel->getPopulatedArray($params);
            if ($cId) {
                $currencyModel->update($data, 'id = ' . $cId);
                $this->__addMessage('Currency updated');
            } else {
                $data['enabled']    = 1;
                $data['enabled']    = 1;
                $currencyModel->insert($data);
                $this->__addMessage('Currency added');
            }
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'currency');
        } else {
            $currency   = $currencyModel->getPopulatedObject();
            if ($cId) {
                $currency   = $currencyModel->fetchRow('id = ' . $cId);
                if (!$currency) {
                    $this->__addErrorMessage("Sorry, that currency does not exist");
                    $this->_redirect(ADMIN_PROJECT_BASEPATH.'currency');
                }
            }   
        }
        
        $currencies     = $currencyModel->fetchAll();
        $this->view->currencies = $currencies;
        $this->view->currency   = $currency;
    }
    
    
    function deleteAction(){ 
        $currency          = new Admin_Model_Currency();
        $id     = $this->_getParam('id');
        
        try {
            $currency->delete('id = ' . $id);
            $this->__addMessage("Currency deleted");
        } catch (Exception $ex) {
            $this->__addErrorMessage("Currency could not be deleted. This currency may have prices attached to it");
        }
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'currency/index');
    }
}