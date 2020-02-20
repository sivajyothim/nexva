<?php
/**
 * Created by PhpStorm.
 * User: viraj
 * Date: 9/9/14
 * Time: 1:52 PM
 */

class Nexva_MobileBilling_Type_AirtelNiger extends Nexva_MobileBilling_Type_Abstract
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

        $spEndPoint =  'https://NEXVA_NE:NEXVA_NE!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1';

        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        //mapping content Ids according to price points
        $contentId = null;

        //convert dollars to local currency
        $price = $this->getPricePoints(ceil($currencyRate * $price));

        //this is for testing purposes
        /*if('555555555555' == $mobileNo){

            $paymentTimeStamp = date('d-m-Y');

            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            $message = 'Hello, your account was charged '. $price.' XOF at '.$paymentTimeStamp. ' for the successful transaction on the Airtel Niger App Store. Thank you.';
            $this->sendsms($mobileNo, $message, $chapId);

            $paymentResult = 'Success';
            $paymentTransId = strtotime($paymentTimeStamp);
            //Update the relevant Transaction record in the DB
            parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

            return $buildUrl;
        }*/

        //$price = 200;

        $msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
                <soapenv:Header/>
                <soapenv:Body>
                <char:charge>
                <inputMsg>
                <operation>debit</operation>
                <userId>'.$mobileNo.'</userId>
                <contentId>'.$price.'</contentId>
                <itemName>Content Purchase</itemName>
                <contentDescription>price -'.$price.'</contentDescription>
                <circleId></circleId>
                <lineOfBusiness></lineOfBusiness>
                <customerSegment></customerSegment>
                <contentMediaType>AppStore'.$price.'</contentMediaType>
                <serviceId></serviceId>
                <parentId></parentId>
                <actualPrice>'.$price.'</actualPrice>
                <basePrice>0</basePrice>
                <discountApplied>0</discountApplied>
                <paymentMethod></paymentMethod>
                <revenuePercent></revenuePercent>
                <netShare></netShare>
                <cpId>NEXVA_NE</cpId>
                <customerClass></customerClass>
                <eventType>Content Purchase</eventType>
                <localTimeStamp></localTimeStamp>
                <transactionId></transactionId>
                <subscriptionTypeCode>1</subscriptionTypeCode>
                <subscriptionName>TEST</subscriptionName>
                <parentType></parentType>
                <deliveryChannel>sms</deliveryChannel>
                <subscriptionExternalId>AIRTEL</subscriptionExternalId>
                <contentSize></contentSize>
                <currency>XOF</currency>
                <copyrightId>1234</copyrightId>
                <sMSkeyword>123</sMSkeyword>
                <srcCode>121</srcCode>
                <contentUrl>http://www.airtel.in</contentUrl>
                <subscriptiondays>1</subscriptiondays>
                <!--Optional:-->
                <cpTransactionId></cpTransactionId>
                <copyrightDescription></copyrightDescription>
                </inputMsg>
                </char:charge>
                </soapenv:Body>
                </soapenv:Envelope>';

        $result = $client->send($msg, 'https://NEXVA_NE:NEXVA_NE!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1');
        //Zend_Debug::dump($result);die();
        $paymentTimeStamp = date('d-m-Y');

        $buildUrl = null;

        if('Success' == $result['outputMsg']['status'])
        {

            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            $message = "Vous avez ete facture a '. $price.'f le '.$paymentTimeStamp. ' pour l'achat d'une application sur Airtel AppStore.";
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


        
        $smppHost = '196.46.244.58';
        $smppPort = '31110';
        $systemId = 'ninexva';
        $password = 'ninexva';
        $systemType = 'smpp';
        $from = 'AppStore-NE';
        

        $smpp = new SMPPClass();
        $smpp->SetSender($from);
        $smpp->_debug = false;
        /* bind to smpp server */
        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
        /* send enquire link PDU to smpp server */
        //$smpp->TestLink();
        /* send single message; large messages are automatically split */
        //$messageStatus = $smpp->Send('250731000057', 'neXva neXva neXva neXva neXva neXva ');
        $messageStatus = $smpp->Send($mobileNo, $message);

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
        $array = array(200,400,600,800,1000,1200,1400,1600,1800,2000,2200,2400,2600,2800,3000,3200,3400,3600,3800,4000,4200,4400,
                        4600,4800,5000,5200,5400,5600,5800,6000,6200,6400,6600,6800,7000,7200,7400,7600,7800,8000,8200,
                        8400,8600,8800,9000,9200,9400,9600,9800,10000);

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