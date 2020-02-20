<?php

class Default_TestController extends Nexva_Controller_Action_Web_MasterController {

    public function init() {
        parent::init();
        //$this->multiLanguageAction();
    }

    public function testAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();


        $file = APPLICATION_PATH . '/../public/demos/provision.zip';

        //$file = 'http://107.23.186.194/c4/exchangeexplodedfileserver/WhereFairiesDwell_NeXva_a1aa5030-f738-4496-867e-d0501dbf5bec.zip?secret=89c5db9dabb8dbd77d856a83feab6a99b2f2f08a9688dbe16edd6e918fec3cc5f3cb89dcf6d38e8a36da22c88ceb6ad8ba9d8bdfff9ad8d83a8e6a9394a66685f7acbeef&f=provision.zip';
        $post = array('provision' => '@' . $file);
        // $url = "my_url";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.mobilereloaded.com/metaflow/submit');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "metaconnect@metaflow.com:strip0");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        // For Debug mode; shows up any error encountered during the operation
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        //  curl_setopt($ch, CURLOPT_URL, $url);


        $xmlResponse = curl_exec($ch);
        curl_close($ch);

        Zend_Debug::dump($xmlResponse);




        die('dd');
    }

    public function indexAction() {



        $cat = new Model_ProductCategories();

        $categoryModel = new Model_Category();
        //Get Main Categories
        $categories = $categoryModel->getParentCategories();

        //Loop manin categories and add sub Cats
        foreach ($categories as $key => $value) {

            $noOfAppsMain = $cat->productCountByCategory($value['main_cat'], 8056);

            if ($noOfAppsMain)
                $value['app_count'] = $noOfAppsMain->app_count;
            else
                $value['app_count'] = null;

            $allCategories[$key] = $value;

            if ($value["main_cat"]) {

                //Get Sub Categories
                $subCat = $categoryModel->getSubCatsByID($value["main_cat"]);

                foreach ($subCat as $keySub => &$valueSub) {

                    $noOfAppsSub = $cat->productCountByCategory($valueSub['cat_id'], 8056);

                    if ($noOfAppsSub)
                        $valueSub['app_count'] = $noOfAppsSub->app_count;
                    else
                        $valueSub['app_count'] = 0;
                }

                $allCategories[$key]['sub_cat'] = $subCat;
            }

            $noOfAppsMain = '';
        }



        Zend_Debug::dump($allCategories);
        die();





        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);



        $awsKey = "AKIAIB7MH7NAQK55BKOQ";

        $awsSecretKey = "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";
        $bucketName = "production.applications.nexva.com";

        $defaultS3Url = "http://s3.amazonaws.com/";

        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);

        $fileName = "9963/ThermoFreeSigned_1_1.apk";
        $object = $bucketName . '/productfile/' . $fileName;

        if ($fileExist = $s3->isObjectAvailable($object)) {
            Zend_Debug::dump($fileExist);
            echo "File Exists";
        } else {
            echo "Does not exist";
        }
        die();
        return $fileExist;
    }

    public function abcAction() {

        $chapProduct = new Api_Model_ChapProducts();

        $chapId = 8056;

        $deviceId = $this->deviceAction('Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2');

        $productsToDownload = $chapProduct->getTopCategoryProducts($chapId, $deviceId, 2, 1, 0);


        if ($productsToDownload) {

            $nexApi = new Nexva_Api_NexApi();
            $productInfo = $nexApi->getProductDetails($productsToDownload, $deviceId, true);

            if (count($productInfo) > 0) {
                $this->__printArrayOfDataJson($productInfo);
            }
        } else {

            $this->__dataNotFound();
        }


        die('sss');
    }

    public function aaaAction() {

        $chapProduct = new Api_Model_ChapProducts();

        $chapId = 8056;

        $deviceId = $this->deviceAction('Mozilla/5.0 (Linux; U; Android 1.5; fr-fr; Galaxy Build/CUPCAKE) AppleWebKit/525.10+ (KHTML, like Gecko) Version/3.0.4 Mobile Safari/523.12.2');

        $chapProduct = new Api_Model_ChapProducts();
        $topProducts = $chapProduct->getTopProductsByDevice($chapId, $deviceId, true, 10, 0);


        if ($topProducts) {

            $nexApi = new Nexva_Api_NexApi();
            $productInfo = $nexApi->getProductDetails($topProducts, $deviceId, true);

            if (count($productInfo) > 0) {
                $this->__printArrayOfDataJson($productInfo);
            }
        } else {

            $this->__dataNotFound();
        }


        die('sss');
    }

    protected function deviceAction($userAgent) {



        //Iniate device detection using Device detection adapter
        $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();

        //Detect the device
        $exactMatch = $deviceDetector->detectDeviceByUserAgent($userAgent);

        //Device barand name
        $brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');

        //Get the Device ID of nexva db
        $deviceId = $deviceDetector->getNexvaDeviceId();

        return $deviceId;
    }

    private function __dataNotFound() {

        $this->getResponse()->setHeader('Content-type', 'application/json');
        echo json_encode(array("message" => "Data Not found", "error_code" => "3000"));
        exit;
    }

    /**
     * 
     * print the apps data in json format
     * @param $apps array 
     */
    private function __printArrayOfDataJson($apps) {

        $apps = str_replace('\/', '/', json_encode($apps));
        echo $apps;
        exit;
    }

    public function test1Action() {
        die('pppp');
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // XmlRpc
        $client = new Zend_XmlRpc_Client("http://10.51.0.59");

        //$client->call($method)
        $result = $client->call('ping', array('test'));

        echo '<br/><br/>XmlRpc:<br/>';

        var_dump($result);

        die('pppppp');
    }

    public function sendsmsCongoAction() 
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        include_once( APPLICATION_PATH . '/../public/vendors/php-smpp/smppclient.class.php' );
        include_once( APPLICATION_PATH . '/../public/vendors/php-smpp/gsmencoder.class.php' );
        include_once( APPLICATION_PATH . '/../public/vendors/php-smpp/sockettransport.class.php' );

        

        // Construct transport and client
        $transport = new SocketTransport(array('10.80.101.50'), 8411);

        //Zend_Debug::dump($transport);die();
        $transport->setRecvTimeout(10000);
        $smpp = new SmppClient($transport);
//Zend_Debug::dump($smpp);die();
        // Activate binary hex-output of server interaction
        $smpp->debug = true;
        $transport->debug = true;

        // Open the connection
        $yy = $transport->open();

        Zend_Debug::dump($yy);
        die();
        $x = $smpp->bindTransmitter("mtnappssm45", "nosxvfn4");

        // Optional connection specific overrides
        //SmppClient::$sms_null_terminate_octetstrings = false;
        //SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
        SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;

        // Prepare message
        $message = 'MTN Congo app-store - from neXva';
        $encodedMessage = GsmEncoder::utf8_to_gsm0338($message);
        $from = new SmppAddress('068661314', SMPP::TON_ALPHANUMERIC);
        $to = new SmppAddress('068661314', SMPP::TON_INTERNATIONAL, SMPP::NPI_E164);

        // Send
        $xx = $smpp->sendSMS($from, $to, $encodedMessage);
        Zend_Debug::dump($xx);
        die();
        // Read SMS and output
        $sms = $smpp->readSMS();
        echo "SMS:\n";
        var_dump($sms);

        // Close connection
        $smpp->close();
    }

    public function connectsmsAction() {
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $password = 'nosxvfn4';
        $username = 'mtnappssm45';

//        $stream_context = stream_context_create();
//        stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert_pro);
//        stream_context_set_option($stream_context, 'ssl', 'passphrase', $password);
//        stream_context_set_option
//        
//        $client = stream_socket_client("tcp://$addr:80", $errno, $errorMessage);

        
        
        if (!($sock = socket_create(AF_INET, SOCK_STREAM, 0))) {
            die('testing');
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Couldn't create socket: [$errorcode] $errormsg \n");
        }

        echo "Socket created \n";

        //Connect socket to remote server
        if (!socket_connect($sock, '10.80.101.50', 8411)) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Could not connect: [$errorcode] $errormsg \n");
        }
        else
        {
            echo "Connection established \n";
        }

        

//        $message = "GET / HTTP/1.1\r\n\r\n";
//
//        //Send the message to the server
//        if( ! socket_send ( $sock , $message , strlen($message) , 0))
//        {
//            $errorcode = socket_last_error();
//            $errormsg = socket_strerror($errorcode);
//
//            die("Could not send data: [$errorcode] $errormsg \n");
//        }
//
//        echo "Message send successfully \n";
        
     
    }
    
    
    public function sendsms2Action()
    {
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        //include_once( APPLICATION_PATH . '/../public/vendors/php-smpp/smppclient.class.php' );
        //include_once( APPLICATION_PATH . '/../public/vendors/php-smpp/gsmencoder.class.php' );
        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
        
        $smpphost = "10.80.101.50";
        $smppport = 8411;
        $systemid = "mtnappssm45";
        $password = "nosxvfn4";
        $system_type = "";
        $from = 'MTNAPP';

        $smpp = new SMPPClass();
        $smpp->SetSender($from);
        /* bind to smpp server */
        $smpp->Start($smpphost, $smppport, $systemid, $password, $system_type);
        /* send enquire link PDU to smpp server */
        $smpp->TestLink();
        /* send single message; large messages are automatically split */
        $x = $smpp->Send("242068661308", "Test message from neXva");
        
                /* send unicode message */
        //$smpp->Send("31648072766", "&#1589;&#1576;&#1575;&#1581;&#1575;&#1604;&#1582;&#1610;&#1585;", true);
        /* send message to multiple recipients at once */
        //$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
        /* unbind from smpp server */
        
        $smpp->End();

    }

    public function multiLanguageAction() {
        // load required classes
        //require_once 'Zend/Loader.php';
        //Zend_Loader::loadClass('Zend_Translate');
        //Zend_Loader::loadClass('Zend_Locale');
        //Zend_Loader::loadClass('Zend_Registry');
        // initialize locale and save in registry
        // auto-detect locale from browser settings
        /* try {
          $locale = new Zend_Locale('browser');//echo $locale;die();
          } catch (Zend_Locale_Exception $e) {
          $locale = new Zend_Locale('en');
          } */
        //echo $locale;
        //$registry = Zend_Registry::getInstance();
        //$registry->set('Zend_Locale', $locale);
        //die();
        // set up translation adapter
        // automatically use locale from registry
        //$tr = new Zend_Translate('array', 'lang', null, array('scan' => Zend_Translate::LOCALE_FILENAME));
        // set up translation adapter
        //$tr = new Zend_Translate('array', $fr, 'fr');
        //$tr = new Zend_Translate('array', '../lang/lang-fr.php', 'fr');
        //$tr = new Zend_Translate('array', 'lang', null, array('scan' => Zend_Translate::LOCALE_FILENAME));
        //echo LOCALE_DIRECTORY;die();
        //$tr = new Zend_Translate('array', '../lang', null, array('scan' => Zend_Translate::LOCALE_DIRECTORY));
        //$translate = new Zend_Translate('xliff',"../lang", null, array('scan' => Zend_Translate::LOCALE_DIRECTORY));
        //$translate = new Zend_Translate('gettext', '../lang', null, array('scan' => Zend_Translate::LOCALE_DIRECTORY));
        //$registry->set('Zend_Translate', $translate);
        //Zend_Debug::dump($tr);die();
        //$this->view->tr = $tr;
        //$tr = new Zend_Translate(array('adapter' => 'gettext','content' => '/lang','scan' => Zend_Translate::LOCALE_DIRECTORY));
        //$translate = new Zend_Translate('gettext',APPLICATION_PATH . "/langs/",null,array('scan' => Zend_Translate::LOCALE_DIRECTORY));
        //$registry = Zend_Registry::getInstance();
        //$registry->set('Zend_Translate', $translate);
        //$translate->setLocale('en');
    }
    
    
    public function ipTestAction() {
    
    	//	$_SERVER['HTTP_USER_AGENT'];
    
    	$to = 'chathura@nexva.com';
    	$subject = "neXva test";
    	$txt = "neXva test";
    	$headers = "From: chathura@nexva.com" . "\r\n" .
    			"CC: ";
    
    	Zend_Debug::dump(apache_request_headers(),'dd');
    	//Zend_Debug::dump($_SERVER);
    
    	//	$a = var_export($_SERVER);
    
    	$dump = var_export($_SERVER, true);
    
    	mail($to,'User-Agent',$dump,$headers);
    
    	die('<b>Thank you</b>');
    
    
    
    }
    

    	
    	public function testbAction(){
    	    
    	    echo 'sss';
    	    
    	    $users = new Api_Model_Users();
    	    $userList = $users->listChapSupportNexPayer();
    	    Zend_Debug::dump($userList);
    	    die();
    	
    		$all = new Api_Model_ChapProducts();
    		$bb  =  $all->myOpenMobileApps(23142);
    	    Zend_Debug::dump(count($bb));
    		Zend_Debug::dump($bb);
    		die();
    	
    	
    	}
    	
    	public function testcAction()
    	{
    	    
    	   $abc = new Nexva_MobileBilling_Type_Huaweinew();
    	 //  $dd =  $abc->doPayment('33644', '30712', '57743', '242066150675', 'test', '0.99');
    	   
    	     $ff =  $abc->sendsms( '242066691501', 'test', '33644');
    	   
    	   echo $dd;
    	   die();
    	}

    

}