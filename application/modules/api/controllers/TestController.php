<?php

/*
 * 
 * this is for the paythru testing for this can be deleted 
 * 
 */


class Api_TestController extends Zend_Controller_Action
{

        
    public function init() {
        
     //   ini_set("session.use_trans_sid",true);
       /* ini_set("session.use_cookies",false);
        ini_set("session.use_only_cookies",false);
        Zend_Session::setId('tkiithgsjptqdtsecrh6rv5v64');
        
       session_id('tkiithgsjptqdtsecrh6rv5v64'); session_start();
        
       // session_id('m35gau7iqdtgrjrkhul34pp9q2');
        $sessionUser = new Zend_Session_Namespace('api_nexpayer');
        
        echo $sessionUser->id;
        
       // die('cc');
        */

        
        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        
        /* Initialize actionNexpayer controller here */
        $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        global $HTTP_RAW_POST_DATA;
        
    }
    
    
    public function airtelSriLankaAction(){


    	//echo 'testing';die();
    	//$client = new nusoap_client('http://172.16.1.185:8080/dbill?serviceNode=SMSC',false);
    	$client = new nusoap_client('http://10.200.182.146:9080/dbill?serviceNode=IACCR',false);
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	/*$xml = '<?xml version="1.0" encoding="UTF-8"?>
    	 <ocsRequest>
    	<serviceNode>SMSC</serviceNode>
    	<sequenceNo>060309431450545700</sequenceNo>
    	<requestType>4</requestType>
    	<cpcgFlag>1</cpcgFlag>
    	<callingParty>94755841077</callingParty>
    	<calledParty>9810599383</calledParty>
    	<startTime>1370232800</startTime>
    	<serviceId>SMSO</serviceId>
    	<serviceType>SMS</serviceType>
    	<callDuration>1</callDuration>
    	<callDirection>O</callDirection>
    	</ocsRequest>';*///94757474481
    	//94755841077
    
    	$xml = '<?xml version="1.0" encoding="UTF-8"?>
    	<ocsRequest>
    	<serviceNode>IACCR</serviceNode>
    	<sequenceNo>060309431450545703</sequenceNo>
    	<requestType>4</requestType>
    	<cpcgFlag>1</cpcgFlag>
    	<callingParty>94757474481</callingParty>
    	<calledParty>94757474481</calledParty>
    	<portedCalledParty>NA</portedCalledParty>
    	<startTime>1370232800</startTime>
    	<serviceId>5798</serviceId>
    	<serviceType>wap_Appstore_5</serviceType>
    	<callDuration>1</callDuration>
    	<callDirection>O</callDirection>
    	</ocsRequest>';
    	//94757474481
    	$result=$client->send($xml,'POST');
    	
    	$xml = trim($client->response);
    	$bb = strpos($xml,"<?");

    	$xml = substr($xml, $bb);

    	$resultArray = simplexml_load_string($xml);

    	if($resultArray->result == 'OK' or $resultArray->result == 'Subscriber does not exist in ABMF database'){
    	    echo 'BBBBBBBBBBBBBBBBBBBBBBBBBBB';
    	}  else {
    	    echo 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    	    
    	}

    	
    	
    	 
    	echo '-----------------------------------------------Request-------------------------------------------------','<br/>';
    	echo '--------------------------------------------------------------------------------------------------------';
    	Zend_Debug::dump($client->request);
    	echo '-----------------------------------------------Response--------------------------------------------------','<br/>';
    	echo '--------------------------------------------------------------------------------------------------------';
    	Zend_Debug::dump($client->response);
    	echo '--------------------------------------------------------------------------------------------------------','<br/>';
    	echo '--------------------------------------------------------------------------------------------------------';
    	Zend_Debug::dump($result);die();
    }
    
    
    public function testmtsAction()
    {
    
     $sss  =  new Nexva_MobileBilling_Type_TelekomSrbija();
     $sss->sendsms('381641800244', 'test');
        
     die();
    
    }
    
    

    
    
    
    public function testsmsairtelslAction()
    {
    
        $ch = curl_init();
    	
    	$message = 'dddd';
    	
    	$message = urlencode($message);
    	
    	$msisdn = '94757474481';

    	
    	$str = 'http://10.200.186.1/cgi-local/sendsms.pl?login=nexva&pass=nex123&sms='.$message.'&msisdn='.$msisdn.'&src=nexva&type=text';
    	
    	$url = $str;
    
    	// set url
    	curl_setopt($ch, CURLOPT_URL, $url);
    
    	//return the transfer as a string
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    	// $output contains the output string
    	$output = curl_exec($ch);
    	
    	Zend_Debug::dump($output);
    	
    	
    
    	
    	
    
    	// close curl resource to free up system resources
    	curl_close($ch);
    	
    	die();
    
    }
    
    
    
    public function aaAction() {
     
        
    	header("location: myapp://view?id=123");
    	die();
    
    
    }
    
    
    
    
    
        
    public function sssAction()
    {
        
        $cache          = Zend_Registry::get('cache');
        $cacheKey       = 'PRODUCT_BASE_' . trim('ddddd');
        $cacheKey       = str_ireplace('-', '_', $cacheKey);
        
        Zend_Debug::Dump($cacheKey);
            $cache->remove($cacheKey);
            
            die();
        
    }
    
    public function ssssAction()
    {
        
        
    	include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
    
    	$mobileNo = '08032006348';
    
    	$amount = 150;
    	$paymentTimeStamp = date('YmdHis');
    
    	$paymentResult = 'Success';
    
    	$client1 = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
    	$client1->soap_defencoding = 'UTF-8';
    	$client1->decode_utf8 = false;
    
    	$timeStamp = date("Ymd").date("His");
    
    	$spId =  2340110000426;
    	$pass = 'nexT321';
    
    	$password = md5($spId.$pass.$timeStamp);
    
    	$header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => $mobileNo, 'FA' => $mobileNo));
    
    	$phone = array(
    			'addresses'     =>  'tel:'.$mobileNo,
    			'senderName'   =>  99999998,
    			'message'  => 'test.'
    	);
    
    	$result = $client1->call('sendSms', $phone, '', '', $header);
    
    	die($result);
    
    
    	//$aa = new  Nexva_MobileBilling_Type_Huawei();
    	//  $aa->doPayment(21134, 57945, 30828, '08032006348', 'test', '1');
    
    	die();
    
    
    	$message = 'SSSS SFD 353453b   trhrth rth rth ytj tyjyu jyujy jjy jyj yj.';
    
    	$validator = new Zend_Validate_Alnum(array('allowWhiteSpace' => true));
    	if($validator->isValid($message)) {
    		echo 'b';
    	} else {
    		echo 'a';
    	}
    
    	die();
    	/* Initialize action controller here */
    
    
    }
    
    
    
    
    public function userAgentAction() {
    
    
    	$_SERVER['HTTP_USER_AGENT'];
    
    	$to = 'chathura@nexva.com';
    	$subject = "My subject";
    	$txt = "Hello world!";
     	$headers = "From: chathura@nexva.com" . "\r\n" ;//.
    		//	"CC: shaun@nexva.com";
    
    	mail($to,'User-Agent',$_SERVER['HTTP_USER_AGENT'],$headers);
    
    	die('<b>Thank you</b> Your UA is :- '. $_SERVER['HTTP_USER_AGENT']);
    
    
    
    }
    
    
    public function ipTestAction() {
    
    //	$_SERVER['HTTP_USER_AGENT'];
    
    	$to = 'chathura@nexva.com';
    	$subject = "neXva test";
    	$txt = "neXva test";
    	$headers = "From: chathura@nexva.com" . "\r\n" .
    			"CC: ";
    	
    	Zend_Debug::dump(apache_request_headers());
    	Zend_Debug::dump($_SERVER);
    
    	//	$a = var_export($_SERVER);
    
    	$dump = var_export($_SERVER, true);
    
    	mail($to,'User-Agent',$dump,$headers);
    
    	die('<b>Thank you</b>');
    
    
    
    }
    
    //Tet function to test smtp mails
    public function testZendMailAction(){
        //echo 123; die();
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->addTo('chathura@nexva.com')
        ->setSubject('Test email for test SMTP')
        ->setBodyText('Testing body');
        $mailer->send();
        echo 'done'; die();
                
    }
    
    //Function for print header MSISDN
    public function headerMsisdnAction(){
        $headers = apache_request_headers();
        
        Zend_Debug::dump(        $headers);
        die();
        
        
        
        
        echo $headers['Y-MSISDN'].'###';
        echo @$_SERVER['Y-MSISDN'].'###';
        die();
    }
    
    
    
    public function smpp3Action() {
    
    	include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/class.smpp.php' );
    	 
    	$src = "3369570516"; // or text
    	$dst = "336957051534534";
    	$message = "Test Message";
    
    	$s = new smpps;
    	$s->debug=0;
    
    	// $host,$port,$system_id,$password
    	$s->open("pmg-acg-sms01.ref1.lightsurf.net", 8008, "1432","n3x41A99");
    
    
    
    	// $source_addr,$destintation_addr,$short_message,$utf=0,$flash=0
    	Zend_Debug::dump($s->send_long($src, $dst, $message));
    
    	/* To send unicode
    	 $utf = true;
    	$message = iconv('Windows-1256','UTF-16BE',$message);
    	$s->send_long($src, $dst, $message, $utf);
    	*/
    
    	$s->close();
    
    
    
    	 
    	 
    	die();
    
    
    
    }
    
    
    
    public function smpp2Action() {
    
    	include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
    	//  $smpp->bindTransmitter("1432","n3x41A99");
    
    	// Optional connection specific overrides
    	//pmg-acg-sms01.ref1.lightsurf.net
    
    	print "<pre>";
    	$tx=new SMPP('pmg-acg-sms01.ref1.lightsurf.net','8008');
    	$tx->debug=true;
    	// $tx->system_type="WWW";
    	$tx->addr_npi=1;
    	//print "open status: ".$tx->state."\n";
    	Zend_Debug::dump( $tx->bindTransmitter("1432","n3x41A99"));
    
    
    	$tx->sms_source_addr_npi=1;
    	//$tx->sms_source_addr_ton=1;
    	$tx->sms_dest_addr_ton=1;
    	$tx->sms_dest_addr_npi=1;
    	Zend_Debug::dump($tx->sendSMS("13369570516",'13369570515',"Hello world!"));
    	$tx->close();
    	unset($tx);
    	print "</pre>";
    
    	die('ddd');
    
    
    
    }
    
    public function testsmsAction() {
    
    	$bb = new  Nexva_MobileBilling_Type_Huawei();
    	 
    	$bb = $bb->sendSms('08103114401', 'test safasfas', $chapId=22);
    	 
    	Zend_Debug::dump(   $bb );
    
    	die();
    
    
    
    }
    
    
    public function testcwwAction() {
    
    
    	$pgUsersModel = new Api_Model_PaymentGatewayUsers();
    	$pgDetails = $pgUsersModel->getGatewayDetailsByChap(15267);
    
    	$pgType = $pgDetails->gateway_id;
    	$paymentGatewayId = $pgDetails->payment_gateway_id;
    	 
    	//Call Nexva_MobileBilling_Factory and create relevant instance
    	$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
    	 
    	//$cc = $pgClass->sendsms('3369570515', 'test test', $chapId=22);
    	 
    	$cc = $pgClass->doPayment(22, 37234, 15214, '3369570515', 'test', 1);
    
    	Zend_Debug::dump($cc);
    	 
    	 
    	 
    	//	$cc = $pgClass->sendsms('3369570515', 'test test', $chapId=22);
    	 
    	//	Zend_Debug::dump($cc);
    
    	 
    	 
    
    	die();
    
    
    
    }
    
    public function curlAction()
    {
    
    	// $URL =  'http://api.mobilereloaded.com/huaweingn/sendsms/mobile_no/2347060553995/message/test';
    	$URL =  'http://api.mobilereloaded.com/huaweingn/sendsms';
    
    	$ch = curl_init($URL);
    
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    
    	$fields = array(
    			'mobile_no' => urlencode(2347060553995),
    			'message' => urlencode('test')
    	);
    
    	$fields_string = '';
    
    	//url-ify the data for the POST
    	foreach($fields as $key=>$value) {
    		$fields_string .= $key.'='.$value.'&';
    	}
    
    	rtrim($fields_string, '&');
    
    	curl_setopt($ch,CURLOPT_POST, count($fields));
    	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    
    
    	$xml = json_decode(curl_exec($ch));
    
    	Zend_Debug::dump($xml->status);
    	curl_close($ch);
    
    	die();
    
    
    }
    
    
    
    public function smppAction() {
    
    	include_once( APPLICATION_PATH.'/../public/vendors/php-smpp/smppclient.class.php' );
    	include_once( APPLICATION_PATH.'/../public/vendors/php-smpp/gsmencoder.class.php' );
    	include_once( APPLICATION_PATH.'/../public/vendors/php-smpp/sockettransport.class.php' );
    
    
    	// Construct transport and client
    	//$transport = new SocketTransport(array('pmg-acg-sms01.ref1.lightsurf.net'),8008);
    
    	$transport = new SocketTransport(array('pmg-acg-sms01.ref1.lightsurf.net'),'8008');
    
    
    
    	$transport->setRecvTimeout(10000);
    	$smpp = new SmppClient($transport);
    
    
    
    	// Activate binary hex-output of server interaction
    	$smpp->debug = true;
    	$transport->debug = true;
    
    	// Open the connection
    	// $transport->open();
    	$transport->open();
    
    	$smpp->bindTransmitter("1432","n3x41A99");
    
    	// Optional connection specific overrides
    	SmppClient::$sms_null_terminate_octetstrings = false;
    	SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
    	SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;
    
    	// Prepare message
    	$message = 'Hello world';
    	//  $encodedMessage = GsmEncoder::utf8_to_gsm0338($message);
    	$encodedMessage = GsmEncoder::utf8_to_gsm0338($message);
    
    
    	$from = new SmppAddress('13369570515', SMPP::TON_ALPHANUMERIC);
    	$to = new SmppAddress('13369570515', SMPP::TON_INTERNATIONAL,SMPP::NPI_E164);
    
    
    
    	// Send
    	//  $smpp->sendSMS($from,$to,$encodedMessage,$tags);
    
    	Zend_Debug::dump($smpp->sendSMS($from,$to,$encodedMessage));
    
    	// Close connection
    	$smpp->close();
    	die('dd');
    
    }
    
    public function indexAction()
    {
    
    
    	$mailer = new Nexva_Util_Mailer_Mailer();
    	Zend_Debug::dump( $mailer->addTo('cj19820508@gmail.com', 'cj19820508@gmail.com')
    			->setSubject('Reset your neXva account password.')
    			->setLayout("generic_mail_template")
    			->setMailVar("name", 'chathura')
    			->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
    			->sendHTMLMail('forgotpassword.phtml'));
    
    
    	die('dddd');
    
    	$to = 'cj19820508@gmail.com';
    	$subject = "My subject";
    	$txt = "Hello world!";
    	$headers = "From: cj19820508@gmail.com" . "\r\n" .
    			"CC: chathura@nexva.com";
    
    	Zend_Debug::dump(  mail($to,$subject,$txt,$headers));
    
    	die();
    
    
    
    	phpinfo();
    
    
    	$to = 'cj19820508@gmail.com';
    	$subject = "My subject";
    	$txt = "Hello world!";
    	$headers = "From: cj19820508@gmail.com" . "\r\n" .
    			"CC: chathura@nexva.com";
    
    	Zend_Debug::dump(  mail($to,$subject,$txt,$headers));
    
    	Zend_Debug::dump(  mail('chathura@nexva.com',$subject,$txt,$headers));
    
    	$mailer = new Nexva_Util_Mailer_Mailer();
    	Zend_Debug::dump( $mailer->addTo('cj19820508@gmail.com', 'cj19820508@gmail.com')
    			->setSubject('Reset your neXva account password.')
    			->setLayout("generic_mail_template")
    			->setMailVar("name", 'chathura')
    			->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
    			->sendHTMLMail('forgotpassword.phtml'));
    
    
    	die('dddd');
    	phpinfo();
    
    	$mailer = new Nexva_Util_Mailer_Mailer();
    	$mailer->addTo('cj19820508@gmail.com', 'cj19820508@gmail.com')
    	->setSubject('Verify your neXva account')
    	->setLayout("generic_mail_template")
    	->setMailVar("name",'chathura')
    	->setMailVar("email", 'cj19820508@mail.com')
    	->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
    	->sendHTMLMail('verify_account_cpbo.phtml');
    
    	$mailer = new Nexva_Util_Mailer_Mailer();
    	$mailer->addTo('cj19820508@gmail.com', 'cj19820508@gmail.com')
    	->setSubject('Reset your neXva account password.')
    	->setLayout("generic_mail_template")
    	->setMailVar("name", 'chathura')
    	->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
    	->sendHTMLMail('forgotpassword.phtml');
    
    	 
    
    
    	$to = 'cj19820508@gmail.com';
    	$subject = "My subject";
    	$txt = "Hello world!";
    	$headers = "From: cj19820508@gmail.com" . "\r\n" .
    			"CC: chathura@nexva.com";
    
    	mail($to,$subject,$txt,$headers);
    
    
    
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    
    	// initialize SOAP client
    	$options = array(
    			'location' => 'http://pmg-acg-mdn-validate.ref1.lightsurf.net',
    			'uri'      => 'http://pmg-acg-mdn-validate.ref1.lightsurf.net'
    	);
    	 
    	try {
    		// add a new product
    		// get and display product ID
    		$user = array('aggregatorId' => '1432', 'pwd' => 'n3x41A99');
    
    		$p = array(
    				'user'     => $user,
    				'version' => '1.0',
    				'min'     => 15403057987,
    				'productId'  => 'xxx_nt_999',
    				'shortCode' => 'xxxx',
    				'carrierId' => 401,
    				'sendUAProf' => 'Y',
    				'sendSpendingCapability' => 'Y'
    		);
    
    		$client = new Zend_Soap_Client('', $options);
    		$id = $client->validateMDN($p);
    		 
    		Zend_Debug::dump(Zend_Soap_Client::getLastRequest());
    
    		 
    		echo 'Added product with ID';
    	} catch (SoapFault $s) {
    		die('ERROR: [' . $s->faultcode . '] ' . $s->faultstring);
    	} catch (Exception $e) {
    
    		Zend_Debug::dump($e->getTraceAsString());
    		Zend_Debug::dump( $e->getMessage());
    
    
    		die('ERROR: ' . $e->getMessage());
    	}
    
    
    	 
    }
    
    
    
    
    
    public function validateMdnAction() {
    
    	$err = '';
    
    
    	//$client = new nusoap_client('http://pmg-acg-mdn-validate.ref1.lightsurf.net/pmgvalidate/services/validate');
    
    
    	//  $client = new nusoap_client('http://pmg-acg-mdn-validate.verisign-grs.net/pmgvalidate/services/validate');
    
    	$client = new nusoap_client('http://pmg-acg-validate.syniverse.com/pmgvalidate/services/validate');
    
    	//  $client = new nusoap_client('http://pmg-acg-mdn-validate.verisign-grs.net/pmgvalidate/services/validate');
    
    
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	 
    	$user = array('aggregatorId' => 1423, 'pwd' => 'n3x41A99', 'version' => '1.0');
    
    
    	//3369570515
    
    	//18042161341
    
    	//
    
    	$p = array(
    			'user'     => $user,
    			'min'     => '13369570515',
    			'productId'  => 'nexva_nt_059',
    			'shortCode' => '6171',
    			'carrierId' => 404,
    			'sendUAProf' => 'Y',
    			'sendSpendingCapability' => 'Y'
    	);
    
    	$result = $client->call('validateMDN', $p);
    	 
    
    	if ($client->fault) {
    		echo '<h2>Fault</h2><pre>';
    		print_r($result);
    		echo '</pre>';
    	} else {
    		$err = $client->getError();
    		if ($err) {
    			echo '<h2>Error</h2><pre>' . $err . '</pre>';
    		} else {
    			echo '<h2>Result</h2><pre>';
    			// Decode the result: it so happens we sent Latin-1 characters
    			if (isset($result['return'])) {
    				$result1 = utf8_decode($result['return']);
    			} elseif (!is_array($result)) {
    				$result1 = utf8_decode($result);
    			} else {
    				$result1 = $result;
    			}
    			print_r($result1);
    			echo '</pre>';
    		}
    	}
    
    
    	echo '<h2>Request</h2>';
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2>';
    
    
    	if ($err) {
    		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    		echo '<h2>Debug</h2>';
    		echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
    		 
    	}
    
    	die();
    	Zend_Debug::dump($client->getDebug());
    }
    
    
    public function chargeSubscriberAction() {
    
    
    	$err = '';
    	 
    	//http://pmg-acg-mdn-validate.verisign-grs.net
    
    	//$client = new nusoap_client('http://pmg-acg-billing.ref1.lightsurf.net/pmgxml/services/premiumcharging');
    	 
    	//$client = new nusoap_client('http://pmg-acg-billing.verisign-grs.net/pmgxml/services/premiumcharging');
    	 
    	$client = new nusoap_client('http://pmg-acg-billing.syniverse.com/pmgxml/services/premiumcharging');
    
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    
    	$user = array('aggregatorId' => 1423, 'pwd' => 'n3x41A99', 'version' => '1.0');
    
    	$p = array(
    			'user'     => $user,
    			'min'     => '13364013614',
    			'productId'  => 'nexva_nt_100',
    			'shortCode' => '6171',
    			'carrierId' => 404,
    			'chargeId' => '114'.date("Ymd").date("His"),
    			'msgtxid' => 'NOID'
    	);
    
    	$result = $client->call('chargeSubscriber', $p);
    
    
    	if ($client->fault) {
    		echo '<h2>Fault</h2><pre>';
    		print_r($result);
    		echo '</pre>';
    	} else {
    		$err = $client->getError();
    		if ($err) {
    			echo '<h2>Error</h2><pre>' . $err . '</pre>';
    		} else {
    			echo '<h2>Result</h2><pre>';
    			// Decode the result: it so happens we sent Latin-1 characters
    			if (isset($result['return'])) {
    				$result1 = utf8_decode($result['return']);
    			} elseif (!is_array($result)) {
    				$result1 = utf8_decode($result);
    			} else {
    				$result1 = $result;
    			}
    			print_r($result1);
    			echo '</pre>';
    		}
    	}
    
    
    	echo '<h2>Request</h2>';
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2>';
    
    
    	if ($err) {
    		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    		echo '<h2>Debug</h2>';
    		echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
    		 
    	}
    
    	die();
    	Zend_Debug::dump($client->getDebug());
    }
    
    
    
    public function chargeSubscriberMtnTestCaseTestbedAction() {
    
    	$err = '';
    	 
    	$client = new nusoap_client('http://41.206.4.219:8310/AmountChargingService/services/AmountCharging');
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    	 
    	$timeStamp = date("Ymd").date("His");
    	 
    	$spId = 2340110000211;
    	$pass = 'nexT123';
    
    	$password = md5($spId.$pass.$timeStamp);
    
    	$header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '', 'timeStamp' => $timeStamp, 'OA' => '2347060553995'));
    	 
    	//<v2:OA>2347060553995</v2:OA>
    	//<v2:FA>2347060553995</v2:FA>
    
    	$charge = array(
    			 
    			'description' => 'this is test',
    			'currency' => 'NGN',
    			'amount' => 100,
    			'code' => 10090
    	);
    	 
    
    	//2348034571296
    	 
    	$paymentInfo = array(
    			'endUserIdentifier'     => '2348034571296',
    			'charge'   => $charge,
    			'referenceCode'  => 'text123'
    	);
    	 
    	$paymentInfo = array(
    			'endUserIdentifier'     => '2348034571296',
    			'charge'   => $charge,
    			'referenceCode'  => 'text123'
    	);
    
    	$result = $client->call('chargeAmount', $paymentInfo, '', '', $header);
    
    
    	if ($client->fault) {
    		echo '<h2>Fault</h2><pre>';
    		print_r($result);
    		print_r('dddddddd');
    		echo '</pre>';
    
    		echo '<h2>FaultT</h2><pre>';
    
    		Zend_Debug::dump($result["faultcode"]);
    
    
    	} else {
    		$err = $client->getError();
    		if ($err) {
    			echo '<h2>Error</h2><pre>' . $err . '</pre>';
    		} else {
    			echo '<h2>Result</h2><pre>';
    			// Decode the result: it so happens we sent Latin-1 characters
    			if (isset($result['return'])) {
    				$result1 = utf8_decode($result['return']);
    			} elseif (!is_array($result)) {
    				$result1 = utf8_decode($result);
    			} else {
    				$result1 = $result;
    			}
    			print_r($result1);
    			echo '</pre>';
    		}
    	}
    
    
    	echo '<h2>Request</h2>';
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2>';
    
    
    	 
    
    	if ($err) {
    		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    		echo '<h2>Debug</h2>';
    		echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
    		 
    	}
    	 
    	die();
    
    }
    
    
    public function sendSmsProductionAction() {
    
    	$err = '';
    
    	$client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    	 
    	$timeStamp = date("Ymd").date("His");
    
    	$spId =  2340110000426;
    	$pass = 'nexT321';
    	 
    	//08032001535
    
    	$password = md5($spId.$pass.$timeStamp);
    
    	$header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => '2347060553995', 'FA' => '2347060553995'));
    
    	//'addresses'     =>  'tel:2347060553995',
    	 
    	 
    	$phone = array(
    			'addresses'     =>  'tel:08100504589',
    			'senderName'   =>  99999998,
    			'message'  => 'Hello from nexva'
    	);
    
    	$result = $client->call('sendSms', $phone, '', '', $header);
    
    
    	if ($client->fault) {
    		echo '<h2>Fault</h2><pre>';
    		print_r($result);
    		echo '</pre>';
    	} else {
    		$err = $client->getError();
    		if ($err) {
    			echo '<h2>Error</h2><pre>' . $err . '</pre>';
    		} else {
    			echo '<h2>Result</h2><pre>';
    			// Decode the result: it so happens we sent Latin-1 characters
    			if (isset($result['return'])) {
    				$result1 = utf8_decode($result['return']);
    			} elseif (!is_array($result)) {
    				$result1 = utf8_decode($result);
    			} else {
    				$result1 = $result;
    			}
    			print_r($result1);
    			echo '</pre>';
    		}
    	}
    
    
    	echo '<h2>Request</h2>';
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2>';
    
    
    	if ($err) {
    		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    		echo '<h2>Debug</h2>';
    		echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
    		 
    	}
    
    	die();
    
    }
    
    
    public function sendSmsProductionAbcAction() {
    
    
    
    	$err = '';
    
    	$client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$timeStamp = date("Ymd").date("His");
    
    	$spId =  2340110000426;
    	$pass = 'nexT321';
    
    	$password = md5($spId.$pass.$timeStamp);
    	 
    
    
    	$header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => '2347060553995', 'FA' => '2347060553995'));
    
    	$phone = array(
    			'addresses'     =>  'tel:2347060553995',
    			'senderName'   =>  99999998,
    			'message'  => 'MTN Nigeria ABC '
    	);
    
    	$result = $client->call('sendSms', $phone, '', '', $header);
    
    	 
    
    
    	if ($client->fault) {
    		//echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
    	} else {
    			
    		$err = $client->getError();
    		//Zend_Debug::dump($client->getDebug());
    
    		if ($err) {
    			//Zend_Debug::dump($err);
    			//Zend_Debug::dump($client->getDebug());
    
    		} else {
    			Zend_Debug::dump($result);
    		}
    
    	}
    	//	Zend_Debug::dump($result);
    
    	 
    
    	echo '<h2>Request</h2>';
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2>';
    
    	die('ddd');
    }
    
    
    public function sendSmsProduction2Action() {
    
    
    
    	$err = '';
    
    	$client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$timeStamp = date("Ymd").date("His");
    
    	$spId =  2340110000426;
    	$pass = 'nexT321';
    
    	$password = md5($spId.$pass.$timeStamp);
    
    	 
    
    	$header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => '08032001535', 'FA' => '08032001535'));
    
    	//    			'addresses'     =>  'tel:08063805368',
    	//	+2348032001535
    	$phone = array(
    			'addresses'     =>  'tel:08032001535',
    			'senderName'   =>  99999998,
    			'message'  => 'MTN Nigeria ABC '
    	);
    
    	$result = $client->call('sendSms', $phone, '', '', $header);
    
    
    
    
    	if ($client->fault) {
    		//echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
    	} else {
    		 
    		$err = $client->getError();
    		Zend_Debug::dump($client->getDebug());
    
    		if ($err) {
    			//Zend_Debug::dump($err);
    			//Zend_Debug::dump($client->getDebug());
    
    		} else {
    			Zend_Debug::dump($result);
    		}
    
    	}
    	//	Zend_Debug::dump($result);
    
    
    
    	echo '<h2>Request</h2>';
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2>';
    
    	die('ddd');
    }
    
    
    
    
    public function refundAction() {
    
    
    
    	$err = '';
    
    	$client = new nusoap_client('http://41.206.4.162:8310/AmountChargingService/services/AmountCharging');
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$timeStamp = date("Ymd").date("His");
    
    	$spId =  2340110000426;
    	 
    	$pass = 'nexT321';
    
    	$password = md5($spId.$pass.$timeStamp);
    
    	$header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001927',  'timeStamp' => $timeStamp, 'OA' => '2347060553995', 'FA' => '2347060553995'));
    
    	$charge = array(
    			 
    			'description' => 'this is test',
    			'currency' => 'NGN',
    			'amount' => 1000,
    			'code' => 10090
    	);
    	 
    
    
    	$paymentInfo = array(
    			'endUserIdentifier'     => '2347060553995',
    			'charge'   => $charge,
    			'referenceCode'  => 'text123'
    	);
    	 
    
    	$result = $client->call('refundAmount', $paymentInfo, '', '', $header);
    	 
    	Zend_Debug::dump($result);
    	 
    
    
    
    
    	if ($client->fault) {
    		//echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
    	} else {
    		 
    		$err = $client->getError();
    		//Zend_Debug::dump($client->getDebug());
    
    		if ($err) {
    			//Zend_Debug::dump($err);
    			//Zend_Debug::dump($client->getDebug());
    
    		} else {
    			Zend_Debug::dump($result);
    		}
    
    	}
    	//	Zend_Debug::dump($result);
    
    
    
    	echo '<h2>Request</h2>';
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2>';
    
    	die('ddd');
    }
    
    
    
    public function chargeSubscriberMtnTestCaseProductionAction() {
    
    	$err = '';
    
    	$client = new nusoap_client('http://41.206.4.162:8310/AmountChargingService/services/AmountCharging');
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$timeStamp = date("Ymd").date("His");
    
    	$spId =  2340110000426;
    	$pass = 'nexT321';
    	 
    	 
    	 
    	//	'serviceId' => '234012000001927',
    
    	//	'serviceId' =>	234012000001927',
    
    	//'serviceId' => '234012000001923',
    
    	$password = md5($spId.$pass.$timeStamp);
    
    	$header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001927', 'timeStamp' => $timeStamp, 'OA' => '08103114401', 'FA' => '08103114401'));
    
    	$charge = array(
    
    			'description' => 'this is test',
    			'currency' => 'NGN',
    			'amount' => 400,
    			'code' => 10090
    	);
    
    	 
    	//'endUserIdentifier'     => '2347060553995',
    	//	'endUserIdentifier'     => '08103114401',
    	//  	        'endUserIdentifier'     => '8032001535',
    	 
    	$paymentInfo = array(
    			 
    			'endUserIdentifier'     => '08103114401',
    			'charge'   => $charge,
    			'referenceCode'  => '23124'
    	);
    
    
    
    	$result = $client->call('chargeAmount', $paymentInfo, '', '', $header);
    
    
    	if ($client->fault) {
    		echo '<h2>Fault</h2><pre>';
    		print_r($result);
    		echo '</pre>';
    	} else {
    		$err = $client->getError();
    		if ($err) {
    			echo '<h2>Error</h2><pre>' . $err . '</pre>';
    		} else {
    			echo '<h2>Result</h2><pre>';
    			// Decode the result: it so happens we sent Latin-1 characters
    			if (isset($result['return'])) {
    				$result1 = utf8_decode($result['return']);
    			} elseif (!is_array($result)) {
    				$result1 = utf8_decode($result);
    			} else {
    				$result1 = $result;
    			}
    			print_r($result1);
    			echo '</pre>';
    		}
    	}
    
    
    	echo '<h2>Request</h2>';
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2>';
    
    
    	if ($err) {
    		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    		echo '<h2>Debug</h2>';
    		echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
    		 
    	}
    
    	die();
    
    }
    
    
    
    
    public function testAbcAction () {
    
    
    
    	$client = new nusoap_client('http://pmg-acg-mdn-validate.ref1.lightsurf.net/pmgvalidate/services/validate');
    
    	$user = array('aggregatorId' => '1432', 'pwd' => 'n3x41A99');
    
    	$p = array(
    			'user'     => $user,
    			'version' => '1.0',
    			'min'     => 5403057987,
    			'productId'  => 'xxx_nt_999',
    			'shortCode' => 'xxxx',
    			'carrierId' => 401,
    			'sendUAProf' => 'Y',
    			'sendSpendingCapability' => 'Y'
    	);
    
    	 
    
    	$result = $client->call('validateMDN', $p);
    	// Display the result
    	// print_r($result);
    	Zend_Debug::dump($result);
    	Zend_Debug::dump($err = $client->getError());
    
    	die();
    
    
    }
    
    
    /**
     * Send apple push notifications through apple push notification service (APNS)
     * @param - $message notification message
     * @param - $device_token device token
     */
    private function send_notifications_to_apple($device_token, $message)
    {
    	$apns_host_pro = 'gateway.push.apple.com';
    	$apns_host_dev = 'gateway.sandbox.push.apple.com';
    
    	$apns_port = 2195;
    
    	$apns_cert_pro = './resources/certificates/pushPemProduction.pem';
    	$apns_cert_dev = './resources/certificates/pushPemDevelopment.pem';
    
    	$apns_password_pro = 'art#23GoV!Pro';
    	$apns_password_dev = 'art#23GoV!';
    
    	//create payload body
    	$payload['aps'] = array('alert' => $message, 'badge' => '', 'sound' => 'default');
    	$payload = json_encode($payload);
    
    	$stream_context = stream_context_create();
    	stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert_pro);
    	stream_context_set_option($stream_context, 'ssl', 'passphrase', $apns_password_pro);
    
    	// Open a connection to the APNS server
    	// remove 'sandbox' in production server
    	// Please make sure the port number 2195 is open in your server
    	$apns = stream_socket_client('ssl://' . $apns_host_pro . ':' . $apns_port, $error, $error_string,
    			60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $stream_context);
    	if(!$apns)
    	{
    		return false;
    		exit();
    	}
    
    	// Build the binary notification
    	$apns_message = chr(0) .  pack('n', 32) . pack('H*', str_replace(' ', '', $device_token)) . pack('n', strlen($payload)) . $payload;
    
    	// Send it to the server
    	$reuslt = fwrite($apns, $apns_message, strlen($apns_message));
    
    	// Close the connection
    	socket_close($apns);
    	fclose($apns);
    
    	return true;
    
    }
    
    
    public function testCurlAction()
    {
    
    	//Initialize handle and set options
    	$username = 'metaconnect@metaflow.com1';
    	$password = 'strip0';
    
    	$payloadName = array('ddd' => 'ddd');
    
    	$path_to_log_file = APPLICATION_PATH . "/../corn/provision.zip";
    
    	// $file_name_with_full_path = '';
    
    	$post['provisions'] = '@'.$path_to_log_file;
    
    
    	$process = curl_init('http://api.nexva.com/metaflow/submit');
    	//curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
    	// curl_setopt($process, CURLOPT_HEADER, 1);
    	curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    	curl_setopt($process, CURLOPT_USERPWD, $username . ":" . $password);
    	curl_setopt($process, CURLOPT_TIMEOUT, 30);
    	curl_setopt($process, CURLOPT_POST, 1);
    	curl_setopt($process, CURLOPT_POSTFIELDS, $post);
    	curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    	$return = curl_exec($process);
    
    
    	Zend_Debug::dump( $return );
    	die();
    
    
    	$request_xml = null;
    
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, 'http://api.nexva.com/metaflow/submit ');
    	curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
    	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $request_xml);
    	//  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));
    
    	//Execute the request and also time the transaction ( optional )
    	$start = array_sum(explode(' ', microtime()));
    	$result = curl_exec($ch);
    	$stop = array_sum(explode(' ', microtime()));
    	$totalTime = $stop - $start;
    
    	//Check for errors ( again optional )
    	if ( curl_errno($ch) ) {
    		$result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
    	} else {
    		$returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    		switch($returnCode){
    			case 200:
    				break;
    			default:
    				$result = 'HTTP ERROR -> ' . $returnCode;
    				break;
    		}
    	}
    
    	//Close the handle
    	curl_close($ch);
    
    	//Output the results and time
    	echo 'Total time for request: ' . $totalTime . "\n";
    	echo $result;
    
    
    }
    
    public function test2Action() {
    
    	$tt = '0.99';
    
    	$amount = 100 * $tt;
    
    	$amount = str_pad($amount, 3, '0', STR_PAD_LEFT);
    
    
    	Zend_Debug::dump( $amount);
    
    	die();
    
    	$aa = new Nexva_MobileBilling_Type_Syniverse();
    	$buildUrl =  $aa->doPayment(15267, 38652, 16294, '13369570515', 'ddddddd', '1.00');
    
    	Zend_Debug::dump($buildUrl );
    
    	die('dd');
    
    
    }
    
    
    public function deviceAction() {
    
    	$deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
    	$exactMatch = $deviceDetector->detectDeviceByUserAgent();
    	//If this is not a wireless device redirect to the main site
    	$isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');
    
    
    	// get properties from the Wurfl
    	$brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');
    	$modelName = $deviceDetector->getDeviceAttribute('model_name', 'product_info');
    	$marketing_name = $deviceDetector->getDeviceAttribute('marketing_name', 'product_info');
    	$inputMethod = $deviceDetector->getDeviceAttribute('pointing_method', 'product_info');
    	$osVersion = $deviceDetector->getDeviceAttribute('device_os_version', 'product_info');
    	//get nexva device Id
    	$deviceId = $deviceDetector->getNexvaDeviceId();
    
    	// Set device id and name to session
    	$deviceSession->deviceId = $deviceId;
    	$model = !empty($marketing_name) ? $marketing_name : $modelName;
    	$deviceSession->deviceName = $brandName . ' ' . $model;
    	$deviceSession->inputMethod = $inputMethod;
    	$deviceSession->isWireless = $isWireless;
    	$deviceSession->exactMatch = $exactMatch;
    
    	if ($osVersion) {
    		$deviceSession->osVersion = $osVersion;
    		 
    	}
    
    	Zend_Debug::dump($deviceSession);
    	Zend_Debug::dump($deviceId);
    
    	die();
    }
    
    
    public function testmAction() {
    	 
    	$mailer = new Nexva_Util_Mailer_Mailer();
    	$mailer->addTo('cj19820508@mail.com', 'cj19820508@mail.com')
    	->setSubject('Verify your neXva account')
    	->setLayout("generic_mail_template")
    	->setMailVar("name",'chathura')
    	->setMailVar("email", 'cj19820508@mail.com')
    	->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
    	->sendHTMLMail('verify_account_cpbo.phtml');
    
    }
    
    
    public function testCWWCAction() {
    		
    
    }
    
    public function avgTestAction()
    {
    
    	//$client = new Zend_Http_Client('http://scan.avgmobilation.com/scan');
    	$client = new Zend_Http_Client('http://scan.avgmobilation.com/');
    	$client->setHeaders(array(
    			'Content-Type' => 'application/octet-stream',
    			'scanKey' => 'bnn738459949320599483'));
    
    	$client->setFileUpload('../files/upload/Nexva-mtn.apk','loadApk');
    	$client->request(Zend_Http_Client::POST);
    	echo $client->getLastResponse();
    	//echo $client->getmessage();
    	//$response = $client->request('POST');
    	//Zend_Debug::dump($response);
    	die();
    
    
    
    	//$client = new Zend_Http_Client('http://scan.avgmobilation.com/');
    
    	//$client->setHeaders('Content-Type','application/octet-stream');
    	//$client->setHeaders('scanKey','bnn738459949320599483');
    
    	/*$client->setHeaders(array(
    	 'Content-Type' => 'application/octet-stream',
    			'scanKey' => 'bnn738459949320599483'));*/
    
    	//$client->setFileUpload('../files/upload/Nexva-mtn.apk','loadApk');
    	//$client->request(Zend_Http_Client::POST);
    	//$response = $client->request('GET');
    	//echo $response->getMessage();
    	//Zend_Debug::dump($response);
    	//echo $client->getLastResponse();
    
    	//die();
    }
    
    
    function avgTesttAction()
    {
    
    	$awsKey = 'AKIAIB7MH7NAQK55BKOQ';
    	$awsSecretKey = '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';
    	$bucketName = 'production.applications.nexva.com';
    
    	$s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);
    
    	$data = $s3->getObject($bucketName.'/productfile/0/cccc.txt');
    	 
    	 
    	file_put_contents( APPLICATION_PATH.'/../files/test.txt', $data);
    	 
    	$file = fopen("../files/testst.txt","w");
    	fwrite($file,"Hello World. Testing!");
    	fclose($file);
    	 
    	echo  file_get_contents('../files/testst.txt');
    	 
    	// echo $data;
    	die();
    
    	$list = $s3->getObjectsByBucket($bucketName);
    	foreach($list as $name) {
    		$i++;
    		echo Zend_Debug::dump($name);
    		//  $data = $s3->getObject($bucketName.'productfile/100/Spades_signed_S60v3_OTA_002.sis');
    		//  echo "with data: $data\n";
    		if($i > 1000) exit;
    
    
    
    		$client = new Zend_Http_Client('http://scan.avgmobilation.com/');
    		$client->setHeaders(array(
    				'Content-Type' => 'application/octet-stream',
    				'scanKey' => 'bnn738459949320599483'));
    		$client->setFileUpload('../files/upload/Nexva-mtn.apk','loadApk');
    		$client->request(Zend_Http_Client::POST);
    		if(!is_null($client->getLastResponse()))
    		{
    			return $client->getLastResponse();
    		}
    		else
    		{
    			return 'Try Again Later!';
    
    
    
    		}
    
    
    
    
    
    
    
    		//Zend_Debug::dump($response);
    
    		 
    		 
    		$bucket = 'my-bucket' . strtolower('AKIAIB7MH7NAQK55BKOQ');
    		 
    		$file_resource = fopen('large_video.mov', 'w+');
    		 
    		$response = $s3->get_object($bucket, 'large_video.mov', array(
    				'fileDownload' => $file_resource
    		));
    		 
    		// Success?
    		var_dump($response->isOK());
    		 
    
    		/*$client = new Zend_Http_Client('http://scan.avgmobilation.com/');
    		 $client->setHeaders(array(
    		 		'Content-Type' => 'application/octet-stream',
    		 		'scanKey' => 'bnn738459949320599483'));
    		$client->setFileUpload('../files/upload/Nexva-mtn.apk','loadApk');
    		$client->request(Zend_Http_Client::POST);
    		if(!is_null($client->getLastResponse()))
    		{
    		return $client->getLastResponse();
    		}
    		else
    		{
    		return 'Try Again Later!';
    		}*/
    
    	}
    
    }
	
	function mobileAction()
    {
        $validator = new Zend_Validate_EmailAddress();
        
        Zend_Debug::dump($validator->isValid('bayop@pledge51.com')); 
        
        die();
        
        
        if(!($validator->isValid('bayop@pledge51.com')))
        
		echo @$_SERVER['HTTP_MSISDN'].'#';
		echo @$_SERVER['REMOTE_ADDR'].'<br/>';
		die();
	}
	
	public function getSessionAction() {
		$sessionUser = new Zend_Session_Namespace('partner_user');
    	    $sessionUser->id = 111531;
    	   echo $sessionId = Zend_Session::getId();
		   print_r($_SESSION);
		   echo 'test';
		die();
	}
	
	
	
	public function nusoapAction()
	{
	    
	    include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
	    
	    $client = new nusoap_client('https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort', false);
	    $client->soap_defencoding = 'UTF-8';
	    $client->decode_utf8 = false;
	    
	    
	    $msg = '<?xml version="1.0" encoding="UTF-8"?>
	    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging"> 
   <soapenv:Header/> 
   <soapenv:Body> 
      <char:charge> 
         <inputMsg> 
            <operation>debit</operation> 
            <userId>250735285444</userId> 
            <contentId>111</contentId> 
            <itemName>SRKPic</itemName> 
            <contentDescription>Apps</contentDescription> 
            <circleId/> 
            <lineOfBusiness/> 
            <customerSegment/> 
            <contentMediaType>Apps</contentMediaType> 
            <serviceId>1</serviceId> 
            <parentId/> 
            <actualPrice>70</actualPrice> 
            <basePrice>0</basePrice> 
            <discountApplied>0</discountApplied> 
            <paymentMethod/> 
            <revenuePercent/> 
            <netShare>0</netShare> 
            <cpId>AIRTELAPPSSTORERW</cpId>
            <customerClass/> 
            <eventType>Content Purchase</eventType> 
            <localTimeStamp/> 
            <transactionId/> 
            <subscriptionName>SRKPic</subscriptionName> 
            <parentType/> 
            <deliveryChannel>sms</deliveryChannel> 
            <subscriptionTypeCode>abcd</subscriptionTypeCode> 
            <subscriptionExternalId>2</subscriptionExternalId> 
            <contentSize/> 
            <currency>RWF</currency> 
            <copyrightId>xxx</copyrightId> 
            <cpTransactionId>201313121113_256759542182</cpTransactionId> 
            <copyrightDescription>copyright</copyrightDescription> 
            <sMSkeyword>sms</sMSkeyword> 
            <srcCode>abcd</srcCode> 
            <contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl> 
            <subscriptiondays>1</subscriptiondays> 
         </inputMsg> 
      </char:charge> 
   </soapenv:Body> 
</soapenv:Envelope>';
	    
	    
	    
	    $result=$client->send($msg, 'https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort');

	//    echo '<br/><br/>'.$client->request.'<br/><br/>';
	    
	  // echo $client->response;
	  
	    Zend_Debug::dump($result);
	    echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	    echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	   // echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
	     
	    
	    $err = $client->getError();
	    if ($err) {
	    	//echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	    }
	    
	     
	   // echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	  //  echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	  //  echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
	     
	     
	 //   die();
	     
	    
	    
	 
	    die();
	}
	
		public function curltestAction()
		{
		    


	    $soapUrl = "https://197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort"; // asmx URL of WSDL
	    $soapUser = "AirtelAppStore_RW";  //  username
	    $soapPassword = "AirtelAppStore_RW!ibm123"; // password
	    
	    // xml post structure
	    
	    $xml_post_string = '<?xml version="1.0" encoding="UTF-8"?>
	    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging"> 
   <soapenv:Header/> 
   <soapenv:Body> 
      <char:charge> 
         <inputMsg> 
            <operation>debit</operation> 
            <userId>0734014536</userId> 
            <contentId>111</contentId> 
            <itemName>SRKPic</itemName> 
            <contentDescription>SRK - Subscription Service</contentDescription> 
            <circleId/> 
            <lineOfBusiness/> 
            <customerSegment/> 
            <contentMediaType>WallPaper</contentMediaType> 
            <serviceId>1</serviceId> 
            <parentId/> 
            <actualPrice>0.1</actualPrice> 
            <basePrice>0</basePrice> 
            <discountApplied>0</discountApplied> 
            <paymentMethod/> 
            <revenuePercent/> 
            <netShare>0</netShare> 
            <cpId>TESTRWANDACP</cpId> 
            <customerClass/> 
            <eventType>Content Purchase</eventType> 
            <localTimeStamp/> 
            <transactionId/> 
            <subscriptionName>SRKPic</subscriptionName> 
            <parentType/> 
            <deliveryChannel>sms</deliveryChannel> 
            <subscriptionTypeCode>abcd</subscriptionTypeCode> 
            <subscriptionExternalId>2</subscriptionExternalId> 
            <contentSize/> 
            <currency>RWF</currency> 
            <copyrightId>xxx</copyrightId> 
            <cpTransactionId>201313121113_256759542182</cpTransactionId> 
            <copyrightDescription>copyright</copyrightDescription> 
            <sMSkeyword>sms</sMSkeyword> 
            <srcCode>abcd</srcCode> 
            <contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl> 
            <subscriptiondays>1</subscriptiondays> 
         </inputMsg> 
      </char:charge> 
   </soapenv:Body> 
</soapenv:Envelope>';   // data from the form, e.g. some ID number
	    
	    
	    $xml_post_string = '<?xml version="1.0" encoding="UTF-8"?>
	    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging"> 
   <soapenv:Header/> 
   <soapenv:Body> 
      <char:charge> 
         <inputMsg> 
            <operation>debit</operation> 
            <userId>250735285444</userId> 
            <contentId>111</contentId> 
            <itemName>SRKPic</itemName> 
            <contentDescription>Apps</contentDescription> 
            <circleId/> 
            <lineOfBusiness/> 
            <customerSegment/> 
            <contentMediaType>Apps</contentMediaType> 
            <serviceId>1</serviceId> 
            <parentId/> 
            <actualPrice>70</actualPrice> 
            <basePrice>0</basePrice> 
            <discountApplied>0</discountApplied> 
            <paymentMethod/> 
            <revenuePercent/> 
            <netShare>0</netShare> 
            <cpId>AIRTELAPPSSTORERW</cpId>
            <customerClass/> 
            <eventType>Content Purchase</eventType> 
            <localTimeStamp/> 
            <transactionId/> 
            <subscriptionName>SRKPic</subscriptionName> 
            <parentType/> 
            <deliveryChannel>sms</deliveryChannel> 
            <subscriptionTypeCode>abcd</subscriptionTypeCode> 
            <subscriptionExternalId>2</subscriptionExternalId> 
            <contentSize/> 
            <currency>RWF</currency> 
            <copyrightId>xxx</copyrightId> 
            <cpTransactionId>201313121113_256759542182</cpTransactionId> 
            <copyrightDescription>copyright</copyrightDescription> 
            <sMSkeyword>sms</sMSkeyword> 
            <srcCode>abcd</srcCode> 
            <contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl> 
            <subscriptiondays>1</subscriptiondays> 
         </inputMsg> 
      </char:charge> 
   </soapenv:Body> 
</soapenv:Envelope>';
	    
	    
	    $headers = array(
	    		"Content-type: text/xml;charset=\"utf-8\"",
	    		"Accept: text/xml",
	    		"Cache-Control: no-cache",
	    		"Pragma: no-cache",
	    		"SOAPAction: https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort",
	    		"Content-length: ".strlen($xml_post_string),
	    ); //SOAPAction: your op URL
	    
	    $url = $soapUrl;
	    


	    
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
	    curl_setopt($ch, CURLOPT_TIMEOUT,        10000);
	    curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
	   // curl_setopt($ch, CURLOPT_CAINFO,  APPLICATION_PATH . '/configs/certrw.crt');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    curl_setopt($ch, CURLOPT_USERPWD, 'AirtelAppStore_RW:AirtelAppStore_RW!ibm123');
	    curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
	    
	    

	    $result = curl_exec($ch);
	    
	    if(curl_errno($ch))
	    {
	    	echo 'Curl error: ' . curl_error($ch);
	    }
	    else
	    {
	    	echo $result;
	    }
	    
	    curl_close($ch);
	    
	    
	    Zend_Debug::dump($result);
	    die();
	    curl_close($ch);
	    
	    // converting
	    $response1 = str_replace("<soap:Body>","",$result);
	    $response2 = str_replace("</soap:Body>","",$response1);
	    

	    
	    // convertingc to XML
	    $parser = simplexml_load_string($response2);
	    // user $parser to get your data out of XML response and to display it.

	    
	    
	}
	
	public function smpprAction() {
	
		include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
		//  $smpp->bindTransmitter("1432","n3x41A99");
	
		// Optional connection specific overrides
		//pmg-acg-sms01.ref1.lightsurf.net
	

		$tx=new SMPP('197.157.129.20','31120');
		$tx->debug=true;
		$tx->system_type="smpp";
		$tx->interface_version='3.4';

		$tx->addr_ton=1;
		$tx->addr_npi=1;
		//print "open status: ".$tx->state."\n";
		Zend_Debug::dump( $tx->bindTransmitter("neXvarw","neXvarw"));
	
	
		//$tx->sms_source_addr_npi=1;
		//$tx->sms_source_addr_ton=1;
	//	$tx->sms_dest_addr_ton=1;
	//	$tx->sms_dest_addr_npi=1;
		
		$tx->sms_source_addr_npi=1;
		$tx->sms_source_addr_ton=0;
		$tx->sms_dest_addr_ton=1;
		$tx->sms_dest_addr_npi=0;
		Zend_Debug::dump($tx->sendSMS("731000057",'731000058',"Hello from neXva"));
		$tx->close();
		unset($tx);

	
		die('ddd');
	
	
	
	}
	
	function sendsmsAction()
	{
		include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
	
		$smppHost = '197.157.129.20';
		$smppPort = '31120';
		$systemId = 'neXvarw';
		$password = 'neXvarw';
		$systemType = 'smpp';
		$from = 'AppStore-RW';
	
		
		echo '<pre>';
		        $smpp = new SMPPClass();
		        $smpp->SetSender($from);
		        /* bind to smpp server */
		        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
		        /* send enquire link PDU to smpp server */
		        $smpp->TestLink();
		        /* send single message; large messages are automatically split */
		        $messageStatus = $smpp->Send('250731000057', 'neXva neXva neXva neXva neXva neXva ');
		        
		        Zend_Debug::dump($messageStatus,'ddd');
		        
		
		        $smpp->End();
		        echo '</pre>';
		        die();
		/* send unicode message */
		///$smpp->Send("731000057", "731000057", true);
		/* send message to multiple recipients at once */
		//$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
		/* unbind from smpp server */
	
	}
	
	function sendgabonAction()
	{
		include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
		
	    $from = $this->getRequest()->getParam('from');
		$to = $this->getRequest()->getParam('to');
	
		$smppHost = '192.168.1.61';
		$smppPort = '16920';
		$systemId = 'NEXVA';
		$password = 'NEXVA';
		$systemType = 'smpp';
		$from = '+24107558130';
	
	
		echo '<pre>';
		$smpp = new SMPPClass();
		$smpp->SetSender($from);
		/* bind to smpp server */
		$smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
		/* send enquire link PDU to smpp server */
		$smpp->TestLink();
		/* send single message; large messages are automatically split */
		$messageStatus = $smpp->Send('+24107558130', 'test test', true);
	
		Zend_Debug::dump($messageStatus,'ddd');
	
	
		$smpp->End();
		echo '</pre>';
		die();
		/* send unicode message */
		///$smpp->Send("731000057", "731000057", true);
		/* send message to multiple recipients at once */
		//$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
		/* unbind from smpp server */
	
	}
	
	public function smppgabonAction() {
	
		include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
		//  $smpp->bindTransmitter("1432","n3x41A99");
	
		// Optional connection specific overrides
		//pmg-acg-sms01.ref1.lightsurf.net
	
	
		$tx=new SMPP('192.168.1.61','16920');
		$tx->debug=true;
	//	$tx->system_type="smpp";
	//	$tx->interface_version='3.4';
	
		$tx->addr_ton=1;
		$tx->addr_npi=1;
		//print "open status: ".$tx->state."\n";
		Zend_Debug::dump( $tx->bindTransmitter("NEXVA","NEXVA"));
	
	
		//$tx->sms_source_addr_npi=1;
		//$tx->sms_source_addr_ton=1;
		//	$tx->sms_dest_addr_ton=1;
		//	$tx->sms_dest_addr_npi=1;
	
		$tx->sms_source_addr_npi=1;
		$tx->sms_source_addr_ton=1;
		$tx->sms_dest_addr_ton=1;
		$tx->sms_dest_addr_npi=1;
		Zend_Debug::dump($tx->sendSMS("+94757474481",'+94757474481',"Hello from neXva"));
		$tx->close();
		unset($tx);
	
	
		die('ddd');
	
	
	
	}
	
	public function smpp2gabonaAction() {
	
		include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
		//  $smpp->bindTransmitter("1432","n3x41A99");
	
		// Optional connection specific overrides
		//pmg-acg-sms01.ref1.lightsurf.net
	
		print "<pre>";
		$tx=new SMPP('192.168.1.61','16920');
		$tx->debug=true;
		// $tx->system_type="WWW";
	//	$tx->addr_npi=1;
		print "open status: ".$tx->state."\n";
		Zend_Debug::dump( $tx->bindTransmitter("NEXVA","NEXVA"));
	
	
	//	$tx->sms_source_addr_npi=1;
		//$tx->sms_source_addr_ton=1;
	//	$tx->sms_dest_addr_ton=1;
	//	$tx->sms_dest_addr_npi=1;
		Zend_Debug::dump($tx->sendSMS("094757474481",'094757474481',"Hello world!"));
		$tx->close();
		unset($tx);
		print "</pre>";
	
		die('ddd');
	
	
	
	}
	
	
	public function smpp2mtnUgandaAction() {
	
		include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
		//  $smpp->bindTransmitter("1432","n3x41A99");
	
		// Optional connection specific overrides
		//pmg-acg-sms01.ref1.lightsurf.net
	
		print "<pre>";
		$tx=new SMPP('10.170.10.58','5001');
		$tx->debug=true;
		// $tx->system_type="WWW";
		//	$tx->addr_npi=1;
		print "open status: ".$tx->state."\n";
		Zend_Debug::dump( $tx->bindTransmitter("mtnappshop","n3Xv4"));
	
	
		//	$tx->sms_source_addr_npi=1;
		//$tx->sms_source_addr_ton=1;
		//	$tx->sms_dest_addr_ton=1;
		//	$tx->sms_dest_addr_npi=1;
		Zend_Debug::dump($tx->sendSMS("094757474481",'094757474481',"Hello world!"));
		$tx->close();
		unset($tx);
		print "</pre>";
	
		die('ddd');
	
	
	
	}
	
	public function smppmtnUgandaAction() {
	
		include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
		//  $smpp->bindTransmitter("1432","n3x41A99");
	
		// Optional connection specific overrides
		//pmg-acg-sms01.ref1.lightsurf.net
	
	
		$tx=new SMPP('10.170.10.58','5001');
		$tx->debug=true;
		$tx->system_type="vma";
		$tx->interface_version='3.4';
	
		$tx->addr_ton=1;
		$tx->addr_npi=1;
		//print "open status: ".$tx->state."\n";
		Zend_Debug::dump( $tx->bindTransmitter("mtnappshop","n3Xv4"));
	
	
		//$tx->sms_source_addr_npi=1;
		//$tx->sms_source_addr_ton=1;
		//	$tx->sms_dest_addr_ton=1;
		//	$tx->sms_dest_addr_npi=1;
	
		$tx->sms_source_addr_npi=1;
		$tx->sms_source_addr_ton=0;
		$tx->sms_dest_addr_ton=1;
		$tx->sms_dest_addr_npi=0;
		Zend_Debug::dump($tx->sendSMS("731000057",'731000058',"Hello from neXva"));
		$tx->close();
		unset($tx);
	
	
		die('ddd');
	
	
	
	}

    public function airtelAction(){

        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

        //$client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
        $client = new nusoap_client('http://196.46.244.21:8443', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        //SMS Service
        $msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:sms="http://sms.ibm.com"
                <soapenv:Header/>
                <soapenv:Body>
                <sms:sendSMS>
                <inputMsg>
                <msisdn>1234567890</msisdn>
                <sendername>test-sender</sendername>
                <smsMsg>test-message</smsMsg>
                </inputMsg>
                </sms:sendSMS>
                </soapenv:Body>
                </soapenv:Envelope>';

        //WAP Push Service
        /*$msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:wap="http://wappush.ibm.com">
                <soapenv:Header/>
                <soapenv:Body>
                <wap:sendWAPPush>
                <inputMsg>
                <msisdn>?</msisdn>
                <sendername>?</sendername>
                <text>?</text>
                <href>?</href>
                </inputMsg>
                </wap:sendWAPPush>
                </soapenv:Body>
                </soapenv:Envelope>';*/

        //MMS Service
        /*$msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:mms="http://mms.ibm.com/">
                <soapenv:Header/>
                <soapenv:Body>
                <mms:sendMMS>
                <inputMsg>
                <data>?</data>
                <fname>?</fname>
                <msisdn>?</msisdn>
                <sendername>?</sendername>
                <subject>?</subject>
                </inputMsg>
                </mms:sendMMS>
                </soapenv:Body>
                </soapenv:Envelope>';*/

        //Terminal Location Service
        /*$msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:ibm="http://ibm.com">
                <soapenv:Header/>
                <soapenv:Body>
                <ibm:getLocation>
                <inputMsg>
                <phoneNumber>?</phoneNumber>
                </inputMsg>
                </ibm:getLocation>
                </soapenv:Body>
                </soapenv:Envelope>';*/
        $err = $client->getError();
        $result=$client->send($msg, 'http://196.46.244.21:8443');
        Zend_Debug::dump($client->response);
        //$result=$client->send($msg, 'POST');

        Zend_Debug::dump($err);
        //return $result;
        die();
    }
	
	
	
	
	public function mobileMoneyXmlUgandaAction() {
		
	    include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

		$client = new nusoap_client('http://172.25.48.43:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v15', false);
		$client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
	
		//$timeStamp = date("Ymd").date("His"); 
		
		$timeStamp = '20140401112201';
		$spId =  '2560110000692';
		$spPass = 'Huawei2014';
		
		$password = strtoupper(MD5($spId.$spPass.$timeStamp));
		
		$xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0">
			   <soapenv:Header>
				  <RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
					 <spId>2560110000692</spId>
					 <spPassword>ECF325ECD1342743992165611BBE4A96</spPassword>
					 <serviceId></serviceId>
					 <timeStamp>20140401112201</timeStamp>
					</RequestSOAPHeader>
			   </soapenv:Header>
			   <soapenv:Body>
				  <b2b:processRequest>
					 <serviceId>200</serviceId>
					 <parameter>
						<name>DueAmount</name>
						<value>10</value>
					 </parameter>
					 <parameter>
						<name>MSISDNNum</name>
						<value>256789999550</value>
					 </parameter>
					 <parameter>
						<name>ProcessingNumber</name>
						<value>556</value>
					 </parameter>
					 <parameter> 
						<name>serviceId</name> 
						<value>Appstore</value> 
					 </parameter>
					 <parameter>
						<name>AcctRef</name>
						<value>1234</value>
					 </parameter>
					 <parameter>
						<name>AcctBalance</name>
						<value>0</value>
					 </parameter>
					 <parameter>
						<name>MinDueAmount</name>
						<value>1</value>
					 </parameter>
					 <parameter> 
						<name>Narration</name> 
						<value>121212</value> 
					 </parameter>
					 <parameter>
						<name>PrefLang</name>
						<value>en</value>
					 </parameter>
					 <parameter>
						<name>OpCoID</name>
						<value>25601</value>
					 </parameter>
				  </b2b:processRequest>
			   </soapenv:Body>
			</soapenv:Envelope>';
			
	
			$result=$client->send($xmlMsg, 'POST');
			

			
	  
	    Zend_Debug::dump($result);
	   // echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	  //  echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	//    echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
	     
	    
	    $err = $client->getError();
	    if ($err) {
	    	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	    }
	    
	     
	    echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
	     
			die();
	}

    public function smsAction(){

        //die('zshgdvfzyus');

        $mobileNo = 989373390219;
        $message = 'Hi This is a testing message';
        $chapId = 23045;

        //todo, get chap SMS gateway details dynamically242068661314
        $client = new nusoap_client('http://92.42.55.109:8310/SendSmsService/services/SendSms');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");
        $spId =  '001617';
        $spServiceId = '0016172000001407';
        $pass = '0f596813534b39c9429d6ba598f80b6d';

        $header = array(
            'RequestSOAPHeader' => array (
                'spId' => $spId,
                'spPassword' => '0f596813534b39c9429d6ba598f80b6d',
                'serviceId' => $spServiceId,
                'timeStamp' => $timeStamp,
                'OA' => $mobileNo,
                'FA' => $mobileNo
            )
        );

        $phone = array(
            'addresses'     =>  'tel:'.$mobileNo,
            'senderName'   =>  737920,
            'message'  => $message,
            'receiptRequest' => array(
                'endpoint'     =>  'http://88.190.51.72/recv-irancell.php',
                'interfaceName'   =>  'SmsNotification',
                'correlator'  => '1232',
            )
        );

        $result = $client->call('sendSms', $phone, '', '', $header);
        Zend_Debug::dump($result);die();
    }
    
    function sendsmsbAction()
    {

    	include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
    
    	$smppHost = '212.88.118.228';
    	$smppPort = '5001';
    	$systemId = 'mtnappshop';
    	$password = 'n3Xv4';
    	$systemType = 'vma';
    	$from = 'MTNUganda';
    
    
    	echo '<pre>';
    	$smpp = new SMPPClass();
    	$smpp->SetSender($from);
    	/* bind to smpp server */
    	$smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
    	/* send enquire link PDU to smpp server */
    	//$smpp->TestLink();
    	/* send single message; large messages are automatically split */
    	$messageStatus = $smpp->Send('256775516494', 'neXva neXva neXva neXva neXva neXva test ');
    
    	///Zend_Debug::dump($messageStatus,'ddd');
    
    
    	$smpp->End();
    	echo '</pre>';
    	die();
    	/* send unicode message */
    	///$smpp->Send("731000057", "731000057", true);
    	/* send message to multiple recipients at once */
    	//$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
    	/* unbind from smpp server */
    
    }

  
  public function mobileMoneySendRequestAction() {
		print_r($_POST);
	    if(isset($_POST)){
			print_r($_POST);
		}
	     
		die();
	}
	
	
	public function mobileMoneyXmlUgandaTestAction() {

            ini_set('default_socket_timeout', 300);
       
            //echo 123; die();
	    include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

		$client = new nusoap_client('http://172.25.48.43:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v15', false);
		$client->soap_defencoding = 'UTF-8';
                $client->decode_utf8 = false;
	
		$timeStamp = date("Ymd").date("His"); 
		
		//$timeStamp = '20140428082849';
		//$spId =  '2560110000692';
                $spId =  '2560110000694';
		$spPass = 'Huawei2014';
		
		$password = strtoupper(MD5($spId.$spPass.$timeStamp));
		
		$xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0">
			   <soapenv:Header>
				  <RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
					 <spId>'.$spId.'</spId>
					 <spPassword>'.$password.'</spPassword>
					 <serviceId></serviceId>
					 <timeStamp>'.$timeStamp.'</timeStamp>
					</RequestSOAPHeader>
			   </soapenv:Header>
			   
			   <soapenv:Body>
				  <b2b:processRequest>
					 <serviceId>200</serviceId>
					 <parameter>
						<name>DueAmount</name>
						<value>12</value>
					 </parameter>
					 <parameter>
						<name>MSISDNNum</name>
						<value>256789999550</value>
					 </parameter>
					 <parameter>
						<name>ProcessingNumber</name>
						<value>'.$timeStamp.'</value>
					 </parameter>
					 <parameter> 
						<name>serviceId</name> 
						<value>Appstore.sp</value> 
					 </parameter>
					 <parameter>
						<name>AcctRef</name>
						<value>1234</value>
					 </parameter>
					 <parameter>
						<name>AcctBalance</name>
						<value>0</value>
					 </parameter>
					 <parameter>
						<name>MinDueAmount</name>
						<value>1</value>
					 </parameter>
					 <parameter> 
						<name>Narration</name> 
						<value>121212</value> 
					 </parameter>
					 <parameter>
						<name>PrefLang</name>
						<value>en</value>
					 </parameter>
					 <parameter>
						<name>OpCoID</name>
						<value>25601</value>
					 </parameter>
				  </b2b:processRequest>
			   </soapenv:Body>
			</soapenv:Envelope>';
			
	
			$result=$client->send($xmlMsg, 'POST', 0, 180);
			

			
	  
	    Zend_Debug::dump($result);
	   
	    $err = $client->getError();
	    if ($err) {
	    	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	    }
	    
	     
	    echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
	     
			die();
	}
	
	 

    public function soapServerAction()
    {
		// Create the server instance
		$server = new soap_server();
		// Initialize WSDL support
		$server->configureWSDL('hellowsdl', 'urn:hellowsdl');
	
		// Register the method to expose
		$server->register('hello',                // method name
                    array('name' => 'xsd:string'),        // input parameters
                    array('return' => 'xsd:string'),      // output parameters
                    'urn:hellowsdl',                      // namespace
                    'urn:hellowsdl#hello',                // soapaction
                    'rpc',                                // style
                    'encoded',                            // use
                    'Says hello to the caller'            // documentation
                );

		// Use the request to (try to) invoke the service
		$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
		$server->service($HTTP_RAW_POST_DATA);
    }
	
    // Define the method as a PHP function
    function hello($name) {
        
            return 'Hello, ' . $name;


    }

    // Define the method as a PHP function
    public function helloArray($person) {
		$greeting = 'Hello, ' . $person['firstname'] .
					'. It is nice to meet a ' . $person['age'] .
					' year old ' . $person['gender'] . '.';
		
		$winner = $person['firstname'] == 'Scott';
	
		return array(
					'greeting' => $greeting,
					'winner' => $winner
					);
	}
    
    public function soapClientAction()
    {
  		$client = new nusoap_client('http://nexva.com/HelloService.wsdl?wsdl', true);
		/*$client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
	   	
		$msg = '<?xml version="1.0" encoding="ISO-8859-1"?>
				<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
								   xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
								   xmlns:xsd="http://www.w3.org/2001/XMLSchema"
								   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
								   xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
								   xmlns:si="http://soapinterop.org/xsd"
								   xmlns:tns="urn:hellowsdl2">
					<SOAP-ENV:Body>
						<tns:hello xmlns:tns="urn:hellowsdl2">
							<person xsi:type="tns:Person">
								<firstname xsi:type="xsd:string">Willi</firstname>
								<age xsi:type="xsd:int">22</age>
								<gender xsi:type="xsd:string">male</gender>
							</person>
						</tns:hello>
					</SOAP-ENV:Body>
				</SOAP-ENV:Envelope>';
		
		 $result=$client->send($msg, 'hellowsdl2#hello');
		
		 echo $client->request.'<br/><br/>';
		 
		 echo $client->response;
		 echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
		 echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
		 die();*/
                
                // Check for an error
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
                // At this point, you know the call that follows will fail
            }
            // Call the SOAP method
            $result = $client->call('hello', array('name' => 'Scott'));
            // Check for a fault
            if ($client->fault) {
                echo '<h2>Fault</h2><pre>';
                print_r($result);
                echo '</pre>';
            } else {
                // Check for errors
                $err = $client->getError();
                if ($err) {
                    // Display the error
                    echo '<h2>Error</h2><pre>' . $err . '</pre>';
                } else {
                    // Display the result
                    echo '<h2>Result</h2><pre>';
                    print_r($result);
                echo '</pre>';
                }
            }
            // Display the request and response
            echo '<h2>Request</h2>';
            echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
            echo '<h2>Response</h2>';
            echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
            // Display the debug messages
            echo '<h2>Debug</h2>';
            echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
            
            $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

    }
    
    /*
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     */

    
    public function testUgandaFinalRequestAction()
    {
    	// initialize server and set URI
        // $server = new Zend_Soap_Server(null, array('uri' => 'http://api.nexva.com/test/confirm-third-party-payment-request'));
       
        $server = new TestSoapServer(null, array('uri' => 'http://api.nexva.com/test/confirm-third-party-payment-request'));
    	
    	// set SOAP service class
    	$server->setClass('Uganda_Soap_Functions');
        
        $server->setEncoding('UTF-8');
        //$server->setReturnResponse(false);

    	// handle request
    	$server->handleUganda();

    }
    
    public function confirmThirdPartyPaymentRequestAction()
    {
    	// initialize server and set URI
        // $server = new Zend_Soap_Server(null, array('uri' => 'http://api.nexva.com/test/confirm-third-party-payment-request'));
       
        $server = new TestSoapServer(null, array('uri' => 'http://api.nexva.com/test/confirm-third-party-payment-request'));
    	
    	// set SOAP service class
    	$server->setClass('Uganda_Soap_Functions');
        
        $server->setEncoding('UTF-8');
        //$server->setReturnResponse(false);

    	// handle request
    	$server->handle();

    }

    public function testSoapClientAction()
    {
            $spEndPoint =  'http://api.nexva.com/test/confirm-third-party-payment-request';

            $client = new nusoap_client($spEndPoint);
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = false;

            $this->_header = array();

            $msg = '<?xml version="1.0" encoding="utf-8" ?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<soapenv:Header>
<ns1:NotifySOAPHeader xmlns:ns1="http://www.huawei.com.cn/schema/common/v2_1">
  <ns1:traceUniqueID>504021502871404111059030443002</ns1:traceUniqueID>
</ns1:NotifySOAPHeader>
</soapenv:Header>
<soapenv:Body>
<ns2:processRequest xmlns:ns2="http://b2b.mobilemoney.mtn.ug_v1.0/">
  <serviceId>101</serviceId>
  <parameter>
    <name>ProcessingNumber</name>
    <value>2087103</value>
  </parameter>
  <parameter>
    <name>SenderID</name>
    <value>MOM</value>
  </parameter>
  <parameter>
    <name>AcctRef</name>
    <value>1234</value>
  </parameter>
  <parameter>
    <name>RequestAmount</name>
    <value>10</value>
  </parameter>
  <parameter>
    <name>PaymentRef</name>
    <value>20140411075833</value>
  </parameter>
  <parameter>
    <name>ThirdPartyTransactionID</name>
    <value>20140411075833</value>
  </parameter>
  <parameter>
    <name>MOMAcctNum</name>
    <value></value>
  </parameter>
  <parameter>
    <name>CustName</name>
    <value/>
  </parameter>
  <parameter>
    <name>StatusCode</name>
    <value>01</value>
  </parameter>
  <parameter>
    <name>TXNType</name>
    <value/>
  </parameter>
  <parameter>
    <name>OpCoID</name>
    <value>25601</value>
  </parameter>
</ns2:processRequest>
</soapenv:Body>
</soapenv:Envelope>';
		
        $result=$client->send($msg, 'POST');
        //$result = $client->call('test', array('a' => 3, 'v' => 5), '', '', $this->_header);
        Zend_Debug::dump($result);
        //trun on this for debuging
        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
            echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES)  . '</pre>';
            echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
        Zend_Debug::dump($result);

        Zend_Debug::dump($client->getDebug());
        Zend_Debug::dump($error = $client->getError());

        die();    	
        
    
    }
    
    public function airtelnChargingAction(){
        
        
    
    	include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
    
    	//$client = new nusoap_client('https://196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1', array('username'=> "neXva_gh", 'password' => "AirtelAppStore_RW!ibm123"));
    	//$client = new nusoap_client('https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort', false);
    	$client = new nusoap_client('https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1', false);
    	//$client = new nusoap_client('https://196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1', array('username'=> "neXva_gh", 'password' => "neXva_gh!ibm123"));
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$err = $client->getError();
    	if ($err) {
    		die("client construction error: {$err}\n");
    	}
    
    	$chargingXml = '<?xml version="1.0" encoding="UTF-8"?>
    	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
    	<soapenv:Header />
    	<soapenv:Body>
    	<char:charge>
    	<inputMsg>
    	<operation>debit</operation>
    	<userId>2347012966965</userId>
    	<contentId>N5</contentId>
    	<itemName>test</itemName>
    	<contentDescription>Mobile Apps</contentDescription>
    	<circleId></circleId>
    	<lineOfBusiness></lineOfBusiness>
    	<customerSegment></customerSegment>
    	<contentMediaType>Mobile Apps</contentMediaType>
    	<serviceId></serviceId>
    	<parentId></parentId>
    	<actualPrice>5</actualPrice>
    	<basePrice>5</basePrice>
    	<discountApplied>5</discountApplied>
    	<paymentMethod></paymentMethod>
    	<revenuePercent></revenuePercent>
    	<netShare></netShare>
    	<cpId>NEXVA_NG</cpId>
    	<customerClass></customerClass>
    	<eventType>Content Purchase</eventType>
    	<localTimeStamp></localTimeStamp>
    	<transactionId>234235423512</transactionId>
    	<subscriptionTypeCode>abcd</subscriptionTypeCode>
    	<subscriptionName>0</subscriptionName>
    	<parentType></parentType>
    	<deliveryChannel>SMS</deliveryChannel>
    	<subscriptionExternalId>0</subscriptionExternalId>
    	<contentSize></contentSize>
    	<currency>NGN</currency>
    	<copyrightId>mauj</copyrightId>
    	<cpTransactionId>1234567845</cpTransactionId>
    	<copyrightDescription>copyright</copyrightDescription>
    	<sMSkeyword>sms</sMSkeyword>
    	<srcCode>54321</srcCode>
    	<contentUrl>www.yahoo.com</contentUrl>
    	<subscriptiondays>2</subscriptiondays>
    	</inputMsg>
    	</char:charge>
    	</soapenv:Body>
    	</soapenv:Envelope>';
    
    	$result=$client->send($chargingXml, 'https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1');
    	//echo $client->response;
    	Zend_Debug::dump($client->request);
    	Zend_Debug::dump($client->response);
    	//Zend_Debug::dump($client->getDebug());
    	Zend_Debug::dump($result);die();
    
    	//$err = $client->getError();
    	//$client->setCredentials('neXva_gh', 'neXva_gh!ibm123', 'digest');
    	//=$client->send($msg, 'https://196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1');
    	//Zend_Debug::dump($result);
    
    	//$result=$client->send($msg, 'http://196.46.244.21:8443');
    	//Zend_Debug::dump($client->response);
    	//Zend_Debug::dump($client->getDebug());
    	//$result=$client->send($msg, 'POST');
    
    	//Zend_Debug::dump($err);
    	//return $result;
    	//die();
    }
    
	public function ugandaInPaymentAction(){

		include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

		$client = new nusoap_client('http://172.25.48.43:8310/AmountChargingService/services/AmountCharging', false);
		$client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
	
		//$timeStamp = date("Ymd").date("His");
		
        $timeStamp = '20140502095435';
		$spId =  '2560110000694';
		$spPass = 'Huawei2014';
		
		$password = strtoupper(MD5($spId.$spPass.$timeStamp));
        
		$xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:loc="http://www.csapi.org/schema/parlayx/payment/amount_charging/v2_1/local">
  <soapenv:Header>
    <RequestSOAPHeade>
    <spId>2560110000694</spId>
    <spPassword>E4D7D30BB536F84F9F80154612F10BBE</spPassword>
    <timeStamp>20140502095435</timeStamp>
    <v2:OA>256789999550</v2:OA>
    <v2:FA>256789999550</v2:FA>
    </RequestSOAPHeader>
  </soapenv:Header>
  <soapenv:Body>
    <loc:chargeAmount>
      <loc:endUserIdentifier>256789999550</loc:endUserIdentifier>
      <loc:charge>
        <description>charge</description>
        <currency>UGX</currency>
        <amount>10</amount>
        <code></code> </loc:charge>
      <loc:referenceCode>20140502095435</loc:referenceCode>
    </loc:chargeAmount>
  </soapenv:Body>
</soapenv:Envelope>
';
		 
		/*$xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:loc="http://www.csapi.org/schema/parlayx/payment/amount_charging/v2_1/local">
<soapenv:Header>
	<RequestSOAPHeade>
		<spId>'.$spId.'</spId>
		<spPassword>'.$password.'</spPassword>
		<timeStamp>'.$timeStamp.'</timeStamp>
		<v2:OA>256789999550</v2:OA>
		<v2:FA>256789999550</v2:FA>
		</RequestSOAPHeader>
	</soapenv:Header>
	<soapenv:Body>
		<loc:chargeAmount>
		<loc:endUserIdentifier>256789999550</loc:endUserIdentifier>
		<loc:charge>
		<description>charge</description>
		<currency>UGX</currency>
		<amount>10</amount>
		<code></code> </loc:charge>
		<loc:referenceCode>'.$timeStamp.'</loc:referenceCode>
		</loc:chargeAmount>
	</soapenv:Body>
</soapenv:Envelope>';*/

			
		$result=$client->send($xmlMsg, '', 0, 180);
	  
	    Zend_Debug::dump($result);
	   
	    $err = $client->getError();
	    if ($err) {
	    	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	    }
	    
	     
	    echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
	    die();

	}
	
	
	public function testxmlAction() {
	

	    include_once( APPLICATION_PATH . '/../public/vendors/phpxmlrpc-3.0.0-beta/lib/xmlrpc.inc' );
	    die('ddd');
		$c=new xmlrpc_client("/index.php", "192.168.1.39", 80);
		$c->setDebug(1);
		$r=&$c->send($f);
		if(!$r->faultCode())
		{
			$v=$r->value();
			print "</pre><br/>State number " . $stateno . " is "
			. htmlspecialchars($v->scalarval()) . "<br/>";
			 print "<HR>I got this value back<BR><PRE>" .
			  htmlentities($r->serialize()). "</PRE><HR>\n";
		}
		else
		{
			print "An error occurred: ";
			print "Code: " . htmlspecialchars($r->faultCode())
			. " Reason: '" . htmlspecialchars($r->faultString()) . "'</pre><br/>";
		}
		
		
		die('ddd');

	}
    
    public function airtimePaymentUgandaAction(){

       
            //echo 123; die();
	    include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

		$client = new nusoap_client('http://172.25.48.43:8310/AmountChargingService/services/AmountCharging', false);
		$client->soap_defencoding = 'UTF-8';
                $client->decode_utf8 = false;
	
		//$timeStamp = date("Ymd").date("His");
		
        $timeStamp = '20140502095435';
		$spId =  '2560110000694';
		$spPass = 'Huawei2014';
		
		$password = strtoupper(MD5($spId.$spPass.$timeStamp));
                
                $xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" xmlns:loc="http://www.csapi.org/schema/parlayx/payment/amount_charging/v2_1/local">
                           <soapenv:Header>
                              <v2:RequestSOAPHeader>
                                 <v2:spId>'.$spId.'</v2:spId>
                                 <v2:spPassword>'.$password.'</v2:spPassword>
                                 <v2:serviceId></v2:serviceId>
                                 <v2:timeStamp>'.$timeStamp.'</v2:timeStamp>
                                 <v2:OA>256789999550</v2:OA>
                                 <v2:FA>256789999550</v2:FA>
                                 <v2:token/>
                              </v2:RequestSOAPHeader>
                           </soapenv:Header>
                           <soapenv:Body>
                              <loc:chargeAmount>
                                 <loc:endUserIdentifier>256789999550</loc:endUserIdentifier>
                                 <loc:charge>
                                 <description>charge</description>
                                    <currency>UGX</currency>     
                                    <amount>10</amount>
                                    <code></code>
                                 </loc:charge>
                                 <loc:referenceCode>'.$timeStamp.'</loc:referenceCode>
                              </loc:chargeAmount>
                           </soapenv:Body>
                        </soapenv:Envelope>';
		
		/*$xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:loc="http://www.csapi.org/schema/parlayx/payment/amount_charging/v2_1/local">
                          <soapenv:Header>
                            <RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
                              <spId>'.$spId.'</spId>
                              <spPassword>'.$password.'</spPassword>
                              <timeStamp>'.$timeStamp.'</timeStamp>
                            </RequestSOAPHeader>
                          </soapenv:Header>
                          <soapenv:Body>
                            <loc:chargeAmount>
                              <loc:endUserIdentifier>256789999550</loc:endUserIdentifier>
                              <loc:charge>
                                <description>charge</description>
                                <currency>UGX</currency>
                                <amount>10</amount>
                                <code>4523</code></loc:charge>
                              <loc:referenceCode>'.$timeStamp.'</loc:referenceCode>
                             </loc:chargeAmount>
                          </soapenv:Body>
                        </soapenv:Envelope>';*/
			
			$result=$client->send($xmlMsg, '', 0, 180);
	  
	    Zend_Debug::dump($result);
	   
	    $err = $client->getError();
	    if ($err) {
	    	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	    }
	    
	     
	    echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
	    die();
    }
    
    
   
    
        
    public function testRestAction()
    {
        $client = new Zend_Rest_Client('https://api.centili.com/ api/payment/1_2/transaction');

        $client->apikey('API');
        $client->priceid('10');
        $result = $client->get();
        
        print_r($result); die();
    }
	
		
	public function airtelslheaderAction(){
		echo '#####';
		$headers = apache_request_headers();
        $msisdn = $headers['msisdn'];
		echo '#####'.$msisdn.'#####'; die();
	}
        
        public function curlPostAction(){
            //set POST variables
            $url = 'http://joboffersforme.com/request.php';
            $fields = array(
                                    'lname'=>urlencode('LName'),
                                    'fname'=>urlencode('FName'),
                                    'title'=>urlencode('Title'),
                                    'company'=>urlencode('Company'),
                                    'age'=>urlencode('Age'),
                                    'email'=>urlencode('Email'),
                                    'phone'=>urlencode('phone')
                            );

            //url-ify the data for the POST
            foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
            rtrim($fields_string,'&');

            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_POST,count($fields));
            curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true); 
            curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

            //execute post
            $result = curl_exec($ch);

            //close connection
            curl_close($ch);
        }
        
       
        public function orangeMoneyPaymentAction(){
      
            //session_start();
            Zend_Session::start();
            $sessionId = Zend_Session::getId();
            //echo $sessionId; die(); 
            //$url = "https://ompay.orange.ci/e-commerce_test_gw/init.php";
            //$url = "https://ompay.orange.ci/e-commerce";
            $url = "https://ompay.orange.ci/e-commerce/init.php";
            
            $client = new Zend_Http_Client($url);

            $client->setHeaders(array(
                'User-Agent:Mozilla/5.0 Firefox/3.6.12',
                'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language:en-us,en;q=0.5',
                'Accept-Encoding:deflate',
                'Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'Content-Type:application/x-www-form-urlencoded',
                'Content-Length:109'
            ));
            
            $client->setParameterPost(array(
                'merchantid' => '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e',
                'amount' => 10,
                'sessionid' => $sessionId,
                'purchaseref' => 'ARTICLE1'
            ));

            $response = $client->request(Zend_Http_Client::POST); 
            $token = $response->getRawBody();
           $token = trim($token);
           
           echo htmlspecialchars($client->getLastRequest(), ENT_QUOTES).'<br><br>';
            echo htmlspecialchars($client->getLastResponse(), ENT_QUOTES).'<br><br>';
            
           echo 'OMPAY Live Token - '.$token; die();
            //$redirectUrl = 'http://joboffersforme.com/request.php';
            $redirectUrl = 'https://ompay.orange.ci/e-commerce_test_gw/';
            $data = array(
                'merchantid' => '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e',
                'token' => $token,
                'amount' => 10,
                'sessionid' => $sessionId,
                'purchaseref' => 'ARTICLE1'
            );
           
           echo $token.'#######'.$sessionId; die();
            
           //print_r($data); die();
           //$this->redirectPost($redirectUrl, $data);
            
       }
       
       /*public function redirectPost($url, array $data)
        {
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
            <form name="redirectpost" method="post" action="<? echo $url; ?>">
                <?
                if ( !is_null($data) ) {
                    foreach ($data as $k => $v) {
                        echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
                    }
                }
                ?>
            </form>
            </body>
            </html>
            
            <?php
            exit;
        }*/
        
        public function ycoinsLoginAction(){
 
            $url = "http://stage.centili.com/softbank-api/user/password";
            
            $client = new Zend_Http_Client($url);

            $apiUn = 'nexva';
            $username = 'heshan@nexva.com';
            $password = 'neXva2014';
            $signKey = 'nexva';
            
            $auth = 'BASIC '.base64_encode($username.':'.$password);
            
            //$conc = $apiUn.$password.$username.$signKey;
            $conc = $auth.$apiUn.$signKey;
            
            $signature = hash_hmac('sha256', $conc, $signKey);
            
            //$signature = '98e13849a6575a1786feb51723f07c97506b0751c3a9b8d9b859fe9e9fd94ca3';

            $client->setHeaders(array(
                'Authorization:'.$auth,
                'ibAuth:'.$apiUn,
                'ibSignature:'.$signature
            ));
            
            /*$client->setParameterGet(array(
                'username' => 'rooban@nexva.com',
                'password' => 'neXva2014',
            ));*/

            $response = $client->request(Zend_Http_Client::GET); 
            echo htmlspecialchars($client->getLastRequest(), ENT_QUOTES).'<br><br>';
            echo htmlspecialchars($client->getLastResponse(), ENT_QUOTES).'<br><br>';
            $user = $response->getRawBody();
            echo $auth.'###'.$signature.'<br><br>'; 
            echo $user.'<br><br>';
            Zend_Debug::dump($user);
            
            $response = Zend_Http_Response::fromString($client->getLastResponse());
            echo '###'.$response->getStatus().'###';
            
            die();
        }
        
        public function testYcoinsLoginAction(){
            
            $url = "http://stage.centili.com/softbank-api/user/password";
            
            $client = new Zend_Http_Client($url);

            $apiUn = 'nexva';
            $username = 'rooban@nexva.com';
            $password = 'neXva2014';
            $signKey = 'nexva';
            
            $auth = 'BASIC '.base64_encode($username.':'.$password);
            
            //$conc = $apiUn.$password.$username.$signKey;
            $conc = $auth.$apiUn.$signKey;
            
            $signature = hash_hmac('sha256', $conc, $signKey);

            $client->setHeaders(array(
                'Authorization:'.$auth,
                'ibAuth:'.$apiUn,
                'ibSignature:'.$signature
            ));

            $data = '';
            //$data = 'studentId=113&pin=dumindu&type=0';
            //$json = json_encode($data);

            //$resp = $client->setRawData($data, 'application/x-www-form-urlencoded')->request('GET');

            $resp = $client->request(Zend_Http_Client::GET); 
            
            echo htmlspecialchars($client->getLastRequest(), ENT_QUOTES).'<br><br>';
            echo '--------------------------------------------------------<br><br>';
            echo htmlspecialchars($client->getLastResponse(), ENT_QUOTES).'<br><br>';
            echo '---------------------------------------------------------<br><br>';
            
            echo $results = $resp->getBody();
            
            $response = Zend_Http_Response::fromString($client->getLastResponse());
            echo '###'.$response->getStatus().'###';
            
            Zend_Debug::dump($results);
            
           
            die();
        }
        
        public function qelasyLoginAction(){
            
            /*$base_url = 'http://silk-outsourcing.com';
            $endpoint = '/qelasysecurity/web/index.php/api/default/appStoreAuthentication';
            $data = array('X-USERNAME' => 'non-qelasy-stg', 'X-PASSWORD' => 'non-qelasy-stg', 'email' => 'dumindu%40app-monkeyz.com', 'pin' => 'dumindu', 'type' => '1');
            $client = new Zend_Rest_Client($base_url);
            $client->X-USERNAME('non-qelasy-stg');
            $client->X-PASSWORD('non-qelasy-stg');
            $response = $client->restPost($endpoint, $data);
            print_r($response);*/
           
            
            /*$url = "http://silk-outsourcing.com/qelasysecurity/web/index.php/api/default/appStoreAuthentication";
            $client = new Zend_Http_Client($url);
            $client->setHeaders(array(
                'X-USERNAME:non-qelasy-stg',
                'X-PASSWORD:non-qelasy-stg',
                'Content-Type:application/x-www-form-urlencoded'
            ));

            $client->setParameterGet(array(
                'email' => 'dumindu@app-monkeyz.com',
                'pin' => 'dumindu',
                'type' => 1
            ));
            
            $response = $client->request(Zend_Http_Client::POST); 
            echo htmlspecialchars($client->getLastRequest(), ENT_QUOTES).'<br><br>';
            echo '--------------------------------------------------------<br><br>';
            echo htmlspecialchars($client->getLastResponse(), ENT_QUOTES).'<br><br>';
            echo '---------------------------------------------------------<br><br>';
            
            echo $response->getRawBody();
            //$results = json_decode($response->getRawBody(), true);
            //print_r($results);
            die();*/

            
            $uri = 'http://silk-outsourcing.com/qelasysecurity/web/index.php/api/default/appStoreAuthentication';

            $config = array(
                    'adapter'   => 'Zend_Http_Client_Adapter_Curl',
                    'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
            );
            $client = new Zend_Http_Client($uri, $config);

            $client->setHeaders(array(
                'X-USERNAME:non-qelasy-stg',
                'X-PASSWORD:non-qelasy-stg',
                'Content-Type:application/x-www-form-urlencoded'
            ));
            
            /*$data = array(
                    'email' => 'dumindu@app-monkeyz.com',
                    'pin' => 'dumindu',
                    'type' => '1'
            );*/

            /*$client->setParameterGet(array(
                'email' => 'dumindu@app-monkeyz.com',
                'pin' => 'dumindu',
                'type' => 1
            ));*/
            
            $data = 'email=dumindu@app-monkeyz.com&pin=dumindu&type=1';
            //$data = 'studentId=113&pin=dumindu&type=0';
            //$json = json_encode($data);

            $resp = $client->setRawData($data, 'application/x-www-form-urlencoded')->request('POST');

            echo htmlspecialchars($client->getLastRequest(), ENT_QUOTES).'<br><br>';
            echo '--------------------------------------------------------<br><br>';
            echo htmlspecialchars($client->getLastResponse(), ENT_QUOTES).'<br><br>';
            echo '---------------------------------------------------------<br><br>';
            
            echo $results = $resp->getBody();

            echo '---------------------------------------------------------<br><br>';
            
           $phpNative = Zend_Json::decode($results);
           print_r($phpNative);
            
            echo '---------------------------------------------------------<br><br>';
            
            try {
                $json = Zend_Json::decode($results);

                print_r($json);
            } catch (Exception $ex) {
                echo "failed to decode json";
            }
 
            exit();
            
        }
        
        public function dataAction()
        {
            $post = $this->getRequest()->getRawBody();

            try {
                $json = Zend_Json::decode($post);

                print_r($json);
            } catch (Exception $ex) {
                echo "failed to decode json";
            }

            exit;
        }
        
        public function formAction(){
            
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
                <form name="redirectpost" method="post" action="https://ompay.orange.ci/e-commerce_test_gw/">
                    <input type="hidden" name="merchantid" value="0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e"/>
                    <input type="hidden" name="token" value="<?= $token ?>"/>
                    <input type="hidden" name="sessionid" value="<?= $sessionId ?>"/>
                    <input type="hidden" name="purchaseref" value="<?= $purchaseref ?>"/>
                    <input type="hidden" name="amount" value="<?= $price ?>"/>
                </form>
            </body>
        </html>
        
        <?php
    }
    
        /*public function formAction(){
            
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
                <form name="redirectpost" method="post" action="https://ompay.orange.ci/e-commerce_test_gw/">
                    <input type="hidden" name="merchantid" value="0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e"/>
                    <input type="hidden" name="token" value="457559cf7bbf9d6da07525ec772991b2e402ab4e4a06f8cce0d817c0da581868"/>
                    <input type="hidden" name="sessionid" value="72ls418cb1qec35ppqjv97mun2"/>
                    <input type="hidden" name="purchaseref" value="ARTICLE1"/>
                    <input type="hidden" name="amount" value="10"/>
                </form>
            </body>
        </html>
        
        <?php
    }*/

    public function curlRedirectAction(){
        
        $data = array(
            'merchantid' => '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e', 
            'token' => 'aca9fae6f2ad9a75ab3dd2feb53110ccfe6e93adee82d975d341abdc5e0ba0e2',
            'sessionid' => 'tbi8tlcqqhielnp2pnucavakt4',
            'purchaseref' => 'ARTICLE1',
            'amount' => 10
            );
        
        $curl = curl_init('https://ompay.orange.ci/e-commerce_test_gw/');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        //curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

        curl_exec($curl);
    }
    
    public function userMetaAction(){
        
        $userModel = new Model_User();
        echo $userModel->getMetaValue(135003, 'FIRST_NAME');
        die();
    }
    
    public function paymentMtnIranAction(){

        /*$spEndPoint =  'http://92.42.55.109:8310/AmountChargingService/services/AmountCharging';
        $spId = '000201';
        $spPass = 'e6434ef249df55c7a21a0b45758a39bb';
        $spServiceId = '3500001000012';
        $oa = '989377123456';
        $fa = $mobileNo;
         
        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        
        $timeStamp = date("Ymd").date("His");
         
        $header = array(
                            'RequestSOAPHeader' => array ( 
                                                            'spId' => $spId, 
                                                            'spPassword' => '0f596813534b39c9429d6ba598f80b6d', 
                                                            'serviceId' => $spServiceId, 
                                                            'timeStamp' => $timeStamp, 
                                                            'OA' => $oa, 
                                                            'FA' => $fa
                                                         )
                          );
        
        $error = '';        
        
        //For Iran, they need the amount without decimal
        $amount = ceil($currencyRate * $price);
                
        //$amount = 1000;
        
        $charge = array(
        		'description' => $appId,
        		'currency' => 'IRR',
        		'amount' => $amount,
        		'code' => 1
        );
       
        $timeStamp = date("Ymd").date("His");
        
        $paymentTransId =  $timeStamp;
  
        //     - Unique ID of the request.  can take as transaction id 

        $paymentInfo = array(
        		'endUserIdentifier'     => $mobileNo,
        		'charge'   => $charge,
        		'referenceCode'  => $paymentTransId
        );
        
        $result = $client->call('chargeAmount', $paymentInfo, '', '', $header);
       
        $paymentTimeStamp = date('d-m-Y');
               
        //trun on this for debuging
print_r($result);
    echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    die();*/
    
        $chapId = 23045;
        $buildId = 71259;
        $appId = 39528;
        $mobileNo = '989381994779'; 
        $appName = 'Nexva Test App';
        $price = 0.01;
                
        //$client = new nusoap_client('http://92.42.55.91:8310/AmountChargingService/services/AmountCharging', false);
        $client = new nusoap_client('http://92.42.55.109:8310/AmountChargingService/services/AmountCharging', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");
        $spId = '001808';
        //$pass = 'e6434ef249df55c7a21a0b45758a39bb';
        //$spPass = md5($spId.$pass.$timeStamp);
        
        $currencyUserModel = new Api_Model_CurrencyUsers(); 
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        $amount = ceil($currencyRate * $price);

        $paymentTimeStamp = date('Y-m-d H:i:s');
        $paymentTransId = strtotime("now");
        //$this->_paymentId
        
        $msg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" xmlns:loc="http://www.csapi.org/schema/parlayx/payment/amount_charging/v2_1/local"> 
   <soapenv:Header> 
      <v2:RequestSOAPHeader> 
         <v2:spId>'.$spId.'</v2:spId> 
         <v2:spPassword></v2:spPassword> 
         <v2:serviceId>0018082000002380</v2:serviceId> 
         <v2:timeStamp></v2:timeStamp> 
         <v2:OA>'.$mobileNo.'</v2:OA>  
         <v2:FA>'.$mobileNo.'</v2:FA> 
         <v2:token/> 
         <v2:namedParameters>
            <v2:item>
               <v2:key>contentId</v2:key>
               <v2:value>'.$appId.'</v2:value>
            </v2:item>
            <v2:item>
               <v2:key>country</v2:key>
               <v2:value>'.$paymentTransId.'</v2:value>
            </v2:item>
         </v2:namedParameters>
      </v2:RequestSOAPHeader> 
   </soapenv:Header> 
   <soapenv:Body> 
      <loc:chargeAmount> 
         <loc:endUserIdentifier>tel:'.$mobileNo.'</loc:endUserIdentifier> 
         <loc:charge> 
            <description>charging information</description> 
            <currency>IRR</currency> 
            <amount>'.$amount.'</amount> 
            <code>10080</code> 
         </loc:charge> 
         <loc:referenceCode>'.$timeStamp.'</loc:referenceCode> 
      </loc:chargeAmount> 
   </soapenv:Body> 
</soapenv:Envelope>
';

        $result = $client->send($msg, '');

            Zend_Debug::dump($result); 
            echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
            echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
            echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
   
            die();
        
    }
    
    public function loginMtnIranAction(){
        //$client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
        $client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $msg = '<soap:Envelope soap:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
   <soap:Header>
      <pr:authentication xmlns:pr="http://webservices.irancell.com/ProxyService">
         <pr:user>mw_bks</pr:user>
         <pr:password>MW_bks032Mtn</pr:password>
      </pr:authentication>
   </soap:Header>
   <soap:Body>
      <open:clientRequest xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
         <env:EaiEnvelope>
            <env:Domain>BookStoreAPI</env:Domain>
            <env:Service>Ecare</env:Service>
            <env:Language>en</env:Language>
            <env:UserId>989381994779</env:UserId>
            <env:Sender>abl_bks</env:Sender>
            <env:MessageId>'.rand(100,999).'</env:MessageId>
            <env:Payload>
               <ec:EcareData xmlns:ec="http://eai.mtn.iran/Ecare">
                  <ec:Request>
                     <ec:Operation_Name>authenticateCustomer</ec:Operation_Name>
                     <ec:CustDetails_InputData>
                        <ec:MSISDN>989381994779</ec:MSISDN>
                        <ec:newPassword>433B3C</ec:newPassword>
                        <ec:language>En</ec:language>
                     </ec:CustDetails_InputData>
                  </ec:Request>
               </ec:EcareData>
            </env:Payload>
         </env:EaiEnvelope>
      </open:clientRequest>
   </soap:Body>
</soap:Envelope>
';

                
       /* $msg = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
         <SOAP-ENV:Header>
            <ns1:authentication xmlns:ns1="http://webservices.irancell.com/ProxyService">
              <ns1:user xsi:type="xsd:string">mw_pn</ns1:user>              
              <ns1:password xsi:type="xsd:string">MW_pn030Mtn</ns1:password>
            </ns1:authentication>
          </SOAP-ENV:Header>
          <SOAP-ENV:Body>
            <ns2:clientRequest xmlns:ns2="http://www.openuri.org/">
              <ns3:EaiEnvelope xmlns:ns3="http://eai.mtnn.iran/Envelope">
                <ns3:Domain xsi:type="xsd:string">Portal</ns3:Domain>
                <ns3:Service xsi:type="xsd:string">Ecare</ns3:Service>
                <ns3:Language xsi:type="xsd:string">en</ns3:Language>
                <ns3:UserId xsi:type="xsd:string">abl_care</ns3:UserId>
                <ns3:Sender xsi:type="xsd:string">abl_care</ns3:Sender>
                <ns3:MessageId xsi:type="xsd:string">504016000001401131554427972005</ns3:MessageId>
                <ns3:CorrelationId xsi:type="xsd:string">504016000001401131554427972005</ns3:CorrelationId>
                <ns3:GenTimeStamp xsi:type="xsd:string">2014-02-26T09:39:51</ns3:GenTimeStamp>
                <ns3:Payload>
                  <ns3:EcareData>
                    <ns4:Request xmlns:ns4="http://eai.mtn.iran/Ecare">
                      <ns4:Operation_Name xsi:type="xsd:string">authenticateCustomer</ns4:Operation_Name>
                      <ns4:CustDetails_InputData>
                        <ns4:MSISDN xsi:type="xsd:string">989373390219</ns4:MSISDN>
                        <ns4:language xsi:type="xsd:string">en</ns4:language>
                        <ns4:newPassword xsi:type="xsd:string">nina1194</ns4:newPassword>
                      </ns4:CustDetails_InputData>
                    </ns4:Request>
                  </ns3:EcareData>
                </ns3:Payload>
              </ns3:EaiEnvelope>
            </ns2:clientRequest>
        </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';*/

        $result = $client->send($msg, 'http://92.42.51.122:7001/MTNIranCell_Proxy');
        
        Zend_Debug::dump($result); 
        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
            die();
    }
    
    
    public function getxmlfoAction(){
         error_reporting(E_ALL);
         ini_set('display_errors', 1);
         
         
         $xml = '<?xml version="1.0" encoding="utf-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"><soapenv:Header xmlns:env="http://eai.mtnn.iran/Envelope" xmlns:open="http://www.openuri.org/">
      <pr:authentication soapenv:actor="http://schemas.xmlsoap.org/soap/actor/next" soapenv:mustUnderstand="0" xmlns:pr="http://webservices.irancell.com/ProxyService">
         <pr:user>mw_bks</pr:user>
         <pr:password>MW_bks032Mtn</pr:password>
      </pr:authentication>
   </soapenv:Header><soapenv:Body xmlns:env="http://eai.mtnn.iran/Envelope" xmlns:open="http://www.openuri.org/">
      <ns:clientRequestResponse xmlns:ns="http://www.openuri.org/">
         <EaiEnvelope xmlns="http://eai.mtnn.iran/Envelope" xmlns:cus="http://eai.mtn.iran/CustomerProfile">
            <Domain>abl_Portal</Domain>
            <Service>CustomerProfile</Service>
            <Sender>abl_bks</Sender>
            <MessageId>2102011B111621</MessageId>
            <Language>En</Language>
            <UserId>989339900902</UserId>
            <SentTimeStamp>2014-09-03T11:06:00+00:00</SentTimeStamp>
            <Payload>
               <cus:CustomerProfile xmlns:m="http://www.openuri.org/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <cus:Header_Payload>
                 <cus:Language>En</cus:Language>
                 <cus:UserId>989339900902</cus:UserId>
                 <cus:Sender>abl_bks</cus:Sender>
                 <cus:MessageId>2102011B111621</cus:MessageId>
                 </cus:Header_Payload>
                <cus:Response>
               <cus:CustDetails_OutputData>
                 <cus:MSISDN>989339900902</cus:MSISDN>
                 <cus:Customer_Type>P</cus:Customer_Type>
                 <cus:Operation_Status>A</cus:Operation_Status>
                 <cus:ReferenceNumber>245870986</cus:ReferenceNumber>
                 <cus:InfoLevel xsi:nil="true"/></cus:CustDetails_OutputData>
                 <cus:Result_OutputData>
                 <cus:resultCode>0</cus:resultCode>
                 <cus:resultMessage>Operation Completed Successfully</cus:resultMessage>
                 <cus:reference_ID>3368300567</cus:reference_ID>
                 </cus:Result_OutputData>
                 </cus:Response>
               </cus:CustomerProfile>
            </Payload>
         </EaiEnvelope>
      </ns:clientRequestResponse>
   </soapenv:Body></soapenv:Envelope>';
         
            $doc = new DOMDocument();
            $doc->loadXML($xml);
        
        $xml = simplexml_load_string($xml);
       Zend_Debug::Dump($xml);
        
        
        Zend_Debug::Dump($doc->getElementsByTagName('Customer_Type')->item(0)->nodeValue, 'dd');
        
         //  Zend_Debug::Dump($doc->getElementsByTagName('Payload'), 'ff');;
        
         die();
         
    }
    
    public function mtnIranSignInAction() {

        $password = '399F9G';
        $mobileNumber = '989339900201';
        $source = null;
        $data = null;
                
        $client = new nusoap_client('http://92.42.51.122:7001/MTNIranCell_Proxy', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $objDateTime = new DateTime('NOW');
        $dateTime = $objDateTime->format('c'); 
        
        $xmlGetMssidn = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
                       <soapenv:Header>
                          <pr:authentication soapenv:actor="http://schemas.xmlsoap.org/soap/actor/next" soapenv:mustUnderstand="0" xmlns:pr="http://webservices.irancell.com/ProxyService">
                             <pr:user>mw_bks</pr:user>
                             <pr:password>MW_bks032Mtn</pr:password>
                          </pr:authentication>
                       </soapenv:Header>
                       <soapenv:Body>
                          <ns:clientRequest xmlns:ns="http://www.openuri.org/">
                             <EaiEnvelope xmlns="http://eai.mtnn.iran/Envelope" xmlns:cus="http://eai.mtn.iran/CustomerProfile">
                                <Domain>abl_Portal</Domain>
                                <Service>CustomerProfile</Service>
                                <Sender>abl_bks</Sender>
                                <MessageId>2102011B111621</MessageId>
                                <Language>En</Language>
                                <UserId>'.$mobileNumber.'</UserId>
                                <SentTimeStamp>'.$dateTime.'</SentTimeStamp>
                                <Payload>
                                   <cus:CustomerProfile>
                                      <cus:Request>
                                         <cus:Operation_Name>GetMSISDNInfo</cus:Operation_Name>
                                         <cus:CustDetails_InputData>
                                            <cus:MSISDN>'.$mobileNumber.'</cus:MSISDN>
                                         </cus:CustDetails_InputData>
                                      </cus:Request>
                                   </cus:CustomerProfile>
                                </Payload>
                             </EaiEnvelope>
                          </ns:clientRequest>
                       </soapenv:Body>
                    </soapenv:Envelope>';

        $resultMsisdn = $client->send($xmlGetMssidn, 'http://92.42.51.122:7001/MTNIranCell_Proxy');
   
   
                Zend_Debug::dump($result); 
                echo '<h2>Request</h2><pre>' . htmlspecialchars($clientAuth->request, ENT_QUOTES) . '</pre>';
                echo '<h2>Response</h2><pre>' . htmlspecialchars($clientAuth->response, ENT_QUOTES) . '</pre>';
               echo '<h2>Debug</h2><pre>' . htmlspecialchars($clientAuth->debug_str, ENT_QUOTES) . '</pre>';
        
         
        
        $headerResponse = Zend_Http_Response::fromString($client->response);
        $bodyGetMsisdn = $headerResponse->getRawBody();
        $doc = new DOMDocument();
        $doc->loadXML($bodyGetMsisdn);
        $customerType = $doc->getElementsByTagName('Customer_Type')->item(0)->nodeValue;
        $resultCode = $doc->getElementsByTagName('resultCode')->item(0)->nodeValue;
        
        if ($customerType == 'P' && $resultCode == 0) {

            $clientAuth = new nusoap_client('http://92.42.51.122:7001/MTNIranCell_Proxy', false);
            $clientAuth->soap_defencoding = 'UTF-8';
            $clientAuth->decode_utf8 = false;

            $xmlUserAuthentication = '<soap:Envelope soap:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
               <soap:Header>
                  <pr:authentication xmlns:pr="http://webservices.irancell.com/ProxyService">
                     <pr:user>mw_bks</pr:user>
                     <pr:password>MW_bks032Mtn</pr:password>
                  </pr:authentication>
               </soap:Header>
               <soap:Body>
                  <open:clientRequest xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
                     <env:EaiEnvelope>
                        <env:Domain>BookStoreAPI</env:Domain>
                        <env:Service>Ecare</env:Service>
                        <env:Language>en</env:Language>
                        <env:UserId>989339900410</env:UserId>
                        <env:Sender>abl_bks</env:Sender>
                        <env:MessageId>062222014</env:MessageId>
                        <env:Payload>
                           <ec:EcareData xmlns:ec="http://eai.mtn.iran/Ecare">
                              <ec:Request>
                                 <ec:Operation_Name>authenticateCustomer</ec:Operation_Name>
                                 <ec:CustDetails_InputData>
                                    <ec:MSISDN>' . $mobileNumber . '</ec:MSISDN>
                                    <ec:newPassword>' . $password . '</ec:newPassword>
                                    <ec:language>En</ec:language>
                                 </ec:CustDetails_InputData>
                              </ec:Request>
                           </ec:EcareData>
                        </env:Payload>
                     </env:EaiEnvelope>
                  </open:clientRequest>
               </soap:Body>
            </soap:Envelope>
            ';

            $result = $clientAuth->send($xmlUserAuthentication, 'http://92.42.51.122:7001/MTNIranCell_Proxy');
            $returnResponse = Array();

        
                Zend_Debug::dump($result); 
                echo '<h2>Request</h2><pre>' . htmlspecialchars($clientAuth->request, ENT_QUOTES) . '</pre>';
                echo '<h2>Response</h2><pre>' . htmlspecialchars($clientAuth->response, ENT_QUOTES) . '</pre>';
               echo '<h2>Debug</h2><pre>' . htmlspecialchars($clientAuth->debug_str, ENT_QUOTES) . '</pre>';
        
    
            
            $headerResponseLogin = Zend_Http_Response::fromString($clientAuth->response);
            $bodyGetLogin = $headerResponseLogin->getRawBody();
            $doc = new DOMDocument();
            $doc->loadXML($bodyGetLogin);
            $resultCode = $doc->getElementsByTagName('resultCode')->item(0)->nodeValue;
            $returnMsg = $doc->getElementsByTagName('resultMessage')->item(0)->nodeValue;
            
            //$returnMsg = $result['EaiEnvelope']['Payload']['EcareData']['Response']['Result_OutputData']['resultMessage'];

            if ($resultCode == 0) {
                
                echo 'Success Login';
                //$firstName = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['firstName'];
                //$lastName = $result['EaiEnvelope']['Payload']['EcareData']['Response']['CustDetails_OutputData']['lastName'];
                //$username = $firstName;
                //echo $username; die();
                //--------------------registration process from our side-----------------------

                
                }
             else {

                echo $returnMsg;
            }
        } else {
            
            if($customerType != 'P' && $resultCode == 0)
            {
                $faultMsg = 'Please enter a prepaid MSISDN' ;
            }
            else{
                $faultMsg = 'Invalid MSISDN or password' ;
            }
            
            echo $faultMsg;
        }
        die();
    }
    
     public function getMsisdnInfoAction(){
         error_reporting(E_ALL);
         ini_set('display_errors', 1);
         
         
         //$client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
        $client = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);
        
        // echo 'test'; die(); 
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        


        //$objDateTime = new DateTime('NOW');
        //echo $dateTime = $objDateTime->format('c'); // ISO8601 formated datetime
        
        $objDateTime = new DateTime('NOW');
        $dateTime = $objDateTime->format('c'); 

        $msg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
   <soapenv:Header>
      <pr:authentication soapenv:actor="http://schemas.xmlsoap.org/soap/actor/next" soapenv:mustUnderstand="0" xmlns:pr="http://webservices.irancell.com/ProxyService">
         <pr:user>mw_bks</pr:user>
         <pr:password>MW_bks032Mtn</pr:password>
      </pr:authentication>
   </soapenv:Header>
   <soapenv:Body>
      <ns:clientRequest xmlns:ns="http://www.openuri.org/">
         <EaiEnvelope xmlns="http://eai.mtnn.iran/Envelope" xmlns:cus="http://eai.mtn.iran/CustomerProfile">
            <Domain>abl_Portal</Domain>
            <Service>CustomerProfile</Service>
            <Sender>abl_bks</Sender>
            <MessageId>2102011B111621</MessageId>
            <Language>En</Language>
            <UserId>989381994779</UserId>
            <SentTimeStamp>'.$dateTime.'</SentTimeStamp>
            <Payload>
               <cus:CustomerProfile>
                  <cus:Request>
                     <cus:Operation_Name>GetMSISDNInfo</cus:Operation_Name>
                     <cus:CustDetails_InputData>
                        <cus:MSISDN>989381994779</cus:MSISDN>
                     </cus:CustDetails_InputData>
                  </cus:Request>
               </cus:CustomerProfile>
            </Payload>
         </EaiEnvelope>
      </ns:clientRequest>
   </soapenv:Body>
</soapenv:Envelope>
';

        $result = $client->send($msg, 'http://92.42.51.113:7001/MTNIranCell_Proxy');

        $headerResponse = Zend_Http_Response::fromString($client->response);
        $bodyGetMsisdn = $headerResponse->getRawBody();
        $doc = new DOMDocument();
        $doc->loadXML($bodyGetMsisdn);
        $customerType = $doc->getElementsByTagName('Customer_Type')->item(0)->nodeValue;
        $resultCode = $doc->getElementsByTagName('resultCode')->item(0)->nodeValue;
        
       /*     echo '<br/>';
            echo $body .'<br><br>######';
    echo htmlspecialchars($body, ENT_QUOTES) .'<br>######';
    echo '<br/><br/><br/>';

        echo $client->getHTTPBody($client->response).'###';
        echo $client->response;
        die();*/
        
        
        echo $customerType.'##'.$resultCode; 
        
       //echo $result['EaiEnvelope']['Payload']['CustomerProfile']['Response']['CustDetails_OutputData']['Customer_Type'] .'###'. $resultMsisdn['EaiEnvelope']['Payload']['CustomerProfile']['Response']['Result_OutputData']['resultCode'];
                
        Zend_Debug::dump($result); 
        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
            die();
    }
    
    //Temporary function for insert stats and royalities to apps
    public function insertStatAndRoyalitiesAction(){
        
    }
    
    //Temporary function for add apps to relavent chaps
    public function insertAppsToChapsAction(){
        
        /*
        $chapId = 23045;//MTN Iran
        
        $arrPrIds = array();//IDs goes here
        $productIDS = array_unique($arrPrIds);//Product IDs

        $chapterPlatformType = NULL;
        if($chapId){
            $themeMetaModel = new Model_ThemeMeta();
            $themeMetaModel->setEntityId($chapId);
            $chapterPlatformType = $themeMetaModel->WHITELABLE_PLATEFORM;
        }
       
        $productModel = new Model_Product();

        $chapProductModel = new Pbo_Model_ChapProducts();
        $errorProdIds = Array();
        $addError = FALSE;
        $addSuccess = FALSE;
        foreach($productIDS as $productID)
        {
            // Added for check and add apps which are applicable for chapter site
            $supportedPlatforms = $productModel->getSupportedPlatforms($productID);
            $appPlatformType = $productModel->verifyPlatformType($supportedPlatforms, $productID);
            
            if($chapterPlatformType == 'ANDROID_PLATFORM_CHAP_ONLY' && $appPlatformType == 'NON_ANDROID_PLATFORM_APP'){
                $errorProdIds[]=$productID;
                $addError = TRUE;
            }
            else{
                $errorProdIds[]=$productID;
                $chapProductModel->addProductToChap($chapId, $productID);
                $addSuccess = TRUE;
            }
           
        }
        
        if($addError){
            echo 'The following apps are not compatible with the chapter platform type. ID : '.implode(',', $errorProdIds);
        }
        
        if($addSuccess){
            echo 'Apps successfully added to your store.'.count($errorProdIds).'###'.implode(',', $errorProdIds);
        }

        die();
         
         */
    }
    
    public function checkIpCountryAction(){
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        
        $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider('Api');
        $countryCode  =   $geoData->getCountry($_SERVER['REMOTE_ADDR']);
        print_r($countryCode);
        die();
    }
        
}




class Uganda_Soap_Functions 
{

    function processRequest($p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $p10, $p11, $p12)
    {
        
        $objMain = new stdClass();
        
        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj3 = new stdClass();
        $obj4 = new stdClass();
        $obj5 = new stdClass();
        
        $obj1->return->name = $p2->name;
        $obj1->return->value = $p2->value;

        $obj2->return->name = $p10->name;
        $obj2->return->value = $p10->value;
        
        $obj3->return->name = '';
        $obj3->return->value = '';
        
        $obj4->return->name = 'ThirdPartyAcctRef';
        $obj4->return->value = $p4->value;

        $obj5->return->name = '';
        $obj5->return->value = '';

        return array($obj1,$obj2,$obj3,$obj4,$obj5);

    }

}


class TestSoapServer extends Zend_Soap_Server 
{
    
     public function __construct($wsdl, $options = null)
    {
        return parent::__construct($wsdl, $options);
    }
    
    public function handleUganda($request = null)
    {

        if (null === $request) {
            $request = file_get_contents('php://input');
        }

        // Set Zend_Soap_Server error handler
        $displayErrorsOriginalState = $this->_initializeSoapErrorContext();

        $setRequestException = null;
        /**
         * @see Zend_Soap_Server_Exception
         */

        try {
            $this->_setRequest($request);
        } catch (Zend_Soap_Server_Exception $e) {
            $setRequestException = $e;
        }
        
        $soap = $this->_getSoap();

        $fault = false;
        ob_start();
        if ($setRequestException instanceof Exception) {
            // Create SOAP fault message if we've caught a request exception
            $fault = $this->fault($setRequestException->getMessage(), 'Sender');
        } else {
            try {
                $soap->handle($this->_request);
            } catch (Exception $e) {
                $fault = $this->fault($e);
            }
        }
        $this->_response = ob_get_clean();


        // Custom response
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($request);
        libxml_clear_errors();
        $xml = $doc->saveXML($doc->documentElement);
        $xml = simplexml_load_string($xml);

        $parameters = $xml->body->envelope->body->processrequest->parameter;
        
       // $arrResponse = simplexml_load_string($request);

        //print_r($p1); die();
        
        $this->_response =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0/">
                              <soapenv:Header/>
                              <soapenv:Body>
                                <b2b:processRequestResponse>
                                  <return>
                                    <name>ProcessingNumber</name>
                                    <value>2117285</value>
                                  </return>
                                  <return>
                                    <name>StatusCode</name>
                                    <value>01</value>
                                  </return>
                                  <return>
                                    <name>StatusDesc</name>
                                    <value></value>
                                  </return>
                                  <return>
                                    <name>ThirdPartyAcctRef</name>
                                    <value>20140502123653</value>
                                  </return>
                                  <return>
                                    <name>Token</name>
                                    <value></value>
                                  </return>
                                </b2b:processRequestResponse>
                              </soapenv:Body>
                            </soapenv:Envelope>';
        // Restore original error handler
        restore_error_handler();
        ini_set('display_errors', $displayErrorsOriginalState);

        // Send a fault, if we have one
        if ($fault) {
            $soap->fault($fault->faultcode, $fault->faultstring);
        }

        if (!$this->_returnResponse) {
            echo $this->_response;
            return;
        }
        
            return $this->_response;
    }

    public function handle($request = null)
    {

        if (null === $request) {
            $request = file_get_contents('php://input');
        }

        // Set Zend_Soap_Server error handler
        $displayErrorsOriginalState = $this->_initializeSoapErrorContext();

        $setRequestException = null;
        /**
         * @see Zend_Soap_Server_Exception
         */

        try {
            $this->_setRequest($request);
        } catch (Zend_Soap_Server_Exception $e) {
            $setRequestException = $e;
        }
        
        $soap = $this->_getSoap();

        $fault = false;
        ob_start();
        if ($setRequestException instanceof Exception) {
            // Create SOAP fault message if we've caught a request exception
            $fault = $this->fault($setRequestException->getMessage(), 'Sender');
        } else {
            try {
                $soap->handle($this->_request);
            } catch (Exception $e) {
                $fault = $this->fault($e);
            }
        }
        $this->_response = ob_get_clean();


        // Custom response
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($request);
        libxml_clear_errors();
        $xml = $doc->saveXML($doc->documentElement);
        $xml = simplexml_load_string($xml);

        $parameters = $xml->body->envelope->body->processrequest->parameter;
        
       // $arrResponse = simplexml_load_string($request);

        //print_r($p1); die();
        
        $this->_response =  '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0/">
                              <soapenv:Header/>
                              <soapenv:Body>
                                <b2b:processRequestResponse>
                                  <return>
                                    <name>'.$parameters[0]->name.'</name>
                                    <value>'.$parameters[0]->value.'</value>
                                  </return>
                                  <return>
                                    <name>'.$parameters[8]->name.'</name>
                                    <value>'.$parameters[8]->value.'</value>
                                  </return>
                                  <return>
                                    <name>StatusDesc</name>
                                    <value></value>
                                  </return>
                                  <return>
                                    <name>ThirdPartyAcctRef</name>
                                    <value>'.$parameters[2]->value.'</value>
                                  </return>
                                  <return>
                                    <name>Token</name>
                                    <value></value>
                                  </return>
                                </b2b:processRequestResponse>
                              </soapenv:Body>
                            </soapenv:Envelope>';
        // Restore original error handler
        restore_error_handler();
        ini_set('display_errors', $displayErrorsOriginalState);

        // Send a fault, if we have one
        if ($fault) {
            $soap->fault($fault->faultcode, $fault->faultstring);
        }

        if (!$this->_returnResponse) {
            echo $this->_response;
            return;
        }
        
            return $this->_response;
    }

    
    
    public function googleLoginnnAction() {
    
    	ini_set('display_errors',1);
    	ini_set('display_startup_errors',1);
    	error_reporting(-1);
    
    	########## Google Settings.. Client ID, Client Secret from https://cloud.google.com/console #############
    	$google_client_id 		= '284226356142-s2bi39h2kgfvbl2ef8pb21eh8742fpck.apps.googleusercontent.com';
    	$google_client_secret 	= 'IRz7CzTuq22em6gJq3-PoOEx';
    	$google_redirect_url 	= 'http://caboapps.nexva.com/user/google-login/'; //path to your script
    	$google_developer_key 	= '284226356142-s2bi39h2kgfvbl2ef8pb21eh8742fpck@developer.gserviceaccount.com';
    
    
    	//include google api files
    	require_once  APPLICATION_PATH.'/../public/vendors/openid/google_auth/src/Google_Client.php';
    	require_once  APPLICATION_PATH.'/../public/vendors/openid/google_auth/src/contrib/Google_Oauth2Service.php';
    
    	//start session
    	//session_start();
    
    	//new Nexva_Google_GoogleAuth();
    
    	$gClient = new Google_Client();
    	$gClient->setApplicationName('Inovex CaboApps Store');
    	$gClient->setClientId($google_client_id);
    	$gClient->setClientSecret($google_client_secret);
    	$gClient->setRedirectUri($google_redirect_url);
    	$gClient->setDeveloperKey($google_developer_key);
    
    	$google_oauthV2 = new Google_Oauth2Service($gClient);
    
    	//If user wish to log out, we just unset Session variable
    	if (isset($_REQUEST['reset']))
    	{
    		unset($_SESSION['token']);
    		$gClient->revokeToken();
    		header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL)); //redirect user back to page
    	}
    
    	//If code is empty, redirect user to google authentication page for code.
    	//Code is required to aquire Access Token from google
    	//Once we have access token, assign token to session variable
    	//and we can redirect user back to page and login.
    	if (isset($_GET['code']))
    	{
    		$gClient->authenticate($_GET['code']);
    		$_SESSION['token'] = $gClient->getAccessToken();
    		header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
    		return;
    	}
    
    
    	if (isset($_SESSION['token']))
    	{
    		$gClient->setAccessToken($_SESSION['token']);
    	}
    
    
    	if ($gClient->getAccessToken())
    	{
    		//For logged in user, get details from google using access token
    		$user 				= $google_oauthV2->userinfo->get();
    		 
    		print_r($user);
    		die();
    		 
    		$user_id 				= $user['id'];
    		$user_name 			= filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
    		$email 				= filter_var($user['email'], FILTER_SANITIZE_EMAIL);
    		$profile_url 			= filter_var($user['link'], FILTER_VALIDATE_URL);
    		$profile_image_url 	= filter_var($user['picture'], FILTER_VALIDATE_URL);
    		$personMarkup 		= "$email<div><img src='$profile_image_url?sz=50'></div>";
    		$_SESSION['token'] 	= $gClient->getAccessToken();
    	}
    	else
    	{
    		//For Guest user, get google login url
    		$authUrl = $gClient->createAuthUrl();
    	}
    
    	//HTML page start
    	echo '<!DOCTYPE HTML><html>';
    	echo '<head>';
    	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    	echo '<title>Login with Google</title>';
    	echo '</head>';
    	echo '<body>';
    	echo '<h1>Login with Google</h1>';
    
    	if(isset($authUrl)) //user is not logged in, show login button
    	{
    		echo '<a class="login" href="'.$authUrl.'"><img src="images/google-login-button.png" /></a>';
    	}
    	else // user logged in
    	{
    		/* connect to database using mysqli */
    
    
    
    		echo '<br /><a href="'.$profile_url.'" target="_blank"><img src="'.$profile_image_url.'?sz=100" /></a>';
    		echo '<br /><a class="logout" href="?reset=1">Logout</a>';
    
    		//list all user details
    		echo '<pre>';
    		print_r($user);
    		echo '</pre>';
    	}
    
    	echo '</body></html>';
    
    	die();
    
    }
    
} 