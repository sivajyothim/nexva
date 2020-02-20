<?php

class Api_YcoinspaymentwidgetController extends Zend_Controller_Action {

    public function init() {
        
    }

     public function notifywebAction() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        //print_r($_REQUEST); die();
        
        //if ($this->_request->isPost()) {
        if ($_REQUEST) {    
            /*$clientId = trim($this->_getParam('clientid'));
            $arrClientId = explode('_', $clientId);
            $developerId = $arrClientId[0];
            $appId = $arrClientId[1];

            $service = trim($this->_getParam('service'));
            $transactionId = trim($this->_getParam('transactionid'));
            $phone = trim($this->_getParam('phone'));
            $enduserPrice = trim($this->_getParam('enduserprice'));
            $status = trim($this->_getParam('status'));
            $errormessage = trim($this->_getParam('errormessage'));
            $revenue = trim($this->_getParam('revenue'));*/
          
            $clientId = trim($_REQUEST['clientid']);
            $arrClientId = explode('_', $clientId);
            $session = $arrClientId[0];
            $intorepPayId = $arrClientId[1];

            $service = trim($_REQUEST['service']);
            $transactionId = trim($_REQUEST['transactionid']);
            $phone = trim($_REQUEST['phone']);
            $enduserPrice = trim($_REQUEST['enduserprice']);
            $status = trim($_REQUEST['status']);
            $errormessage = trim($_REQUEST['errormessage']);
            $revenue = trim($_REQUEST['revenue']);

            $chapId = 115189; //Chap ID for YCoins
            $paymentGatewayIdChap = 16; //YCoins in-app payments
            $activationCode = null; //No activation code needed for YCoins

            //$userMeta = new Model_UserMeta();
            //$verified = $userMeta->getAttributeValue($developerId, 'VERIFIED_ACCOUNT');
            //$userModel = new Api_Model_Users();
            //$userDetails = $userModel->getUserById($developerId);

            //$inappPayments = new Api_Model_InappPayments();
            //$paymentId = $inappPayments->addYcoinInApp($chapId, $intorepPayId, $phone, $enduserPrice, $paymentGatewayIdChap, $activationCode, $status, $transactionId, $service, $revenue, $errormessage);

            
            //Get payment gateway Id of the CHAP            
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $paymentGatewayId = $pgDetails->payment_gateway_id;
            $pgType = $pgDetails->gateway_id; 
            $pgClassName = $pgType;

            //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
            $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);
        
            $purchasedAppDetails = $pgClass->selectInteropPayment($session, $intorepPayId);
            $buildId = $purchasedAppDetails->build_id;
            $appId = $purchasedAppDetails->app_id;
            
            $paymentTimeStamp = date('Y-m-d H:i:s');
            $paymentTransId = strtotime("now");
            
            $buildUrl = '';
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
        
            //Update success transaction to the relevant transaction id in the DB
            $pgClass->updateInteropPayment($paymentTimeStamp, $transactionId, $status, $buildUrl, $intorepPayId, $session);

                    
        }
        else{
            die('Access denied!');
        }

        print_r($_REQUEST);
        echo 'Notify';
        die();
     }
     
     
    public function notifyAction() {
     
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        //print_r($_REQUEST); die();
        
        //if ($this->_request->isPost()) {
        if ($_REQUEST) {    
            /*$clientId = trim($this->_getParam('clientid'));
            $arrClientId = explode('_', $clientId);
            $developerId = $arrClientId[0];
            $appId = $arrClientId[1];

            $service = trim($this->_getParam('service'));
            $transactionId = trim($this->_getParam('transactionid'));
            $phone = trim($this->_getParam('phone'));
            $enduserPrice = trim($this->_getParam('enduserprice'));
            $status = trim($this->_getParam('status'));
            $errormessage = trim($this->_getParam('errormessage'));
            $revenue = trim($this->_getParam('revenue'));*/
            
            $clientId = trim($_REQUEST['clientid']);
            $arrClientId = explode('_', $clientId);
            $developerId = $arrClientId[0];
            $appId = $arrClientId[1];

            $service = trim($_REQUEST['service']);
            $transactionId = trim($_REQUEST['transactionid']);
            $phone = trim($_REQUEST['phone']);
            $enduserPrice = trim($_REQUEST['enduserprice']);
            $status = trim($_REQUEST['status']);
            $errormessage = trim($_REQUEST['errormessage']);
            $revenue = trim($_REQUEST['revenue']);

            $chapId = 115189; //Chap ID for YCoins
            $paymentGatewayIdChap = 19; //YCoins in-app payments
            $activationCode = null; //No activation code needed for YCoins

            $userMeta = new Model_UserMeta();
            $verified = $userMeta->getAttributeValue($developerId, 'VERIFIED_ACCOUNT');

            //Check the developer account has been aready verified
            if ($verified != '0') {
                $userModel = new Api_Model_Users();
                $userDetails = $userModel->getUserById($developerId);

                $inappPayments = new Api_Model_InappPayments();
                $paymentId = $inappPayments->addYcoinInApp($chapId, $appId, $phone, $enduserPrice, $paymentGatewayIdChap, $activationCode, $status, $transactionId, $service, $revenue, $errormessage);

                $currencyUserModel = new Api_Model_CurrencyUsers();
                $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
                $currencyRate = $currencyDetails['rate'];
                $currencyCode = $currencyDetails['code'];
                $amountInUsd = ($enduserPrice / $currencyRate);
                $amountInUsd = number_format($amountInUsd, 2, '.', '');
                
                $userAccount = new Model_UserAccount();
                $userAccount->saveRoyalitiesForInapp($appId, $amountInUsd, 'INAPP', $paymentGatewayIdChap, null, $paymentId);
                echo $paymentId; die();
            }
        }
        else{
            die('Access denied!');
        }

        print_r($_REQUEST);
        echo 'Notify';
        die();
    }

    public function redirectAction() {
        print_r($_REQUEST);
        echo 'Redirect';
        die();
    }

    public function errorAction() {
        print_r($_REQUEST);
        echo 'Error';
        die();
    }
    
}