<?php
/**
 * Created by PhpStorm.
 * User: viraj
 * Date: 9/9/14
 * Time: 1:52 PM
 */

class Nexva_MobileBilling_Type_TelekomSrbija extends Nexva_MobileBilling_Type_Abstract
{

    public function __construct()
    {
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
  
        
        /*test only -- remove this*/
        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        $sessionId = Zend_Session::getId();
        $amount = ceil($currencyRate * $price);
        $paymentTimeStamp = date('d-m-Y');
        
   
        /*get payment_reference*/
        $session = new Zend_Session_Namespace('payment_reference');
        /*end*/
      
        //$url = 'https://www.pagali.cv/pagali/index.php?r=pgPaymentInterface/ecommercePayment';
        $url = 'http://api.centili.com/payment/widget?apikey=4fcd691877068956f58205bb7ceb92f1&price='.$amount.'&country=rs&operator=RS_MTS&returnurl=http://store.mts.rs/app/buymts?reference='.$session->payment_reference.'&app_id='.$appId.'&build_id='.$buildId;

        
        return $url;
        
        /*end*/

      
       // return $buildUrl;

    }
    
    
    public function doPaymentApp($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
    
    
    	/*test only -- remove this*/
    	//Get currency rate and code relevant to the CHAP
    	$currencyUserModel = new Api_Model_CurrencyUsers();
    	$currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
    	$currencyRate = $currencyDetails['rate'];
    	$currencyCode = $currencyDetails['code'];
    	$sessionId = Zend_Session::getId();
    	$amount = ceil($currencyRate * $price);
    	$paymentTimeStamp = date('d-m-Y');

    
    	 
    	/*get payment_reference*/
    	$session = new Zend_Session_Namespace('payment_reference');
    	/*end*/

    	//$url = 'https://www.pagali.cv/pagali/index.php?r=pgPaymentInterface/ecommercePayment';
    	//http://api.centili.com/payment/widget?apikey=4fcd691877068956f58205bb7ceb92f1&price=56&country=rs&operator=RS_MTS
    	$url = 'http://api.centili.com/payment/widget?apikey=4fcd691877068956f58205bb7ceb92f1&price='.$amount.'&country=rs&operator=RS_MTS&returnurl=http://api.nexva.com/nexapi/buymts-responce/bb/bb?reference='.$session->payment_reference;
    	
    
    	return $url;
    
    	/*end*/
    
    
    	// return $buildUrl;
    
    }
   

    public function sendsms($mobileNo, $message, $chapId)
    {
    	 
    	ini_set('default_socket_timeout', 6000);
    
    	//$client = new nusoap_client('http://10.1.75.124:38080/ParlayXSmsAccess/services/SendSms', false);
    	
    	$client = new nusoap_client('http://10.1.75.15:38080/ParlayXSmsAccess/services/SendSms', false);
    	
    	
    	 
    	$timeStamp = date("Ymd").date("His");
    
    	$xmlMsgb2911 = '<?xml version="1.0" ?>
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
    	<ns2:addresses>tel:'.$mobileNo.'</ns2:addresses>
    	<ns2:senderName>MTS Store</ns2:senderName>
    	<ns2:message>'.$message.'</ns2:message>
    	</ns2:sendSms>
    	</SOAP-ENV:Body>
    	</SOAP-ENV:Envelope>';
    	
    	
    	$xmlMsgb = '<?xml version="1.0" ?>
    	<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    	<SOAP-ENV:Header>
    	<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
    	<wsse:UsernameToken wsu:Id="XWSSGID-11435375577461001212174" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
    	<wsse:Username>S-J8RO87C1841_S-J8RO87C1841-3_2@P-YJXDU0TgZo0</wsse:Username>
    	<wsse:Password >nexva.1</wsse:Password>
    	<wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">nexva.1</wsse:Nonce>
    	<wsu:Created xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">2006-03-28T09:19:18Z</wsu:Created>
    	</wsse:UsernameToken>
    	</wsse:Security>
    	</SOAP-ENV:Header>
    	<SOAP-ENV:Body>
    	<ns2:sendSms xmlns:ns2="http://www.csapi.org/schema/parlayx/sms/send/v2_1/local" xmlns:ns3="http://www.csapi.org/schema/parlayx/common/v2_1">
    	<ns2:addresses>tel:'.$mobileNo.'</ns2:addresses>
    	<ns2:senderName>S-J8RO87C1841_S-J8RO87C1841-3_2</ns2:senderName>
    	<ns2:message>'.$message.'</ns2:message>
    	</ns2:sendSms>
    	</SOAP-ENV:Body>
    	</SOAP-ENV:Envelope>';
    	 
    
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    	 
    	$timeStamp = date("Ymd").date("His");
    
    	 
    	$header = array('Security' => array('UsernameToken' => array ( 'Username' => 'S-74F400BU121_S-74F400BU121-2_2@P-3JXDU0TgZo0', 'Password' => 'infobip1.', 'Created' => $timeStamp )));
    	 
    	
    	$header = array('Security' => array('UsernameToken' => array ( 'Username' => 'S-J8RO87C1841_S-J8RO87C1841-3_2@P-YJXDU0TgZo0', 'Password' => 'nexva.1', 'Created' => $timeStamp )));
    	
    	
    	///	$result = $client->call('sendSms', $phone, '', '', $xmlMsg);
    	 
    	$result = $client->send($xmlMsgb, '', 0, 300);
    	 
    	 
    	echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
    	die();
    
    	if($client->fault) {
    		// there is a error. Payment is unsuccessful
    		return false;
    	} else {
    		$error = $client->getError();
    		if($error) {
    			return false;
    		} else {
    			return true;
    		}
    	}

    }
}