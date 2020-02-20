<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 4/23/14
 * Time: 5:36 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_MobileBilling_Type_MobilemoneyUganda extends Nexva_MobileBilling_Type_Abstract
{
    public function __construct() {

    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        
        //sleep(800);
        
        //Get the S3 URL of the Relevant build
      //  $productDownloadCls = new Nexva_Api_ProductDownload();
     //   $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
        
    //    return $buildUrl;
   
       
        
        ini_set('default_socket_timeout', 6000);
        
        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];

        $amount = ceil($currencyRate * $price);
      //  echo $chapId,' - ',$buildId,' - ',$appId,' - ',$mobileNo,' - ',$appName,' - ',$price,' - ',$amount;die();

        $client = new nusoap_client('http://172.25.48.36:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v15', false);
        //$client = new nusoap_client('http://api.nexva.com/test/test-uganda-final-request', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

     
       //  $spId =  '2560110000692';  old id fundomo 
        //$spId =  '2560110001348';

        $spId = 2560110002883;
        
        //$spPass = 'Huawei2014';
        
        $spPass = 'Pasword24';
        $nowtime = time();
        
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
						<value>'.$amount.'</value>
					 </parameter>
					 <parameter>
						<name>MSISDNNum</name>
						<value>'.$mobileNo.'</value>
					 </parameter>
					 <parameter>
						<name>ProcessingNumber</name>
						<value>'.$nowtime.'</value>
					 </parameter>
					 <parameter>
						<name>serviceId</name>
						<value>MTNApp.mtn</value>
					 </parameter>
					 <parameter>
						<name>AcctRef</name>
						<value>'.$timeStamp.'</value>
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
						<value>Please confirm the amount of '.$amount.' UGX to Complete Transaction.</value>
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
        
        
        /*
         *  <parameter>
						<name>serviceId</name>
						<value>Appstore.sp</value>
					 </parameter>
         * 
         */

        /*$mobileMoney = new Zend_Session_Namespace('mobilemoney');
        $mobileMoney->accRef = $timeStamp;*/

        $result = $client->send($xmlMsg, 'POST', 0, 180);
        
       //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
     // echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
     //  echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
       
  //     Zend_Debug::dump($result['return'][5]['value'] );
       
   //  die();
//die();
     //   Zend_Debug::dump($result);
    //    die();

/* Zend_Debug::dump($result['return'][1]['value']);
        Zend_Debug::dump($result['return'][3]['value']);
       

        echo $timeStamp,' - ',$result['return'][3]['value'];die();
        die(); */
        /*if( ($result['return'][1]['value'] == 01) ){
            echo 'yes';die();
        }
        else
        {
            echo 'no';die();
        }*/

        $paymentTimeStamp = date('Y-m-d H:i:s');

        if(!$client->fault && ($result['return'][3]['value'] == '01') )
        {
            $error = $client->getError();

            if(!$error)
            {
                //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);


                //$message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
                $message = 'Hello, your account was charged '. $amount.' UGX at '.$paymentTimeStamp. ' for the successful transaction on the MTN Uganda App Store. Thank you.';
				
                $this->sendsms($mobileNo, $message, $chapId);

                $paymentResult = 'Success';
                $paymentTransId = $result['return'][5]['value'];

                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl, $client->request, $client->response);

                return $buildUrl;
            }
        }

    }

    public function sendsms($mobileNo, $message, $chapId)
    {
        //die('Mobile Money');
        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
        
  

        $smppHost = '212.88.118.228';
        $smppPort = '5001';
        $systemId = 'mtnappshop';
        $password = 'n3Xv4';
        $systemType = 'vma';
        $from = 'MTNAppStore';


        $smpp = new SMPPClass();

        $smpp->debugmod(false);
        
        
        $smpp->SetSender($from);
        /* bind to smpp server */
        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
        /* send enquire link PDU to smpp server */
      //  $smpp->TestLink();
        /* send single message; large messages are automatically split */
        //$messageStatus = $smpp->Send('250731000057', 'neXva neXva neXva neXva neXva neXva ');
        $messageStatus = $smpp->Send($mobileNo, $message);

        //Zend_Debug::dump($messageStatus,'booo');
        
  
        
        $smpp->End();
        //die();
        
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH. "/../logs/nexva_sms.log");
        $logger = new Zend_Log($writer);
        
        $logger->info($mobileNo . ' --- '.$message.' || ');
        
        return true;
    }
    
    
    
    public function testsms()
    {
    
    	ini_set('default_socket_timeout', 6000);
    
    	include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
    
    	$client = new nusoap_client('http://10.1.75.124:38080/ParlayXSmsAccess/services/SendSms', false);
    	//$client = new nusoap_client('http://kasdp-traffic-vip.telekom.rs:38080/ParlayXSmsAccess/services/SendSms', false);
    	
/*     	$client = new nusoap_client('http://10.1.75.124:38080/ParlayXSmsAccess/services/SendSms', false);
    	//$client = new nusoap_client('http://api.nexva.com/test/test-uganda-final-request', false);
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
     */
    	
    	$timeStamp = date("Ymd").date("His");

    	
    	  	$xmlMsgb = '<?xml version="1.0" ?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
<SOAP-ENV:Header>
<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
<wsse:UsernameToken wsu:Id="XWSSGID-11435375577461001212174" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
<wsse:Username>S-74F400BU121_S-74F400BU121-2_2@P-3JXDU0TgZo0</wsse:Username>
<wsse:Password >infobip1.</wsse:Password>
<wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">infobip1.</wsse:Nonce>
<wsu:Created xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">2006-03-28T09:19:18Z</wsu:Created>
</wsse:UsernameToken>
</wsse:Security>
</SOAP-ENV:Header>
<SOAP-ENV:Body>
<ns2:sendSms xmlns:ns2="http://www.csapi.org/schema/parlayx/sms/send/v2_1/local" xmlns:ns3="http://www.csapi.org/schema/parlayx/common/v2_1">
<ns2:addresses>tel:381645552117</ns2:addresses>
<ns2:senderName>InfobipTest</ns2:senderName>
<ns2:message>Testing Parlay X WS.</ns2:message>
</ns2:sendSms>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
    	
    	
    
    	//$result = $client->send($xmlMsg, 'POST', 0, 180);
    	  //	<wsse:Username>S-74F400BU121_S-74F400BU121-2_2@P-3JXDU0TgZo0</wsse:Username>
    	
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    	
    	
    	$timeStamp = date("Ymd").date("His");
    	// $timeStamp = 20141217125900;
    	
    	// $password = md5($spId.$pass.$timeStamp);
    	
    	$header = array('Security' => array('UsernameToken' => array ( 'Username' => 'S-74F400BU121_S-74F400BU121-2_2@P-3JXDU0TgZo0', 'Password' => 'infobip1.', 'Created' => $timeStamp )));
    	
    	$phone = array(
    			'addresses'     =>  'tel:'.'381640596018',
    			'senderName'   =>  '1076',
    			'message'  => 'ph3'
    	);
    	 
    	
    ///	$result = $client->call('sendSms', $phone, '', '', $xmlMsg);
    	
    	$result = $client->send($xmlMsgb, '', 0, 300);
    	
    	
    
    	echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
    die();
    	//die();
    	//   Zend_Debug::dump($result);
    	//    die();
    
    	/* Zend_Debug::dump($result['return'][1]['value']);
    	 Zend_Debug::dump($result['return'][3]['value']);
    	 
    
    	echo $timeStamp,' - ',$result['return'][3]['value'];die();
    	die(); */
    	/*if( ($result['return'][1]['value'] == 01) ){
    	 echo 'yes';die();
    	}
    	else
    	{
    	echo 'no';die();
    	}*/
    
    	$paymentTimeStamp = date('Y-m-d H:i:s');
    
    	if(!$client->fault && ($result['return'][3]['value'] == '01') )
    	{
    		$error = $client->getError();
    
    		if(!$error)
    		{
    			//Get the S3 URL of the Relevant build
    			$productDownloadCls = new Nexva_Api_ProductDownload();
    			$buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
    
    
    			//$message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
    			$message = 'Hello, your account was charged '. $amount.' UGX at '.$paymentTimeStamp. ' for the successful transaction on the MTN Uganda App Store. Thank you.';
    
    			$this->sendsms($mobileNo, $message, $chapId);
    
    			$paymentResult = 'Success';
    			$paymentTransId = strtotime($paymentTimeStamp);
    
    			//Update the relevant Transaction record in the DB
    			parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl, $client->request, $client->response);
    
    			return $buildUrl;
    		}
    	}
    
    }
    
}
