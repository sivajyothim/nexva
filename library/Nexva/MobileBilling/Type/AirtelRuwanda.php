<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 4/11/14
 * Time: 2:06 PM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_MobileBilling_Type_AirtelRuwanda extends Nexva_MobileBilling_Type_Abstract
{
    public function __construct()
    {
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        //echo $chapId,' - ',$buildId,' - ',$appId,' - ',$mobileNo,' - ',$appName,' - ',$price;die();

        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];

        //$amount = ceil($currencyRate * $price);

        $spEndPoint =  'https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort';

        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $price = $this->getPricePoints(ceil($currencyRate * $price));

        /*$msg = '<?xml version="1.0" encoding="UTF-8"?>
	    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
   <soapenv:Header/>
   <soapenv:Body>
      <char:charge>
         <inputMsg>
            <operation>debit</operation>
            <userId>'.$mobileNo.'</userId>
            <contentId>111</contentId>
            <itemName>SRKPic</itemName>
            <contentDescription>Apps</contentDescription>
            <circleId/>
            <lineOfBusiness/>
            <customerSegment/>
            <contentMediaType>Apps</contentMediaType>
            <serviceId>1</serviceId>
            <parentId/>
            <actualPrice>'.$amount.'</actualPrice>
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
</soapenv:Envelope>';*/

        //$result=$client->send($msg, 'https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort');

        $soapUrl = "https://197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort"; // asmx URL of WSDL
        $soapUser = "AirtelAppStore_RW";  //  username
        $soapPassword = "AirtelAppStore_RW!ibm123"; // password

        $xml_post_string = '<?xml version="1.0" encoding="UTF-8"?>
	    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
   <soapenv:Header/>
   <soapenv:Body>
      <char:charge>
         <inputMsg>
            <operation>debit</operation>
            <userId>'.$mobileNo.'</userId>
            <contentId>111</contentId>
            <itemName>SRKPic</itemName>
            <contentDescription>Apps</contentDescription>
            <circleId/>
            <lineOfBusiness/>
            <customerSegment/>
            <contentMediaType>Apps</contentMediaType>
            <serviceId>1</serviceId>
            <parentId/>
            <actualPrice>'.$price.'</actualPrice>
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

        $doc = new DOMDocument();
        $doc->loadXML($result);

        $paymentTimeStamp = date('d-m-Y');

        $buildUrl = null;

        //echo $doc->getElementsByTagName('status')->item(0)->nodeValue;die();

        if('Success' == $doc->getElementsByTagName('status')->item(0)->nodeValue)
        {
            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            //todo, change message language
            //$message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
            $message = 'Hello, your account was charged '. $price.' RWF at '.$paymentTimeStamp. ' for the successful transaction on the Airtel Rwanda App Store. Thank you.';
            $this->sendsms($mobileNo, $message, $chapId);

            $paymentResult = 'Success';
            $paymentTransId = strtotime($paymentTimeStamp);
            //Update the relevant Transaction record in the DB
            parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
        }

        curl_close($ch);

        return $buildUrl;



        /*if(!$client->fault)
        {
            $error = $client->getError();

            if(!$error)
            {
                //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

                //todo, change message language
                //$message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
                $message = '';
                $this->sendsms($mobileNo, $message, $chapId);

                $paymentResult = 'Success';
                $paymentTransId = strtotime($paymentTimeStamp);
                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

                return $buildUrl;
            }
        }*/
    }

    public function sendsms($mobileNo, $message, $chapId)
    {
        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );

        $smppHost = '197.157.129.20';
        $smppPort = '31120';
        $systemId = 'neXvarw';
        $password = 'neXvarw';
        $systemType = 'smpp';
        $from = 'AppStoreRW';


        //echo '<pre>';
        $smpp = new SMPPClass();
        $smpp->SetSender($from);
        $smpp->_debug = false;
        /* bind to smpp server */
        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
        /* send enquire link PDU to smpp server */
        $smpp->TestLink();
        /* send single message; large messages are automatically split */
        //$messageStatus = $smpp->Send('250731000057', 'neXva neXva neXva neXva neXva neXva ');
        $messageStatus = $smpp->Send($mobileNo, $message);

        //Zend_Debug::dump($messageStatus,'ddd');die();

        $smpp->End();
        //echo '</pre>';
        //die();
        /* send unicode message */
        ///$smpp->Send("731000057", "731000057", true);
        /* send message to multiple recipients at once */
        //$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
        /* unbind from smpp server */
    }

    public function getPricePoints($price) {

        $pricePoints = 0;
        $itemKey = 0;

        //Airtel price points array
        $array = array(70,140,315,343,413,623,693,700,763,770,784,854,875,910,952,980,1043,1050,1120,1183,1190,1253,
                       1330,1344,1365,1393,1400,1596,1631,1743,1750,1757,1813,1890,1953,2030,2037,2093,2100,2121,2240,
                       2380,2450,2513,2730,2793,2800,3136,3150,3360,3465,3493,3500,3843,4165,4193,4200,4543,4550,4865,
                       4893,4900,5243,5523,5593,5600,6300,6860,6930,6965,6993 );

        $minPricePoint = min($array);
        $maxPricePoint = max($array);

        if ($price >= $maxPricePoint) {
            $pricePoints = $maxPricePoint;
        }
        elseif($price <= $minPricePoint){
            $pricePoints = $minPricePoint;
        }
        else {
            foreach ($array as $key => $value) {

                if ($minPricePoint >= $price) {
                    break;
                }

                $minPricePoint = $value;
                $itemKey = $key;
            }

            $pricePoints = $array[$itemKey];
        }

        return $pricePoints;
    }
}