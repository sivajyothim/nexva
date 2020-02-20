<?php

class Api_OrangemoneyController extends Zend_Controller_Action {

    public function init() {
        /* Initialize actionNexpayer controller here */
        $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
    }

    public function notifyAction() {
       /* echo 'notify';
        print_r($_REQUEST);
        die();*/
        $this->redirectAction();
    }

    public function redirectAction() {
       
       //print_r($_REQUEST); die();
        
        /*$this->_request->setPost(array(
            'someKey' => 'someValue'
        ));*/
        //$this->_forward('post-back-pg', 'App', 'partnermobile');
        //$aPost = $this->_request->getPost();
        $postBackResponse = $this->_request->getParams();
        if($postBackResponse){
            $purchaseref = $this->getDec($postBackResponse['purchaseref']);
            $arrPurchaseResp = explode('#', $purchaseref);
            $interopPaymentId = $arrPurchaseResp[0];
            $type = $arrPurchaseResp[1];
            
            //print_r($arrPurchaseResp); die();
            /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                echo $interopPaymentId.'----'.$type; die();
            }*/
            //qelasyapp://view?status=".$status."&ompay_token=".$_REQUEST['token']."&amount=".$_REQUEST['amount']."&app_token=1234&payment_id=".$interopPaymentId
            //print_r($arrPurchaseResp); echo $interopPaymentId; die();
            //echo $type;
            //Zend_Debug::dump($postBackResponse);
            //Zend_Debug::dump($postBackResponse['status']); die();
                
            if($type == 'APP'){

                $amount = $postBackResponse['amount'];
                $clientid = $postBackResponse['clientid'];
                //$interopPaymentId = $_REQUEST['purchaseref'];
                $token = $postBackResponse['token'];
                $appToken = $arrPurchaseResp[2];
                
                //Status codes and relevent error messages need to be added
                if($postBackResponse['status'] == 0){
                    $status = 'success';
                }
                else{
                    $status = 'fail';
                }
                
                ///Zend_Debug::dump($postBackResponse);
                ///Zend_Debug::dump($postBackResponse['status']); die();
                
                $this->handleOmpayResponse($postBackResponse);
                
                /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                    header("location: qelasyapp://view?status_code=".$postBackResponse['status']);
                }
                else{
                    header("location: qelasyorange://view?status=".$status."&ompay_token=".$token."&amount=".$amount."&payment_id=".$interopPaymentId."&status_code=".$postBackResponse['status']);
                }*/
                header("location: qelasyorange://view?status=".$status."&ompay_token=".$token."&amount=".$amount."&payment_id=".$interopPaymentId."&status_code=".$postBackResponse['status']);
                
                //header("location: qelasyapp://view?status=".$status."&ompay_token=".$token."&amount=".$amount."&payment_id=".$interopPaymentId."&app_token=".$appToken."&status_code=".$postBackResponse['status']);
            }
             
            if($type == 'WEB'){
              
                //$baseUrl = 'http://'.$_SERVER['SERVER_NAME'];
                $baseUrl = 'http://store.qelasy.com';
                $this->_redirector = $this->_helper->getHelper('Redirector');
                $this->_redirector->setCode(307)
                   ->setUseAbsoluteUri(true)
                   ->gotoUrl($baseUrl . '/app/post-back-pg',
                        array()
                   );
            }
        }
        else{
            echo 'No response found!';
        }

        die();
    }
    
    public function handleOmpayResponse($postBackResponse, $chapId = 81604) {

        error_reporting(E_ALL);
        ini_set('display_errors', 1);
            
        //$auth = Zend_Auth::getInstance();
        //$userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId;

        //Check if the payment has been made successfully 
        if ($postBackResponse['status'] == 0) {

            $amount = $postBackResponse['amount'];
            $clientid = $postBackResponse['clientid'];
            //$interopPaymentId = $postBackResponse['purchaseref'];
            $token = $postBackResponse['token'];
            
            $purchaseref = $this->getDec($postBackResponse['purchaseref']);
            //$purchaseref = $postBackResponse['purchaseref'];
            $arrPurchaseResp = explode('#', $purchaseref);
            $interopPaymentId = $arrPurchaseResp[0];
            $sessionId = $arrPurchaseResp[2];

            Zend_Session::setId($sessionId);
            $sessionUser = new Zend_Session_Namespace('partner_user');
            $userId = $sessionUser->id;
            
            /*$sessionUser = new Zend_Session_Namespace('partner_user');
            $userId = $sessionUser->id;*/
        
            //$userId = 1;
        
             //Get payment gateway Id of the CHAP            
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $pgType = $pgDetails->gateway_id; 
            $paymentGatewayId = $pgDetails->payment_gateway_id;
            $pgClassName = $pgType;

            //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
            $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);
        
            //Select the relevent payment records
            $purchasedAppDetails = $pgClass->selectInteropPayment($sessionId, $interopPaymentId);

 
            $buildId = $purchasedAppDetails->build_id;
            $appId = $purchasedAppDetails->app_id;
            $mobileNo = '';
            $priceDoller = $purchasedAppDetails->price;

            $productModel = new Partnermobile_Model_Products();
            $productDetails = $productModel->getDetailsById($appId);
            $appName = $productDetails->name;

            $paymentTransId = date("Ymd") . date("His");
            $paymentTimeStamp = date('d-m-Y');
            $paymentTransId = strtotime($paymentTimeStamp);
            
            //Convert the price to the local price
            $currencyUserModel = new Api_Model_CurrencyUsers();
            $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
            $currencyRate = $currencyDetails['rate'];
            $currencyCode = $currencyDetails['code'];
            $price = ceil($currencyRate * $priceDoller);
        
            $amount = (int)$amount;
            $price = (int)$price;

            if($amount  == $price) {
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
                return $buildUrl;
            } else {
                //Update success transaction to the relevant transaction id in the DB
                $pgClass->updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'fail', $buildUrl, $interopPaymentId, $token);
                return $appId;
            }
        }
        //die();
    }

    public function errorAction() {
        //Redirect to the same function for redirection
        $this->redirectAction();
    }
    
    public function ompayRedirectionAction(){

          if($this->_getParam('token')){  
            $token = $this->_getParam('token', null);
            $paymentId = $this->_getParam('payment_id', null);
            $chapId = $this->_getParam('chap_id', null);
            $sessionId = $this->_getParam('session_id', null);;
            
            //echo $token.'###'.$paymentId.'###'.$chapId.'###'.$sessionId.'###';
            //Get payment gateway Id of the CHAP            
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $pgType = $pgDetails->gateway_id; 
            $paymentGatewayId = $pgDetails->payment_gateway_id;
            $pgClassName = $pgType;

            //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
            $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);
        
            //Select the relevent payment records
            $purchasedAppDetails = $pgClass->selectInteropPayment($sessionId, $paymentId);

            $price = $purchasedAppDetails->price;
            
            
            //Convert the price to the local price
            $currencyUserModel = new Api_Model_CurrencyUsers();
            $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
            $currencyRate = $currencyDetails['rate'];
            $currencyCode = $currencyDetails['code'];
            $price = ceil($currencyRate * $price);
        
            //echo $token.'###'.$paymentId.'###'.$chapId.'###'.$sessionId.'###'.$price;
            //die();
            
            $purchaseref = $pgClass->getEnc($paymentId.'#APP#'.$sessionId);
            
            $sandBox = 0;
            if ($sandBox == 0) {
                $merchantId = '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e';
                $url = 'https://ompay.orange.ci/e-commerce/init.php';
                $redirect_url = 'https://ompay.orange.ci/e-commerce/';
            } else {
                $merchantId = '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e';
                $url = 'https://ompay.orange.ci/e-commerce_test_gw/init.php';
                $redirect_url = 'https://ompay.orange.ci/e-commerce_test_gw/';
            }

        ?>
            
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <script type="text/javascript">
                    function closethisasap() {
                        document.forms["redirectpost"].submit();
                    }
                </script>
            </head>
            <body onload="closethisasap();">
                <form name="redirectpost" method="post" action="<?= $redirect_url ?>">
                    <input type="hidden" name="merchantid" value="<?= $merchantId ?>"/>
                    <input type="hidden" name="token" value="<?= $token ?>"/>
                    <input type="hidden" name="sessionid" value="<?= $sessionId ?>"/>
                    <input type="hidden" name="purchaseref" value="<?= $purchaseref ?>"/>
                    <input type="hidden" name="amount" value="<?= $price ?>"/>
                </form>
            </body>
        </html>
        
        <?php
          }
          else{
              
          }
        //die('Hi');
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
    
        
    function getEnc($string) {

        $key = 'neXva.inc.2014';
        // initialization vector 
        $iv = md5(md5($key));
        $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
        $output = base64_encode($output);
        return $output;
    }

    function getDec($string) {

        $key = 'neXva.inc.2014';
        // initialization vector 
        $iv = md5(md5($key));
        $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
        $output = rtrim($output, "");
        return $output;
    }

}