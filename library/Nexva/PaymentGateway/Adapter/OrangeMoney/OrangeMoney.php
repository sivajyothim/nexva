<?php

class Nexva_PaymentGateway_Adapter_OrangeMoney_OrangeMoney extends Nexva_PaymentGateway_Abstract {

    private static $__instance;
    protected static $_orangeMoney = null;
    protected $orange_money_payment_vars;

    private function __clone() {
        
    }

    public static function getInstance($sandBox = 0) {

        if (!isset(self::$__instance)) {
            $class = __CLASS__;
            self::$__instance = new $class($sandBox);
        }

        return self::$__instance;
    }

    public function getAdapterName() {
        return "OrangeMoney";
    }

    public function __construct($sandBox) {

        $config = Zend_Registry::get('config');

        $sandBox = 0;
        
        if ($sandBox == 0) {
            $merchant_id = '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e';
            $url = 'https://ompay.orange.ci/e-commerce/init.php';
            $redirect_url = 'https://ompay.orange.ci/e-commerce/';
        } else {
            $merchant_id = '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e';
            $url = 'https://ompay.orange.ci/e-commerce_test_gw/init.php';
            $redirect_url = 'https://ompay.orange.ci/e-commerce_test_gw/';
        }

        $this->addVar("merchant_id", $merchant_id);
        $this->addVar("url", $url);
        $this->addVar("redirect_url", $redirect_url);
    }

    public function addVar($name, $value) {
        $this->orange_money_payment_vars[$name] = $value;
    }

    public function prepare($vars = array()) {
        foreach ($vars as $key => $value) {
            $this->addVar($key, $value);
        }
    }

    function executeRequest($data) {

        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);
        //Get the session for initialize the payment
        $sessionId = Zend_Session::getId();

        $interopPaymentId = $data['interop_payment_id'];
        $chapId = $data['chap_id'];
        $price = $data['price'];

        $redirectUrl = $this->orange_money_payment_vars['redirect_url'];
        $url = $this->orange_money_payment_vars['url'];
        $merchantId = $this->orange_money_payment_vars['merchant_id'];

        //Convert the price to the local price
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        $price = ceil($currencyRate * $price);
        //$price = 3.00;

        $client = new Zend_Http_Client($url);

        $client->setHeaders(array(
            'User-Agent: Mozilla/5.0 Firefox/3.6.12',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9',
            'Accept-Language: en-us,en;q=0.5',
            'Accept-Encoding: deflate',
            'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: 109'
        ));

        $client->setParameterPost(array(
            'merchantid' => $merchantId,
            'amount' => $price,
            'sessionid' => $sessionId,
            'purchaseref' => $this->getEnc($interopPaymentId . '#WEB#' . $sessionId),
                //'purchaseref' => $interopPaymentId.'#WEB#'.$sessionId
        ));

        $response = $client->request(Zend_Http_Client::POST);
        $token = $response->getRawBody();

        $pgData = array(
            'merchantid' => $merchantId,
            'token' => trim($token),
            'amount' => $price,
            'sessionid' => $sessionId,
            'purchaseref' => $this->getEnc($interopPaymentId . '#WEB#' . $sessionId),
                //'purchaseref' => $interopPaymentId.'#WEB#'.$sessionId
        );

        //print_r($pgData); die();
        $this->redirectPost($redirectUrl, $pgData);
    }

    function handleResponse($postBackResponse, $chapId) {

        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);

        $auth = Zend_Auth::getInstance();
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId;

        //Set the error messages
        $status_message = '';
        switch ($postBackResponse['status']) {
            case -1:
                $status_message = 'Payment failed';
                break;

            case -2:
                $status_message = 'Failed to find merchant';
                break;

            case -3:
                $status_message = 'Insufficient customer’s balance';
                break;

            case -4:
                $status_message = 'Service not available';
                break;

            case -5:
                $status_message = 'Failure on OTP code';
                break;

            case -6:
                $status_message = 'Transaction terminated';
                break;

            case -7:
                $status_message = 'Incorrect parameters (telephone, code OTP)';
                break;

            case -8:
                $status_message = 'Timeout (session duration)';
                break;

            default;
                $status_message = 'Payment failed';
                break;
        }

        $sessionId = Zend_Session::getId();

        $amount = $postBackResponse['amount'];
        $clientid = $postBackResponse['clientid'];

        //$interopPaymentId = $postBackResponse['purchaseref'];

        $purchaseref = $this->getDec($postBackResponse['purchaseref']);
        $arrPurchaseResp = explode('#', $purchaseref);
        $interopPaymentId = $arrPurchaseResp[0];
        $appSession = $arrPurchaseResp[2];

        $token = $postBackResponse['token'];

        //Select the relevent payment records
        $purchasedAppDetails = parent::selectInteropPayment($sessionId, $interopPaymentId);

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
        $price = ceil($currencyRate * $priceDoller);

        //Typecast and trim added for get the same format
        $sessionId = trim($sessionId);
        $appSession = trim($appSession);
        $amount = (int) $amount;
        $price = (int) $price;

        /*
          Zend_Debug::dump($sessionId);
          Zend_Debug::dump($appSession);
          Zend_Debug::dump($amount);
          Zend_Debug::dump($price);
          die(); */


        //Check if the payment has been made successfully 
        if (($postBackResponse['status'] == 0) && ($sessionId == $appSession) && ($amount == $price)) {
            //if (1 == 1) {
            // echo $appId.'###'.$price.'###'.$chapId.'###'.$userId.'###'; die();
            //if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            //************* Add Royalties *************************
            //echo '###'.$appId.'###'.$price.'###CHAP###'.$chapId.'###'.$userId; 
            //$userAccount = new Model_UserAccount();
            //$userAccount->saveRoyalitiesForApi($appId, $priceDoller, $paymentMethod = 'CHAP', $chapId, $userId, $interopPaymentId);
            //echo 'success'; die();
            //}
            //Adding download stastics
            $ipAddress = $_SERVER['REMOTE_ADDR'];

            $model_ProductBuild = new Model_ProductBuild();
            $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

            $modelDownloadStats = new Api_Model_StatisticsDownloads();
            $modelDownloadStats->addDownloadStat($appId, $chapId, 'MOBILE', $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, '', $sessionId);
            $buildUrl = $this->generateBuildUrl($appId, $buildId);

            //Update success transaction to the relevant transaction id in the DB
            parent::updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'success', $buildUrl, $interopPaymentId, $token);

            $returnDetails['status_message'] = 'Payment was suceessful';
            $returnDetails['build_url'] = $buildUrl;
            $returnDetails['status'] = 'success';
            return $returnDetails;
        } else {
            $buildUrl = '';
            //Update success transaction to the relevant transaction id in the DB
            parent::updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = $status_message, $buildUrl, $interopPaymentId, $token);

            $returnDetails['status_message'] = $status_message;
            $returnDetails['status'] = 'fail';
            return $returnDetails;
        }
    }

    function generateBuildUrl($appId, $buildId) {

        $buildUrl = null;

        //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

        //Update the relevant Transaction record in the DB
        //parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

        return $buildUrl;
    }

    function redirectPost($redirectUrl, array $pgData) {
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
                <form name="redirectpost" method="post" action="<?= $redirectUrl; ?>">
        <?
        if (!is_null($pgData)) {
            foreach ($pgData as $k => $v) {
                echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
            }
        }
        ?>
                </form>
            </body>
        </html>
                    <?
        exit;
    }

    function getAppId($postBackResponse) {

        $sessionId = Zend_Session::getId();

        $purchaseref = $this->getDec($postBackResponse['purchaseref']);
        $arrPurchaseResp = explode('#', $purchaseref);
        $interopPaymentId = $arrPurchaseResp[0];

        //Select the relevent payment records
        $purchasedAppDetails = parent::selectInteropPayment($sessionId, $interopPaymentId);

        return $purchasedAppDetails->app_id;
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
?> 