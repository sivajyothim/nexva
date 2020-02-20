<?php

class Api_GooglewalletController extends Zend_Controller_Action {

    protected $_pgGateway;
    protected $_chapId;
    
    public function init() {
        /* Initialize actionNexpayer controller here */
        $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_pgGateway = 'GoogleWallet';
        $this->_chapId = 81604;
        
    }
    
    public function getJwtAction(){
        
        $qstring = $this->getRequest()->getParam('qstring', null);
        $decUrlStr = $this->base64urlDecode($qstring);
        $decMyStr = $this->getDecMy($decUrlStr);
        
        $arrDecMyStr = explode('_',$decMyStr);

        $appId = $arrDecMyStr[0];
        $buildId = $arrDecMyStr[1];
        $appToken = $arrDecMyStr[2];
        
        /*$appId = $this->getRequest()->getParam('id', null);
        $buildId = $this->getRequest()->getParam('build', null);
        $appToken = $this->getRequest()->getParam('appToken', null);
        $paymentGatewayName = $this->getRequest()->getParam('payment_gateway_name', null);
        $chapId = 81604;*/

        //Get payment gateway Id of the CHAP            
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($this->_chapId);
        
        //If chap has more than 1 payment method the ID will sent from view 
        if($this->_pgGateway){
            $pgType = $this->_pgGateway; 
        }
        else{
            $pgType = $pgDetails->gateway_id; 
        }
        
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        //Get product details by appId
        $productModel = new Partnermobile_Model_Products();
        $productDetails = $productModel->getDetailsById($appId);

        //$mobileNo = $auth->getIdentity()->mobile_no;//Mobile number will be null.
        $mobileNo = '';
        $price = $productDetails->price;
        $appName = $productDetails->name;
        $deviceId = $this->_deviceId;
        $pgClassName = $pgType;
        
        //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
        $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);

        //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
        $interopPaymentId = $pgClass->addMobilePayment($this->_chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId);
        
        //Set the parameters to redirection and execute
        $data = array('chap_id' => $this->_chapId, 'price' => $price, 'interop_payment_id' => $interopPaymentId, 'app_id' => $appId, 'app_name' => $appName, 'app_token' => $appToken);
        //Zend_Debug::dump($data);die();
        $jwt = $pgClass->executeRequest($data);
        echo $jwt;
    }

    public function doPaymentAction() {

        $appId = $this->_getParam('appId');
        $buildId = $this->_getParam('build_Id');
        $appToken = $this->_getParam('app_token');
        
        //$str = "id=".$appId."&build=".$buildId."&appToken=".$appToken."&payment_gateway_name=GoogleWallet";
        //$str = 'id=39528&build=71259&appToken=&payment_gateway_name=GoogleWallet';
        
        $str = $appId.'_'.$buildId.'_'.$appToken;
        $encMyStr = $this->getEncMy($str);
        $encUrlStr = $this->base64urlEncode($encMyStr);
       ?>
 
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Google Wallet Payment</title>
             <script src="https://sandbox.google.com/checkout/inapp/lib/buy.js"></script>
              <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
              <script type='text/javascript'>
                function googleWalletFunc() {
                    $.ajax({
                    type: "POST",
                    url: "http://api.nexva.com/googlewallet/get-jwt",
                    data: "qstring=<?=$encUrlStr?>",
                    success: function(jwtValue){
                        var jwt_val = jwtValue.trim();
                        google.payments.inapp.buy({
                        jwt: jwt_val,
                        success: successHandler,
                        failure: failureHandler
                      });
                      return false;
                    },
                    error: function(){

                    }
                    });
                }

                var successHandler = function(result){
                      var sellerData = result['request']['sellerData'];
                      var sellerDataArr = sellerData.split("_");
                      var sessionId = sellerDataArr[0];
                      var paymentId = sellerDataArr[1];
                      var price = result['request']['price'];
                      var status = 1;

                      $.ajax({
                            type: "POST",
                            url: "http://api.nexva.com/googlewallet/handle-google-wallet-response",
                            data: "seller_data="+sellerData+"&status="+status+"&price="+price,
                            success: function (msg){
                                window.location = "qelasygoogle://view?status=success&appid=<?= $appId ?>&token="+sessionId+"&paymentid="+paymentId;
                            }
                      });
                }

                var failureHandler = function(result){
                    var sellerData = result['request']['request']['sellerData'];
                    var sellerDataArr = sellerData.split("_");
                    var sessionId = sellerDataArr[0];
                    var paymentId = sellerDataArr[1];
                    window.location = "qelasygoogle://view?status=fail&appid=<?= $appId ?>&token="+sessionId+"&paymentid="+paymentId;
                }
              </script>
        </head>
        <body onload="googleWalletFunc()">
        </body>
        </html>
  <?php
    }
    
    public function handleGoogleWalletResponseAction() {

        error_reporting(E_ALL);
        ini_set('display_errors', 1);
            
        //$auth = Zend_Auth::getInstance();
        //$userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId;
        $chapId = 81604;
        //Check if the payment has been made successfully 
        $status = $this->getRequest()->getParam('status', null);
        
        $sellerData = $this->getRequest()->getParam('seller_data', null);
        $arrSellerData = explode('_',$sellerData);
        $interopPaymentId = $arrSellerData[1];
        $sessionId = $arrSellerData[0];
        $price = $this->getRequest()->getParam('price', null);
        $token = $sessionId;

        /*$status = 1;
        $sellerData = 'tokwr2232dsds_3199';
        $arrSellerData = explode('_',$sellerData);
        $interopPaymentId = $arrSellerData[1];
        $sessionId = $arrSellerData[0];
        $price = 0.03;
        $token = $sessionId;*/
        
        /*echo $sellerData.'##'.$paymentId.'##'.$sessionId.'##'.$price;
        die();*/
        if ($status == 1) {

            /*Zend_Session::setId($sessionId);
            $sessionUser = new Zend_Session_Namespace('partner_user');
            $userId = $sessionUser->id;           
            //echo $userId; die();*/
            
            /*$sessionUser = new Zend_Session_Namespace('partner_user');
            $userId = $sessionUser->id;*/
        
            $userId = 1;
        
             //Get payment gateway Id of the CHAP            
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $pgType = $pgDetails->gateway_id; 
            $paymentGatewayId = $pgDetails->payment_gateway_id;
            
            
            $paymentGatewayName = 'GoogleWallet';
            
            //If chap has more than 1 payment method the ID will sent from view 
            if($paymentGatewayName){
                $pgType = $paymentGatewayName; 
            }
            else{
                $pgType = $pgDetails->gateway_id; 
            }
            $pgClassName = $pgType;

            //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
            $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);
        
            //Select the relevent payment records
            $purchasedAppDetails = $pgClass->selectInteropPayment($sessionId, $interopPaymentId);
            //echo 'ip---'.$interopPaymentId;
            // print_r($purchasedAppDetails); die();
 
            $buildId = $purchasedAppDetails->build_id;
            $appId = $purchasedAppDetails->app_id;
            $mobileNo = '';
            $priceDoller = $purchasedAppDetails->price;

            $productModel = new Partnermobile_Model_Products();
            $productDetails = $productModel->getDetailsById($appId);
            $appName = $productDetails->name;

            
            $paymentTimeStamp = date('Y-m-d H:i:s');
            $paymentTransId = strtotime("now");
        
            $paymentTransId = strtotime($paymentTimeStamp);
            
           
            
            //Convert the price to the local price
            $currencyUserModel = new Api_Model_CurrencyUsers();
            $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
            $currencyRate = $currencyDetails['rate'];
            $currencyCode = $currencyDetails['code'];
            $price = ceil($currencyRate * $priceDoller);
        
            //$amount = (int)$amount;
            $price = (int)$price;

            if($status == 1) {
            //if (1 == 1) {

                //************* Add Royalties *************************
                    
                //echo '###'.$appId.'###'.$price.'###CHAP###'.$chapId.'###'.$userId; 
                //$userAccount = new Model_UserAccount();
                //$userAccount->saveRoyalitiesForApi($appId, $priceDoller, $paymentMethod = 'CHAP', $chapId, $userId);
   

                //Adding download stastics
                $ipAddress = $_SERVER['REMOTE_ADDR'];

                $model_ProductBuild = new Model_ProductBuild();
                $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

                $modelDownloadStats = new Api_Model_StatisticsDownloads();
                $modelDownloadStats->addDownloadStat($appId, $chapId, 'API', $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, '', $sessionId);
              
                $buildUrl = $this->generateOmpayBuildUrl($chapId, $appId, $buildId, $price);
 
                //echo $buildUrl; die();
                //Update success transaction to the relevant transaction id in the DB
                $pgClass->updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'success', $buildUrl, $interopPaymentId, $token);
                //return $buildUrl;
                echo 'success';
            } else {
                //Update success transaction to the relevant transaction id in the DB
                $pgClass->updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'fail', $buildUrl, $interopPaymentId, $token);
                //return $appId;
                echo 'fail';
            }
        }
        die();
    }

    
    function generateOmpayBuildUrl($chapId, $appId, $buildId, $price) {
        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];

        $amount = ceil($currencyRate * $price);

        $timeStamp = date("Ymd") . date("His");

        $paymentTransId = $timeStamp;

        $paymentTimeStamp = date('d-m-Y');

        $buildUrl = null;

        //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

        //Update the relevant Transaction record in the DB
        //parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

        return $buildUrl;
    }
        
    function getEncMy($string) {

        $key = 'neXva.inc.2014';
        // initialization vector 
        $iv = md5(md5($key));
        $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
        $output = base64_encode($output);
        return $output;
    }

    function getDecMy($string) {

        $key = 'neXva.inc.2014';
        // initialization vector 
        $iv = md5(md5($key));
        $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
        $output = rtrim($output, "");
        return $output;
    }

    function base64urlEncode($data) { 
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 

    function base64urlDecode($data) { 
      return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    } 
    
    
    function testaaAction(){
        $str = 'id=39528&build=71259&appToken=&payment_gateway_name=GoogleWallet';
        echo $enMy = $this->getEncMy($str);
        echo '<br/>';
        echo $enUr = $this->base64urlEncode($enMy);
        echo '<br/>';
        echo $deUr = $this->base64urlDecode($enUr);
        echo '<br/>';
        echo $deMy = $this->getDecMy($deUr);
        die();
    }
}