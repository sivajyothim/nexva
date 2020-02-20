<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/16/14
 * Time: 5:37 PM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_MobileBilling_Type_AirtelDrc extends Nexva_MobileBilling_Type_Abstract
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


        $price = $this->getAirtelPricePoints(ceil($currencyRate * $price));


        $soapUrl = 'https://41.222.198.77:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort';

        $msg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:char="
        http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
        <soapenv:Header/>
        <soapenv:Body>
        <char:charge>
        <inputMsg>
        <operation>debit</operation>
        <userId>'.$mobileNo.'</userId>
        <contentId>111</contentId>
        <itemName>SRKP</itemName>
        <contentDescription>Apps</contentDescription>
        <circleId/>
        <lineOfBusiness/>
        <customerSegment/>
        <contentMediaType>AppsPurchases'.$price.'0</contentMediaType>
        <serviceId>1</serviceId>
        <parentId/>
        <actualPrice>'.$price.'.0</actualPrice>
        <basePrice>'.$price.'.0</basePrice>
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
        <currency>UNI</currency>
        <copyrightId>xxx</copyrightId>
        <cpTransactionId>rhKJJH</cpTransactionId>
        <copyrightDescription>copyright</copyrightDescription>
        <sMSkeyword>sms</sMSkeyword>
        <srcCode>abcd</srcCode>
        <contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl> (
        		http://www.ibm.com%29%3C/contentUrl%3E)
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
            "SOAPAction: https://AgUser:AgUser@41.222.198.77:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort",
            "Content-length: ".strlen($msg),
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
        curl_setopt($ch, CURLOPT_USERPWD, 'AgUser:AgUser');
        curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);

        $result = curl_exec($ch);
       

        $paymentTimeStamp = date('d-m-Y H:i:s');
        $buildUrl = null;

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

            //$message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
            $message = 'Hello, your account was charged '. $price.'u at '.$paymentTimeStamp. ' for the successful transaction on the Airtel DRC App Store. Thank you.';
            $this->sendsms($mobileNo, $message, $chapId);

            $paymentResult = 'Success';
            $paymentTransId = strtotime($paymentTimeStamp);

            //parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
            if($this->_paymentId){
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
            }

        } else {
            $paymentResult = 'Fail';
            $paymentTransId = strtotime($paymentTimeStamp);
            //parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

            if($this->_paymentId){
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
            }
        }

        return $buildUrl;
    }

    public function sendsms($mobileNo, $message, $chapId)
    {

    	include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
    
    	$smppHost = '41.222.198.73';
    	$smppPort = '31110';
    	$systemId = 'appstore';
    	$password = 'app1234';
    	$systemType = 'SMPP';
    	$from = 'AppStore';
    
    
    	$smpp = new SMPPClass();
    	$smpp->SetSender($from);
    	$smpp->_debug = false;
    	/* bind to smpp server */
    	$smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
    	/* send enquire link PDU to smpp server */
    	//$smpp->TestLink();
    	/* send single message; large messages are automatically split + 243999967533 */
    	$messageStatus = $smpp->Send($mobileNo, $message, true);
    
    	$smpp->End();

        return true;

    }

    public function getAirtelPricePoints($price) {

        $pricePoints = 0;
        $itemKey = 0;

        //Airtel price points array
        $array = array(50, 75, 100, 125, 150, 175, 200, 225, 250, 275, 300, 325, 350, 375, 
                       400, 425, 450, 475, 500, 525, 550, 575, 600, 625, 650, 675, 700, 725, 
                       750, 775, 800, 825, 850, 875, 900, 925, 950, 975, 1000, 1025, 1050, 1075, 
                       1100, 1125, 1150, 1175, 1200, 1225, 1250, 1275, 1300, 1325, 1350, 1375, 1400, 
                       1425, 1450, 1475, 1500, 1525, 1550, 1575, 1600
                );

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