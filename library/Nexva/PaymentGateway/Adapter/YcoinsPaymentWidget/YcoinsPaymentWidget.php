<?php

class Nexva_PaymentGateway_Adapter_YcoinsPaymentWidget_YcoinsPaymentWidget extends Nexva_PaymentGateway_Abstract {

    private static $__instance;
    protected static $_ycoinsPaymentWidget = null;
    protected $ycoins_payment_vars;

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
        return "YcoinsPaymentWidget";
    }

    public function __construct($sandBox) {

        $config = Zend_Registry::get('config');

        $sandBox = 0;

        if ($sandBox == 0) {
            $api_key = 'de3eddb0c42b38f3b093ea76e8c2ad4b';
            $api_password = '';
            $url = 'https://www.centili.com/widget/WidgetModule';
        } else {
            $api_key = '619f305b7a1ce0b36ad86f2c0502d976';
            $api_password = '';
            $url = 'http://demo.centili.com/widget/WidgetModule';
        }

        $this->addVar("api_key", $api_key);
        $this->addVar("api_password", $api_password);
        $this->addVar("url", $url);
    }

    public function addVar($name, $value) {
        $this->ycoins_payment_vars[$name] = $value;
    }

    public function prepare($vars = array()) {
        foreach ($vars as $key => $value) {
            $this->addVar($key, $value);
        }
    }

    function executeRequest($data) {

        //Get the session for initialize the payment
        $sessionId = Zend_Session::getId();

        $interopPaymentId = $data['interop_payment_id'];
        $chapId = $data['chap_id'];
        $price = $data['price'];

        //Encrypt the session and payment id

        $clientIdEnc = $this->getEnc($sessionId . '#' . $interopPaymentId);
        $clientIdEnc = rawurlencode($clientIdEnc);

        $url = $this->ycoins_payment_vars['url'];
        $api_key = $this->ycoins_payment_vars['api_key'];

        //Convert the price to the local price
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        $price = ceil($currencyRate * $price);

        if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99') {
            /* Zend_Debug::dump($sessionId); 
              Zend_Debug::dump($interopPaymentId);
              Zend_Debug::dump($clientIdEnc);
              echo $clientIdEnc; die(); */
            $price = 3.00;
        }

        //Create signature

        $obj = new Encryption();

        $amount = $price;
        $api = $api_key;
        //$clientIdEnc = $sessionId.'#'.$interopPaymentId;
        $clientIdEnc = $sessionId . '_' . $interopPaymentId;
        $clientid = $clientIdEnc;
        /* echo $clientIdEnc.'<br>';

          $clientid = $obj->encrypt($clientIdEnc);

          echo $clientid.'<br>';
          $clientid2 = $obj->decrypt($clientid);
          echo $clientid2;
          die(); */
        $country = 'jp';
        $countrylock = 'true';
        $mobile = 'true';
        $signKey = 'Centili';

        $conc = $amount . $api . $clientid . $country . $countrylock . $mobile;
        $conc = strtolower($conc);
        //echo $amount.'##'.$api.'##'.$clientid.'##'.$country.'##'.$countrylock.'##'.$mobile; 
        //echo '<br><br>'.$conc;


        $signature = hash_hmac('sha1', $conc, $signKey);

        //echo '<br><br>'.$signature;die();
        //Get the helper
        $redirector = new Zend_Controller_Action_Helper_Redirector();

        //Redirect the relevent parameters to YCoins payment login window
        $redirector->gotoUrl($url . '?amount=' . $price . '&api=' . $api_key . '&clientid=' . $clientIdEnc . '&country=jp&countrylock=true&mobile=true&sign=' . $signature);
    }

    function handleResponse($postBackResponse, $chapId) {

        //print_r($postBackResponse); die();
        $auth = Zend_Auth::getInstance();
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId;

        $returnDetails = array();

        $filters = array
            (
            'status' => array('StringTrim'),
            'clientid' => array('StringTrim'),
            'transactionid' => array('StringTrim')
        );

        $validators = array(
            'status' => array(
                new Zend_Validate_NotEmpty(),
                new Zend_Validate_Alpha()
            ),
            'clientid' => array(
                new Zend_Validate_NotEmpty(),
                //new Zend_Validate_Alnum()
                new Zend_Validate_Regex(array('pattern' => '/^[\w.-]*$/'))
            ),
            'transactionid' => array(
                new Zend_Validate_NotEmpty(),
                new Zend_Validate_Digits()
            )
        );

        $inputValidation = new Zend_Filter_Input($filters, $validators, $postBackResponse);

        //Check if the payment has been made successfully 

        /*if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99') {
            echo $inputValidation->isValid();
            print_r($postBackResponse);
            die();
        }*/
        
        if ($inputValidation->isValid()) {

            $sessionId = Zend_Session::getId();
            //This is the encrypted cliendId that send from above function
            //$clientId = rawurldecode($postBackResponse['clientid']);
            $clientId = $postBackResponse['clientid'];
            //$clientId = str_replace(' ', '', $clientId);
            //$arrClientId = explode('#', $this->getDec($clientId));

            $arrClientId = explode('_', $clientId);

            //Session id that sent to the payment gateway
            $postBackSessionId = $arrClientId[0];

            //Price that sent to the payment gateway
            //$postBackPrice = $postBackResponse['price'];

            //$transactionid = $postBackResponse['transactionid'];
            //Payment id that sent to the payment gateway
            $interopPaymentId = $arrClientId[1];

            /* if( $_SERVER['REMOTE_ADDR'] == '220.247.236.99' ){
              Zend_Debug::dump($postBackResponse);
              Zend_Debug::dump($arrClientId);
              Zend_Debug::dump($clientId);
              echo $interopPaymentId; die();
              } */

            //Select the relevent payment records
            $purchasedAppDetails = parent::selectInteropPayment($sessionId, $interopPaymentId);

            /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                print_r($purchasedAppDetails); die();
            }*/
            
            $buildId = $purchasedAppDetails->build_id;
            $appId = $purchasedAppDetails->app_id;
            $mobileNo = '';
            $token = $purchasedAppDetails->token;
            $transId = $purchasedAppDetails->trans_id;
            $buildUrl = $purchasedAppDetails->downlaod_link;
            $status = $purchasedAppDetails->status;
            //$errorMsg = $purchasedAppDetails->error_msg;
            
            if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                //echo $buildId.'##'.$appId.'##'.$token.'##'.$transId.'##'.$buildUrl.'##'.$status; die();
            }
            
                    
            //Check same user, same session, same app should be block for ycoins
        

            
            $productModel = new Partnermobile_Model_Products();
            $productDetails = $productModel->getDetailsById($appId);
            $appName = $productDetails->name;

            $paymentTimeStamp = date('Y-m-d H:i:s');
            $paymentTransId = strtotime("now");
        
            //$paymentTransId = date("Ymd") . date("His");
            //$paymentTimeStamp = date('d-m-Y');
            //$paymentTransId = strtotime($paymentTimeStamp);

            //Convert the price to the local price
            /* $currencyUserModel = new Api_Model_CurrencyUsers();
              $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
              $currencyRate = $currencyDetails['rate'];
              $price = ceil($currencyRate * $price); */

            //echo $postBackSessionId.'###'.$sessionId.'###'.$postBackPrice.'###'.$price; die();

            $returnDetails['build_url'] = null;
            if ($status == 'success' && trim($sessionId) == trim($token)) {

                //if (1 != 1) {
                //************* Add Royalties *************************

                /* if( $_SERVER['REMOTE_ADDR'] == '220.247.236.99' ){
                  $userAccount = new Model_UserAccount();
                  //echo $appId.'###'.$price.'###'.$chapId.'###'.$userId; die();
                  $userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod = 'CHAP', $chapId, $userId);
                  } */

                //$userAccount = new Model_UserAccount();
                //$userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod = 'CHAP', $chapId, $userId, $interopPaymentId);

                //Adding download stastics
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                $model_ProductBuild = new Model_ProductBuild();
                $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

                $modelDownloadStats = new Api_Model_StatisticsDownloads();
                $modelDownloadStats->addDownloadStat($appId, $chapId, 'MOBILE', $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, '', $sessionId);
                //$buildUrl = $this->generateBuildUrl($appId, $buildId);

                //Update success transaction to the relevant transaction id in the DB
                //parent::updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'success', $buildUrl, $interopPaymentId, $transactionid);

                //echo 'inside'.$buildUrl; die();

                if($buildUrl){
                    $returnDetails['status_message'] = 'Payment was suceessful';
                    $returnDetails['build_url'] = $buildUrl;
                    $returnDetails['status'] = 'success';
                }
                else{
                    $returnDetails['status_message'] = 'Build URL not found';
                    $returnDetails['build_url'] = '';
                    $returnDetails['status'] = 'success';
                }

                return $returnDetails;
              
            } else {

                $returnDetails['status_message'] = 'Payment was unsuceessful';
                $returnDetails['status'] = 'fail';
                return $returnDetails;
            }
        } else {
            $returnDetails['status_message'] = 'Invalid response';
            $returnDetails['status'] = 'fail';
            return $returnDetails;
        }
    }

    function generateBuildUrl($appId, $buildId) {

        $buildUrl = null;
        //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

        return $buildUrl;
    }

    function getAppId($postBackResponse) {

        $sessionId = Zend_Session::getId();

        //This is the encrypted cliendId that send from above function
        $clientId = $postBackResponse['clientid'];
        //$arrClientId = explode('#', $this->getDec($clientId));
        $arrClientId = explode('_', $clientId);

        //Payment id that sent to the payment gateway
        $interopPaymentId = $arrClientId[1];

        //Select the relevent payment records
        $purchasedAppDetails = parent::selectInteropPayment($sessionId, $interopPaymentId);

        return $purchasedAppDetails->app_id;
    }

    function getEnc($string) {

        $key = 'neXvainc2014';
        // initialization vector 
        $iv = md5(md5($key));
        $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
        $output = base64_encode($output);
        return $output;
    }

    function getDec($string) {

        $key = 'neXvainc2014';
        // initialization vector 
        $iv = md5(md5($key));
        $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
        $output = rtrim($output, "");
        return $output;
    }

}

class Encryption {

    var $skey = "SuPerEncKey2010"; // you can change it

    public function safe_b64encode($string) {

        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    public function safe_b64decode($string) {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function encode($value) {

        if (!$value) {
            return false;
        }
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->skey, $text, MCRYPT_MODE_ECB, $iv);
        return trim($this->safe_b64encode($crypttext));
    }

    public function decode($value) {

        if (!$value) {
            return false;
        }
        $crypttext = $this->safe_b64decode($value);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->skey, $crypttext, MCRYPT_MODE_ECB, $iv);
        return trim($decrypttext);
    }

    public function encrypt($data, $passphrase = 'nexva') {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC); //create a random initialization vector to use with CBC encoding
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $key = pack('H*', $passphrase . $passphrase);
        return base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv));
    }

    public function decrypt($data, $passphrase = 'nexva') {
        $data = base64_decode($data);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC); //retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
        $iv = substr($data, 0, $iv_size);
        $data = substr($data, $iv_size); //retrieves the cipher text (everything except the $iv_size in the front)
        $key = pack('H*', $passphrase . $passphrase);
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv), chr(0));
    }

}