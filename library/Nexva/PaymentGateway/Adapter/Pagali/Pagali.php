<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 7/18/14
 * Time: 3:58 PM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_PaymentGateway_Adapter_Pagali_Pagali extends Nexva_PaymentGateway_Abstract {

    private static $__instance;
    protected static $_pagali = null;
    protected $pagali_payment_vars;

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
        return "Pagali";
    }

    public function __construct($sandBox) {

        $config = Zend_Registry::get('config');

        if ($sandBox == 0) {
            $id_ent = 'E46ECE05-2971-31F3-39BD-819B8D503772';
            $id_temp = 'DA624A65-C6C9-EECE-C2FD-1DED96531A40';
            $url = 'https://www.pagali.cv/pagali/index.php?r=pgPaymentInterface/ecommercePayment';
            $redirect_url = 'http://unitelapps.nexva.com/app/post-back-pg';
        } else {
            $id_ent = 'E46ECE05-2971-31F3-39BD-819B8D503772';
            $id_temp = 'DA624A65-C6C9-EECE-C2FD-1DED96531A40';
            $url = 'https://www.pagali.cv/pagali/index.php?r=pgPaymentInterface/ecommercePayment';
            $redirect_url = 'http://unitelapps.nexva.com/app/post-back-pg';
        }

        $this->addVar("id_ent", $id_ent);
        $this->addVar("id_temp", $id_temp);
        $this->addVar("url", $url);
        $this->addVar("return", $redirect_url);
        $this->addVar("notify", $redirect_url);
    }

    public function addVar($name, $value) {
        $this->pagali_payment_vars[$name] = $value;
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
        $appId = $data['app_id'];
        $this->pagali_payment_vars['app_id'] = $data['app_id'];
        $appName = $data['app_name'];

        //echo $interopPaymentId,' - ',$chapId,' - ',$price,' - ',$appId;die();

        //$redirectUrl = $this->pagali_payment_vars['redirect_url'];
        $url = $this->pagali_payment_vars['url'];
        //$merchantId = $this->pagali_payment_vars['merchant_id'];

        //Convert the price to the local price
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        $price = ceil($currencyRate * $price);
        //$price = 3.00;

        $pgData = array(
            'id_ent' => $this->pagali_payment_vars['id_ent'],
            'id_temp' => $this->pagali_payment_vars['id_temp'],
            'order_id' => $interopPaymentId.'nexva'.$sessionId,
            'currency_code' => $currencyCode,
            'return' => $this->pagali_payment_vars['return'],
            'notify' => $this->pagali_payment_vars['notify'],
            'total' => $price,
            'item_name' => $appName,
            'quantity' => '1',
            'item_number' => $appId,
            'amount' => $price,
            'total_item' => $price,
        );

        $this->redirectPost($url, $pgData);
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
        <form name="redirectpost" method="post" action="<?php echo $redirectUrl; ?>">
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

    function generateBuildUrl($appId, $buildId) {

        $buildUrl = null;

        //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

        //Update the relevant Transaction record in the DB
        //parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

        return $buildUrl;
    }

    function handleResponse($postBackResponse, $chapId) {

        $auth = Zend_Auth::getInstance();
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId;

        $returnDetails = array();
        $returnDetails['status_message'] = $postBackResponse['payment_status'];
        $returnDetails['build_url'] = null;

        //Check if the payment has been made successfully
        if ($postBackResponse['payment_status'] == 'Completed') {

            $sessionId = Zend_Session::getId();
            $exploded =  explode( 'nexva', $postBackResponse['order_id'] ) ;

            $interopPaymentId = $exploded[0];
            $token = $exploded[1];

            //Select the relevent payment records
            $purchasedAppDetails = parent::selectInteropPayment($sessionId, $interopPaymentId);

            $buildId = $purchasedAppDetails->build_id;
            $appId = $purchasedAppDetails->app_id;
            $mobileNo = '';
            $dollarPrice = $purchasedAppDetails->price;

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
            $price = ceil($currencyRate * $dollarPrice);

            $userAccount = new Model_UserAccount();
            //$userAccount->saveRoyalitiesForApi($appId, $dollarPrice, $paymentMethod = 'CHAP', $chapId, $userId, $interopPaymentId);

            //Adding download stastics
            $ipAddress = $_SERVER['REMOTE_ADDR'];

            $model_ProductBuild = new Model_ProductBuild();
            $buildInfo = $model_ProductBuild->getBuildDetails($buildId);

            $modelDownloadStats = new Api_Model_StatisticsDownloads();
            if($userId){
                $modelDownloadStats->addDownloadStat($appId, $chapId, 'MOBILE', $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, '', $sessionId);
            } else {
                $caboAppsSession = new Zend_Session_Namespace('caboapps');
                $userId = $caboAppsSession->userId;

                $modelDownloadStats->addDownloadStat($appId, $chapId, 'API', $ipAddress, $userId, $buildId, $buildInfo->platform_id, $buildInfo->language_id, '', $sessionId);
            }


            $buildUrl = $this->generateBuildUrl($appId, $buildId);
            $returnDetails['build_url'] = $buildUrl;
            //$returnDetails['build_url'] = 'test build url';

            //Update success transaction to the relevant transaction id in the DB
            parent::updateInteropPayment($paymentTimeStamp, $paymentTransId, $paymentResult = 'success', $buildUrl, $interopPaymentId, $token);
            $returnDetails['status'] = 'success';
        } else {
            $returnDetails['status'] = 'fail';
        }

        return $returnDetails;

    }

    function getAppId($postBackResponse){

        $sessionId = Zend_Session::getId();

        $exploded =  explode( 'nexva', $postBackResponse['order_id'] ) ;
        $interopPaymentId = $exploded[0];

        //Select the relevent payment records
        $purchasedAppDetails = parent::selectInteropPayment($sessionId, $interopPaymentId);

        return $purchasedAppDetails->app_id;

    }

}