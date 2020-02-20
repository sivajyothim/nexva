<?php
/**
 * Created by PhpStorm.
 * User: viraj
 * Date: 9/30/14
 * Time: 12:53 PM
 */

class Nexva_MobileBilling_Type_AirtelMalawi extends Nexva_MobileBilling_Type_Abstract
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

        $spEndPoint =  'https://NEXVA_MW:NEXVA_MW!ibm123@41.223.58.133:8443/ChargingServiceFlowWeb/sca/ChargingExport1';

        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        //convert dollars to local currency
        $price = $this->getPricePoints(ceil($currencyRate * $price));

        $msg = '<?xml version="1.0" encoding="UTF-8"?>
    	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
    	<soapenv:Header/>
    	<soapenv:Body>
    	<char:charge>
    	<inputMsg>
    	<operation>debit</operation>
    	<userId>'.$mobileNo.'</userId>
    	<contentId>1</contentId>
    	<itemName>test</itemName>
    	<contentDescription>Content Purchase</contentDescription>
    	<circleId/>
    	<lineOfBusiness/>
    	<customerSegment/>
    	<contentMediaType>Apps'.$price.'</contentMediaType>
    	<serviceId></serviceId>
    	<parentId/>
    	<actualPrice>'.$price.'</actualPrice>
    	<basePrice>0</basePrice>
    	<discountApplied>0</discountApplied>
    	<paymentMethod/>
    	<revenuePercent/>
    	<netShare>0</netShare>
    	<cpId>NEXVA_MW</cpId>
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
    	<currency>MWK</currency>
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

        $result=$client->send($msg, 'https://NEXVA_MW:NEXVA_MW!ibm123@41.223.58.133:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort');

        $paymentTimeStamp = date('d-m-Y');

        $buildUrl = null;

        if('Success' == $result['outputMsg']['status'])
        {
            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            //$message = "Vous avez ete facture a '. $price.'f le '.$paymentTimeStamp. ' pour l'achat d'une application sur Airtel AppStore.";
            $message = 'Hello, your account was charged '. $price.' MWK at '.$paymentTimeStamp. ' for the successful transaction on the Airtel Malawi App Store. Thank you.';
            $this->sendsms($mobileNo, $message, $chapId);

            $paymentResult = 'Success';
            $paymentTransId = strtotime($paymentTimeStamp);

            //Update the relevant Transaction record in the DB
            parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
        }

        return $buildUrl;
    }

    public function sendsms($mobileNo, $message, $chapId)
    {
        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
        
        $message = trim($message);

        $smppHost = '41.223.58.132';
        $smppPort = '31110';
        $systemId = 'nex7v';
        $password = 'nex7v';
        $systemType = 'smpp';
        $from = 'AppStore';

        //echo '<pre>';
        $smpp = new SMPPClass();
        $smpp->debugmod(false);
        $smpp->SetSender($from);
        $smpp->_debug = false;
        /* bind to smpp server */
        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
        /* send enquire link PDU to smpp server */
        $smpp->TestLink();
        /* send single message; large messages are automatically split */
        //$messageStatus = $smpp->Send('265997201172', 'test test from neXva', true);
        $messageStatus = $smpp->Send($mobileNo, $message);

        //Zend_Debug::dump($messageStatus,'ddd');


        $smpp->End();
        //echo '</pre>';
        //die();
        return true;

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
        $array = array(100,200,300,400,500,600,700,800,900,1000,1100,1200,1300,1400,1500,1600,1700,1800,1900,2000,
            2100,2200,2300,2400,2500,2600,2700,2800,2900,3000,3100,3200,3300,3400,3500,3600,3700,3800,3900,4000);

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