<?php


class Default_AppController extends Nexva_Controller_Action_Web_MasterController {

  public function init() {
        parent::init();
        $this->setLastRequestedUrl();
    /* Initialize action controller here */
  }

  public function redirectAction() {
      
    if (is_null($this->_request->id)) {
        $this->_redirect('/');
    }
    $url = 'http://' . Zend_Registry::get('config')->nexva->application->base->url . "/" . $this->_request->id;
    $this->getResponse()->setRedirect($url, 301)->sendResponse();
  }

  public function reviewAction() {
    $reviewModel = new Model_Review();
    $reviews = $reviewModel->getReviewsByContentId($this->_request->id);
    $this->view->reviews = $reviews;
  }

  public function indexAction() {

    if (is_null($this->_request->id)) {
        $this->_redirect('/');
    }
    //echo $this->_request->id;die();
    $productModel = new Model_Product();
    $productInfo = $productModel->getProductDetailsById($this->_request->id);

    $productBuildModel = new Model_ProductBuild();
    $avgApproved = $productBuildModel->getAvgApproved($this->_request->id);
    $this->view->avgApproved = $avgApproved;

    if (is_null($productInfo)) {
        $this->_redirect('/'); //app not found - go back home.
    }

 

    if (isset($productInfo['deleted']) && $productInfo['deleted'] == 1) {
        $this->render('deleted');
        return;
    }  

    $recommendationsObj = new Nexva_Recommendation_Recommendation();
    $this->view->baseUrl = Zend_Registry::get('config')->nexva->application->base->url;
    $apps = $recommendationsObj->getRecommendationsForProduct($this->_request->id, $this->getSelectedDeviceIds(), 5);

    $session = new Zend_Session_Namespace('lastRequest');
    $session->lastRequestUri = $_SERVER["REQUEST_URI"];

    // get payment gateways
    $paymentGatewaysModel = new Model_PaymentGateway();
    $paymentGateways = $paymentGatewaysModel->getPaymentGatewaysThatSupport('web');

    $this->view->paymentGateways = $paymentGateways;
    $count = NULL;
    foreach ($apps as $app) {
      ++$count;
      $recommendedApps[$count]['id'] = $app['id'];
      $recommendedApps[$count]['uid'] = $app['uid'];
      $recommendedApps[$count]['name'] = $app['name'];
      $recommendedApps[$count]['nameForLinks'] = $app['name'];
      $recommendedApps[$count]['thumb'] = $app['thumb'];
      $recommendedApps[$count]['desc'] = (strlen($app['desc']) > 100) ? substr($app['desc'], 0, strpos($app['desc'], " ", 100)) . ".." : $app['desc'];
      $recommendedApps[$count]['desc_brief'] = $app['desc_brief'];
    }

    if (count($apps) == 0) {
      $this->view->hideRecommendations = true;
    } else {
      $this->view->recommendedApps = $recommendedApps;
    }

    $userId = Zend_Auth::getInstance()->getIdentity();

    //setup the new rating model and pull avg from there
    $ratingModel    = new Model_Rating();
    $this->view->avgRating      = $ratingModel->getAverageRatingByProduct($this->_request->id);    
    $this->view->totalRatings   = $ratingModel->getTotalRatingByProduct($this->_request->id);

    //check if it's already rated
    $ratingNamespace    = new Zend_Session_Namespace('Ratings');

    $productRated       = false;
    $userRating         = 1;
    if (isset($ratingNamespace->ratedProducts)) {
        $ratedProducts          = $ratingNamespace->ratedProducts;
        if (isset($ratedProducts[$this->_request->id])) {
            $productRated   = true;
            $userRating     = $ratedProducts[$this->_request->id];
        }
    }
    
    $ratingLevels       = array();
    $ratingLevels[1]    = 'Uninstalling it now';
    $ratingLevels[2]    = 'Meh, could do better';
    $ratingLevels[3]    = 'Nothing special';
    $ratingLevels[4]    = 'Pretty good';
    $ratingLevels[5]    = 'Awesome!';
        
    $this->view->productRated   = $productRated;
    $this->view->userRating     = $userRating;
    $this->view->ratingLevels   = $ratingLevels;
    
    if (isset($userId)) {
      $this->view->userLoggedIn = true;
    }
    
    $reviewModel    = new Model_Review();
    $reviews        = $reviewModel->getReviewsByContentId($this->_request->id);
    $evasReviews    = $reviewModel->getReviewsByContentId($this->_request->id, 1, 'EVA');
    
    $this->view->reviews    = $reviews;
    $this->view->evaReview  = $evasReviews;

    $categoryModel = new Model_Category();

      //@todo: find a better way to handle this condition - die() is not appropriate.
    if ($this->_request->preview) {
      $adminUrl = Zend_Registry::get('config')->nexva->application->admin->url;

      if (isset($_SERVER['HTTP_REFERER'])) {
        if ($this->_request->uid != $productInfo['uid'] && false == strstr($_SERVER['HTTP_REFERER'], $adminUrl))
          die('Invalid request: You do not have permission to preview this application');
      }else {
        die('Invalid request: You do not have permission to preview this application');
      }
    }
    

    
    /** Statistics **/
    $stats          = new Nexva_Analytics_ProductView();
    $devices        = $this->getSelectedDevices();
    $loggedDevice   = array();
    if (is_array($devices) && !empty($devices)) {
        foreach ($devices as $deviceId => $device) { 
            $loggedDevice[$deviceId]    = $device['phone'];
        }
    } else {
        $loggedDevice['0']  = 'Web';    
    }
    $opts           = array(
        'app_id'    => $this->_request->id, 
        'device_id'   => $loggedDevice,
        'cp_id'     => $productInfo['uid']
    );
    $stats->log($opts);

    $this->view->productInfo = $productInfo;
    $userMeta = new Model_UserMeta();
    $compnayName = $userMeta->getAttributeValue($productInfo['uid'], 'COMPANY_NAME');
    preg_match_all('/[a-zA-Z0-9\s]+/', $compnayName, $match);
    $compnayName = implode('', $match[0]);
    $this->view->cpName = str_replace(' ', '-', $compnayName);
    $this->view->cpId = $productInfo['uid'];

    //added 09/09/14 (viraj) for get parent category id by app id
    $productCategoryModel = new Model_ProductCategories();
    $categoryDetails = $productCategoryModel->getParentCategoryByProductId($this->_request->id);

    if(isset($categoryDetails)){
        $this->view->categoryBreadcrumbs = $categoryModel->getCategoryBreadcrumb($categoryDetails[0]->id);
        $this->view->categoryId = $categoryDetails[0]->id;
    }
    //added ends here 09/09/14

    /*if (isset($productInfo['categories'][0])) {
      //$this->view->categoryBreadcrumbs = $categoryModel->getCategoryBreadcrumb($productInfo['categories'][0]);
      $this->view->categoryBreadcrumbs = $categoryModel->getCategoryBreadcrumb(1);
      //set the category id to select
      $this->view->categoryId = $productInfo['categories'][0];
    }*/

    $this->view->productCompatibleDevices = $productModel->getFormattedProductCompatibleDevices($this->_request->id);

    $this->view->headTitle()->setSeparator(' - ');
    $this->view->headTitle($productInfo['name']);
    $this->view->headTitle($productInfo['user_meta']->COMPANY_NAME);
    $this->view->headTitle("neXva.com");
    $this->view->headMeta()->appendName('keywords', $productInfo['keywords'] . "," . $productInfo['name'] . "," . $productInfo['user_meta']->COMPANY_NAME .
        "," . $productInfo['platform_name'] . ", neXva, nexva.com");

    //FB open-graph stuff
    /**
    $this->view->headMeta()->appendMeta('og:title', $productInfo['name']);
    
    $this->view->headMeta()->appendProperty('og:description', $productInfo['desc_brief']);
    $this->view->headMeta()->appendProperty('og:image', 'http://' . Zend_Registry::get('config')->nexva->application->base->url . $productInfo['thumb']);
    $this->view->headMeta()->appendProperty('og:site_name', 'neXva');
    $this->view->headMeta()->appendProperty('og:url',
        'http://' . Zend_Registry::get('config')->nexva->application->base->url . "/" . $productInfo['id']);
    **/

    //oembed discovery endpoints for neXlinker
   /*
    $this->view->headLink()->headLink(
        array(
            'rel' => 'alternate',
            'type' => 'text/xml+oembed',
            'title' => 'Blah',
            'href' =>  'http://'.Zend_Registry::get('config')->nexva->application->base->url.'/nexlinker/oembed/url/'.
                urlencode(urlencode('http://'.Zend_Registry::get('config')->nexva->application->base->url.'/'.$this->_request->id)).'/format/xml'
        )
    );
    *
    */

    /*$nexApi = new Nexva_Api_NexApi();
    $productDetails = $nexApi->detailsAppAction($productId,null,$this->_chapId,$this->_chapLanguageId);*/

    $this->view->flashMessenger = $this->_helper->getHelper('FlashMessenger');
  }

  public function qrcodeAction() {
    $this->_helper->viewRenderer->setNoRender(true);
    $this->_helper->getHelper('layout')->disableLayout();
    $config = Zend_Registry::get('config');

    $productModel = new Model_Product();
    $productInfo = $productModel->getProductDetailsById($this->_request->id);

    $slugHelper = new Nexva_View_Helper_Slug();
    $slug = $slugHelper->slug($productInfo['name']);

    $ref = $this->_getParam('ref', 'qr');
    $height = $this->_getParam('h', '125');
    $width = $this->_getParam('w', '125');

    $qrcodeHelper = new Nexva_View_Helper_Qr();
    $qrcode = $qrcodeHelper->qr("http://" . $config->nexva->application->base->url . "/app/$slug/" . $this->_request->id . '/ref/' . $ref,
            $height, $width);

    //grab QR code. @todo: perhaps this should be in the view helper
    $handle = fopen($qrcode, "rb");
    $contents = stream_get_contents($handle);
    fclose($handle);

    //@todo: set far future expire headers so this wont be requested again.
    //@todo: perhaps write $contents to a file (in /public/cache/qrcodes/) and read it from there before generating.
    //and display it:
    $this->getResponse()->setHeader('Content-type', 'image/png');
    $this->getResponse()->setBody($contents);
  }

  public function buyAction() {
    // @TODO : add the download counter
    $this->_helper->viewRenderer->setNoRender(true);
    $this->_helper->getHelper('layout')->disableLayout();
    $pgID = $this->_request->pgid;

    // TODO : get the payment dateway name and send it to factory
    $payementGatewaysModel = new Model_PaymentGateway();
    $pgRow = $payementGatewaysModel->find($pgID);
    $pgRow = $pgRow->current();
    $factoryProduct = $pgRow->gateway_id;
    $paymentGateway = Nexva_PaymentGateway_Factory::factory($factoryProduct, $factoryProduct);
//    $paymentGateway = new Nexva_PaymentGateway_PayPal();
    // get user details
    $userId = Zend_Auth::getInstance()->getIdentity()->id;
		if (empty ( $userId )) {
			$session = new Zend_Session_Namespace ( 'lastRequest' );
			$session->lastRequestUri = $_SERVER ['REQUEST_URI'];
			$this->_redirect ( '/user/login' );
		
		}

    $productId = $this->_request->id;
    $userMeta = new Model_UserMeta();
    $userMeta->setEntityId($userId);
    //get product details
    $product = new Model_Product();
    $prodcutDetails = $product->getProductDetailsById($productId);
    // set session to get returinig url
    $session = new Zend_Session_Namespace('lastRequest');
//    $session->lastRequestUri = $_SERVER["REQUEST_URI"];
    $session->product_id = $productId;
    $session->payment_gateway_id = $pgID;
    // session Id
    $sessionId = Zend_Session::getId();
    // IPN
    $postback = "http://" . $_SERVER['SERVER_NAME'] . '/app/postback?sessionid=' . $sessionId;
    $paypalNotify = "http://" . $_SERVER['SERVER_NAME'] . '/app/postbackpaypal';
    // Success URL
    $success = "http://" . $_SERVER['SERVER_NAME'] . '/app/' . Nexva_View_Helper_Slug::slug($prodcutDetails['name'] . '-for-' . $prodcutDetails['platform_name']) . '.' . $prodcutDetails['id'];
//        $customData = array('uid' => $userId, 'productId' => $productId);
    $vars = array(
      'return' => $success,
      'cancel_return' => $success,
      'notify_url' => $paypalNotify,
      'success_url' => $postback,
      'item_name' => $prodcutDetails['name'],
      'item_desc' => $prodcutDetails['name'],
      'item_price' => $prodcutDetails['cost'],
      'item_reference' => $prodcutDetails['id'],
      'amount' => $prodcutDetails['cost'],
      'custom' => $userId . '&' . $productId,
      'no_shipping' => 1
    );
    $paymentGateway->Prepare($vars);
    $paymentGateway->Execute();
  }

  public function postbackpaypalAction() {

    if($_REQUEST["payment_status"] == "Refunded" || $_REQUEST["payment_status"] == "Reversed") {

    }

    $this->_helper->viewRenderer->setNoRender(true);
    $this->_helper->getHelper('layout')->disableLayout();

    $logger = new Zend_Log();
    $path_to_log_file = '/var/www/vhosts/mobilereloaded.com/httpdocs/public/paypal.log';
    $writer = new Zend_Log_Writer_Stream($path_to_log_file);
    $logger->addWriter($writer);
    $logger->log('called', Zend_Log::INFO);

    $paymentGateway = new Nexva_PaymentGateway_Adapter_Paypal_Paypal();
    if ($paymentGateway->IpnValidate()) {
      $ipnPostData = $paymentGateway->getIpnData();
      //get the transacton id
      $transId = $ipnPostData['txn_id'];
      $customData = explode('&', $ipnPostData['custom']);
      $userId = $customData[0];
      $productId = $customData[1];
    }
    else
      return;

    // check the product already exists on the order details table
    $orders = new Model_Order();
    //  if product id is already in the table then redirec to download file
    if ($orders->isPruchsed($productId))
      return;

    $product = new Model_Product();
    $productDetail = $product->getProductDetailsById($productId);
    // Orders

    $orderData = array(
      'user_id' => $userId,
      'order_date' => new Zend_Db_Expr('NOW()'),
      'merchant_id' => 'nexva_paypal',
      'transaction_id' => $transId
    );
    $orderId = $orders->insert($orderData);
    // Orders details
    $orderDetails = new Model_OrderDetail();
    $orderDetailsData = array(
      'order_id' => $orderId,
      'product_id' => $productDetail['id'],
      'price' => $productDetail['cost']
    );
    $orderDetails->insert($orderDetailsData);
    // User Account

    $userAccount = new Model_UserAccount();
    // @TODO : add the payment gateway ID here
    $userAccount->saveRoyalities($productId, 3);

    $data = array(
      'order_id' => $orderId,
      'transaction_id' => $transId,
      'payment_gateway' => 'Paypal'
    );

    $orders->sendInvoice($productId, $data);
    $orders->sendNotificationOfPurchase($orderId);
  }

  public function postbackAction() {

    $this->_helper->viewRenderer->setNoRender(true);
    $this->_helper->getHelper('layout')->disableLayout();

    // get the session id
    $sessionId = $this->_request->sessionid;
    // recreate the session
    session_id($sessionId);
    // get user Id
    $userId = Zend_Auth::getInstance()->getIdentity()->id;
    // get product id from session

    $session = new Zend_Session_Namespace('lastRequest');
    $productId = $session->product_id;
    if (empty($productId)) {
        $this->_redirect($session->lastRequestUri);
    }
    $product        = new Model_Product();
    $productDetail  = $product->getProductDetailsById($productId, true);
    
    $pgId       = $session->payment_gateway_id;
    $payementGatewaysModel = new Model_PaymentGateway();
    $pgRow      = $payementGatewaysModel->find($pgId);
    $pgRow      = $pgRow->current();
    
    $factoryProduct = $pgRow->gateway_id;
    $paymentGateway = Nexva_PaymentGateway_Factory::factory($factoryProduct, $factoryProduct);
    if (!$paymentGateway->validate()) {
      $this->_redirect('/app/purchased-.' . $productId);
    }
    // check the product already exists on the order details table
    $orders = new Model_Order();
    //  if product id is already in the table then redirec to download file
    if ($orders->isPruchsed($productId))
      $this->_redirect('/app/purchased-.' . $productId);

    $transId = $this->_request->transkey;  
    $orderId    = $orders->insertOrder($productId, $userId, $transId);
    // User Account

    $userAccount = new Model_UserAccount();
    // @TODO : add the payment gateway ID here
    $userAccount->saveRoyalities($productId, $pgId);

    $promoStats = new Nexva_Analytics_ProductPurchase();
    $data   = array(
        'app_id'    => $productId, 
        'price'     => $productDetail['cost'], 
        'device_id' => 0,
        'device_name'   => 'Web',
        'chap_id'   => null,
        'platform'  => 'WEB'
    );
    $promoStats->log($data);
    
    $data = array(
      'order_id' => $orderId,
      'transaction_id' => $transId, 
      'payment_gateway' => $factoryProduct
    );
    $orders->sendInvoice($productId, $data);

    $orders->sendNotificationOfPurchase($orderId);
    $this->_redirect('/app/purchased-.' . $productId);
  }

  public function purchasedAction() {

    //ensure the usr is first logged in
    if( is_null(Zend_Auth::getInstance ()->getIdentity()) )
    {
        $this->setLastRequestedUrl();
        $this->_redirect('/user/login/message/'.urlencode('Login to your neXva account to view your downloads'));
    }

    $devices = $this->_request->id;
    $userModel = new Model_User();
    $products = $userModel->getPurchasedProduct($devices);
    $prod = array();
    $productModel = new Model_Product();
    foreach ($products as $product) {
      $prod[] = $productModel->getProductDetailsById($product->product_id);
    }

    $this->view->products = $prod;

    // set the devices
    $devices = $this->getSelectedDevices();
    $this->view->selectedDevices = $devices;
  }

    

public function postbackpaypalcpAction() {

    $this->_helper->viewRenderer->setNoRender(true);
    $this->_helper->getHelper('layout')->disableLayout();

    

    $paymentGateway = new Nexva_PaymentGateway_Adapter_Paypal_Paypal();
    if ($paymentGateway->IpnValidate()) {
      $ipnPostData = $paymentGateway->getIpnData();
      //get the transacton id
      $transId = $ipnPostData['txn_id'];
      $customData = explode('&', $ipnPostData['custom']);
      $userId = $customData[0];
      $amount = $customData[1];
    }
    else
   		return;

   
    $invoices = new Model_CpInvoices();

    $invoiceData = array(
      'user_id' => $userId,
      'date' => new Zend_Db_Expr('NOW()'),
      'amount' => $amount,
      'payment_gateway' => 'PAYPAL',    	
      'transaction_id' =>   $transId
    );
    
    	
    $invoiceId =  $invoices->insert($invoiceData);
    
    $userAccount = new Model_UserAccount();
    // @TODO : add the payment gateway ID here
    $userAccount->creditCp($userId, $amount, $invoiceId, 'CP');


    $data = array(
      'invoice_id' => $invoiceId,
      'transaction_id' => $transId,
      'payment_gateway' => 'PAYPAL',
      'amount' => $amount
    );
    
    $invoices->sendInvoice($userId, $data);



  }

    public function generateDynamicKeyAction() {
        
        //ensure the usr is first logged in
        if( is_null(Zend_Auth::getInstance ()->getIdentity()) )
        {
            $this->setLastRequestedUrl();
            $this->_redirect('/user/login/message/'.urlencode('Login to your neXva account to generate your license key'));
        }

        $this->view->error = "";
        $this->view->request_error = "";

        //check if the logged in user has bought this app in the first place
        $orderModel = new Model_Order();
        $order = $orderModel->findOrder($this->_request->id);
        $this->view->order = $order;

        $orderDetailsModel = new Model_OrderDetail();
        $orderDetails = $orderDetailsModel->findOrderDetails($this->_request->id);

        $validOrder = !is_null($orderDetails);

        if( $validOrder ) {

            $hasPurchased = $orderModel->isPruchsed($orderDetails->product_id); //@todo: fix typo: isPruchsed(). it's being used in too many places. shouldn't this method be in Model_User?
            $this->view->has_purchased = $hasPurchased;

            $productKeyModel = new Model_OrderProductKey();
            $key =  $productKeyModel->getProductKey($this->_request->id);
            $this->view->product_key = $key;

            $userModel = new Model_User();
            $user =  $userModel->fetchRow('id = '. $order->user_id);

            if( $this->getRequest()->isPost() ) {

                if( !is_null($key)) //already exists
                    throw new Zend_Exception('Invalid request: Product key already exists. Cannot create new product key');

                $productKeysModel = new Model_ProductKey();
                $endpoint = $productKeysModel->licenceKeyForProduct($orderDetails->product_id); //figure out where to post. see ticket #351

                $productModel = new Model_Product();
                $product = $productModel->getProductDetailsById($orderDetails->product_id);

                $cpMeta = new Cpbo_Model_UserMeta();
                $cpMeta->setEntityId($product['uid']);


                /**
                What needs to be sent:
                - email : The email address of the user who purchased the item
                - id : The id of the app
                - app_name: The name of the app
                - app_version: The version of the app (not support as of yet)
                - transaction_id: The transaction id generated by the payment gateway that processed the payment.
                - nonce : A random 32 digit hexadecimal numbers
                - secret : A MD5 hash of your unique neXva account secret concatenated with a nonce (described above).
                - test : 1 or 0
                 *
                 */

                $nonce = uniqid();

                $postArray = array(
                        'post_fields' => array (
                            'imei' => $this->_request->imei,
                            'endpoint' => $endpoint,

                            'email' => $user->email,
                            'id' => $product['id'],
                            'app_name' => $product['name'],
                            'transaction_id' =>  $order->transaction_id,
                            'nonce' => $nonce,
                            'secret' => md5($cpMeta->ACCOUNT_ID.$nonce),
                            'test' => 0
                        )
                    );

                $dynamicKeyGen = new Nexva_DynamicKeyGenerator_PostToScript(
                    array(
                        'endpoint' => $endpoint,
                        'post_fields' => $postArray
                    )
                );

                $response = $dynamicKeyGen->getKey();

                switch ($dynamicKeyGen->getLastHttpResponseCode()) {
                    case 200:
                        //response was good.. save this key
                        $key = $dynamicKeyGen->getLastHttpResponse();

                        $productKeyModel->saveProductKey($this->_request->id, $key);
                        Zend_Registry::get('logger')->info("Generated product key '$key' for order id: ".$this->_request->id);

                        $this->view->license_key = $key;
                        $this->_redirect('/app/generate-dynamic-key/id/'.$this->_request->id);
                        break;
                    default:
                        $this->view->request_error = "Unable to generate license key: Invalid response from license server (HTTP/".$dynamicKeyGen->getLastHttpResponseCode().")";
                        Zend_Registry::get('logger')->warn("Unable to generate product key: Invalid HTTP response recieved: HTTP "
                                .$dynamicKeyGen->getLastHttpResponseCode()." for order ID ".$this->_request->id);
                }
            } //post
        } //validOrder

    }

    function getAvgApproved()
    {

    }


}





