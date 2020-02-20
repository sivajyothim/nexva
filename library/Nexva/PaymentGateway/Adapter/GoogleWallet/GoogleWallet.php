<?php
include_once 'generate_token.php';
class Nexva_PaymentGateway_Adapter_GoogleWallet_GoogleWallet extends Nexva_PaymentGateway_Abstract {

    private static $__instance;
    protected static $_googleWallet = null;
    protected $googleWalletVars;

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
        return "GoogleWallet";
    }

    public function __construct($sandBox) {

        $config = Zend_Registry::get('config');

        if ($sandBox == 0) {
            $issuerId = "11504226664989288609";
			$secretKey = "GqwhHyHLyU7AZvW2IG02ww";
        } else {
            $issuerId = "11504226664989288609";
			$secretKey = "GqwhHyHLyU7AZvW2IG02ww";
        }

        $this->addVar("issuerId", $issuerId);
        $this->addVar("secretKey", $secretKey);

    }

    public function addVar($name, $value) {
        $this->googleWalletVars[$name] = $value;
    }

    public function prepare($vars = array()) {
        foreach ($vars as $key => $value) {
            $this->addVar($key, $value);
        }
    }
	
	public function generateJwt($appId, $paymentId, $appToken = null){

		include_once "lib/JWT.php";
		include_once "payload.php";
		
		$issuerId = $this->googleWalletVars['issuerId'];
		$secretKey = $this->googleWalletVars['secretKey'];

		$sellerIdentifier = SellerInfo::$issuerId;
		$sellerSecretKey = SellerInfo::$secretKey;
		
		$productModel = new Partnermobile_Model_Products();
		$productDetails = $productModel->getDetailsById($appId);
		$appName = $productDetails->name;
		$price = $productDetails->price;
                
                $productMetaModel = new Model_ProductMeta();
                $description = $productMetaModel->getAttributeValue($appId, 'BRIEF_DESCRIPTION');
                /*$currencyUserModel = new Api_Model_CurrencyUsers(); 
                $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap(81604);
                $currencyRate = $currencyDetails['rate'];
                $currencyCode = $currencyDetails['code'];
                $amount = ceil($currencyRate * $price);*/

		
                //$sessionId = Zend_Session::getId();
                
		$payload = new Payload();
		$payload->SetIssuedAt(time());
		$payload->SetExpiration(time()+3600);
		$payload->AddProperty("name", $appName);
		$payload->AddProperty("description",$description);
		$payload->AddProperty("price",$price);
		$payload->AddProperty("currencyCode", "USD");
		/*$payload->AddProperty("sellerData",
			"session:".$sessionId.",payment_id:".$paymentId);*/
		$payload->AddProperty("sellerData",$appToken."_".$paymentId);
		// Creating payload of the product.
		$Token = $payload->CreatePayload($sellerIdentifier);
		
		// Encoding payload into JWT format.
		$jwtToken = JWT::encode($Token, $sellerSecretKey);
		
		return $jwtToken; 
	}

    function executeRequest($data) {
        
        echo trim($this->generateJwt($data['app_id'], $data['interop_payment_id'], $data['app_token']));
        die();
    }

    function handleResponse($postBackResponse, $chapId) {

        $auth = Zend_Auth::getInstance();
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId;
//print_r($postBackResponse); 

$sellerData = $postBackResponse['request']['sellerData'];

$arrSellerData = explode(',', $sellerData);
$arrSellerDataClild = Array();

foreach($arrSellerData as $row){
    $arrMId = explode(':', $row);
    $arrSellerDataClild [$arrMId[0]] = $arrMId[1];
}


        
        //Check if the payment has been made successfully 
        if (1 == 1) {

            $sessionId = Zend_Session::getId();
    
            //Session id that sent to the payment gateway
            $postBackSessionId = $arrSellerDataClild['session'];

            //Price that sent to the payment gateway
            //$postBackPrice = $postBackResponse['price'];

            $transactionid = $postBackResponse['response']['orderId'];
            //Payment id that sent to the payment gateway
            $interopPaymentId =  $arrSellerDataClild['payment_id'];

            /*if( $_SERVER['REMOTE_ADDR'] == '220.247.236.99' ){
               Zend_Debug::dump($postBackResponse); 
               Zend_Debug::dump($arrClientId); 
               Zend_Debug::dump($clientId); 
               echo $interopPaymentId; die();
            }*/
            
            //Select the relevent payment records
            $purchasedAppDetails = parent::selectInteropPayment($sessionId, $interopPaymentId);

            $buildId = $purchasedAppDetails->build_id;
            $appId = $purchasedAppDetails->app_id;
            $mobileNo = '';
            $price = $purchasedAppDetails->price;

            $productModel = new Partnermobile_Model_Products();
            $productDetails = $productModel->getDetailsById($appId);
            $appName = $productDetails->name;

            $paymentTransId = date("Ymd") . date("His");
            $paymentTimeStamp = date('d-m-Y');
            $paymentTransId = strtotime($paymentTimeStamp);

            //Convert the price to the local price
            /*$currencyUserModel = new Api_Model_CurrencyUsers();
            $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
            $currencyRate = $currencyDetails['rate'];
            $price = ceil($currencyRate * $price);*/

            //echo $postBackSessionId.'###'.$sessionId.'###'.$postBackPrice.'###'.$price; die();
            
            if(trim($postBackSessionId) == trim($sessionId)) { 
            //if (1 != 1) {
                //************* Add Royalties *************************
                
                /*if( $_SERVER['REMOTE_ADDR'] == '220.247.236.99' ){
                    $userAccount = new Model_UserAccount();
                    //echo $appId.'###'.$price.'###'.$chapId.'###'.$userId; die();
                    $userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod = 'CHAP', $chapId, $userId);
                }*/

                $userAccount = new Model_UserAccount();
                $userAccount->saveRoyalitiesForApi($appId, $price, $paymentMethod = 'CHAP', $chapId, $userId, $interopPaymentId);
                    
                //Adding download stastics
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                $model_ProductBuild = new Model_ProductBuild();
                $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

                $modelDownloadStats = new Api_Model_StatisticsDownloads();
                $modelDownloadStats->addDownloadStat($appId, $chapId, 'MOBILE', $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, '', $sessionId);
                $buildUrl = $this->generateBuildUrl($appId, $buildId);

                //Update success transaction to the relevant transaction id in the DB
                parent::updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'success', $buildUrl, $interopPaymentId, $transactionid);

                return $buildUrl;
            } else {

                //Update success transaction to the relevant transaction id in the DB
                parent::updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'fail', $buildUrl, $interopPaymentId, $transactionid);

                return false;
            }
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
        $arrClientId = explode('#', $this->getDec($clientId));

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

?> 