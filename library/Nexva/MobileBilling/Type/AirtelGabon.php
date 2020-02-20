<?php
/**
 * Created by PhpStorm.
 * User: Rooban
 * Date: 9/9/14
 * Time: 1:52 PM
 */

class Nexva_MobileBilling_Type_AirtelGabon extends Nexva_MobileBilling_Type_Abstract
{

    public function __construct()
    {
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {

        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];

        $spEndPoint =  'https://AgUser:AgUser@154.0.177.3:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort';

        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        //App ID
        $contentId = $appId;

        //Get the correct price points from price points view helper 
        $objPricePOints = new Nexva_View_Helper_PricePoints();
        $price = $objPricePOints->PricePoints(ceil($currencyRate * $price), $chapId);

        $paymentTimeStamp = date('Y-m-d H:i:s');
        $paymentTransId = strtotime("now");
        $transactionId = $paymentTransId;
        
        $msg = '<?xml version="1.0" encoding="UTF-8"?>
    	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
    	<soapenv:Header/>
    	<soapenv:Body>
    	<char:charge>
    	<inputMsg>
    	<operation>debit</operation>
    	<userId>'.$mobileNo.'</userId>
    	<contentId>'.$price.'</contentId>
    	<itemName>SRKPic</itemName>
    	<contentDescription>Content Purchase</contentDescription>
    	<circleId/>
    	<lineOfBusiness/>
    	<customerSegment/>
    	<contentMediaType>Apps</contentMediaType>
    	<serviceId></serviceId>
    	<parentId/>
    	<actualPrice>'.$price.'</actualPrice>
    	<basePrice>0</basePrice>
    	<discountApplied>0</discountApplied>
    	<paymentMethod/>
    	<revenuePercent/>
    	<netShare>0</netShare>
    	<cpId>AIRTELAPPSSTORE</cpId>
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
    	<currency>CFA</currency>
    	<copyrightId>neXva</copyrightId>
    	<cpTransactionId>'.$transactionId.'</cpTransactionId>
    	<copyrightDescription>copyright</copyrightDescription>
    	<sMSkeyword>sms</sMSkeyword>
    	<srcCode>abcd</srcCode>
    	<contentUrl>http://apps.ga.airtellive.com/'.$appId.'</contentUrl>
    	<subscriptiondays>1</subscriptiondays>
    	</inputMsg>
    	</char:charge>
    	</soapenv:Body>
    	</soapenv:Envelope>';

        $result = $client->send($msg, 'https://AgUser:AgUser@154.0.177.3:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort');
        
        /*Zend_Debug::dump($result);
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
        echo $result['outputMsg']['status'];
    	die();*/

        $buildUrl = null;

        if($result['outputMsg']['status'] == 'Success'){
            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
            $message = "Vous avez ete facture a '. $price.'f le '.$paymentTimeStamp. ' pour l'achat d'une application sur Airtel Gabon AppStore.";
            $this->sendsms($mobileNo, $message, $chapId);
            $paymentResult = 'Success';
           
            //Update the relevant Transaction record in the DB
            parent::UpdateMobilePayment($paymentTimeStamp, $transactionId, $paymentResult, $buildUrl);
        }
        else{
            //$paymentResult = 'Fail';
            $paymentResult = (isset($result['outputMsg']['error'])) ? $result['outputMsg']['error']['errorCode'] : 'Fail';
            parent::UpdateMobilePayment($paymentTimeStamp, $transactionId, $paymentResult, $buildUrl);
        }

        return $buildUrl;
    }

    public function sendsms($mobileNo, $message, $chapId = null)
    {
    	include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
    	//$from = $this->getRequest()->getParam('from');
    	//$to = $this->getRequest()->getParam('to');
    	//$smppHost = '192.168.1.61';
    	//$smppPort = '16920';
    	
    	$smppHost = '192.168.1.39';
    	$smppPort = '5001';
    	
    	
    	$systemId = 'NEXVA';
    	$password = 'NEXVA';
    	$systemType = 'smpp';
    	$from = 'AirtelAPPS';

    	$smpp = new SMPPClass();
    	$smpp->SetSender($from);
        $smpp->_debug = false;
    	/* bind to smpp server */
    	$smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
    	/* send enquire link PDU to smpp server */
    	//$smpp->TestLink();
    	/* send single message; large messages are automatically split */
    	$messageStatus = $smpp->Send('+'.$mobileNo, $message, true);
        
    	//Zend_Debug::dump($messageStatus); die();
        //return $messageStatus;
        
    	$smpp->End();
    }
}