<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 6/16/14
 * Time: 5:37 PM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_MobileBilling_Type_AirtelNigeria extends Nexva_MobileBilling_Type_Abstract
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

        //$amount = ceil($currencyRate * $price);

        $client = new nusoap_client('https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        //mapping content Ids according to price points
        $contentId = null;

        //convert dollars to local currency
        $price = $this->getAirtelPricePoints(ceil($currencyRate * $price));

        switch($price){
            case 5:
                $contentId = 'N5';
                $amount = 5;
                break;
            case 10:
                $contentId = 'N10';
                $amount = 10;
                break;
            case 15:
                $contentId = 'N15';
                $amount = 15;
                break;
            case 20:
                $contentId = 'N20';
                $amount = 20;
                break;
            case 25:
                $contentId = 'N25';
                $amount = 25;
                break;
            case 50:
                $contentId = 'N50';
                $amount = 50;
                break;
            case 100:
                $contentId = 'N100';
                $amount = 100;
                break;
            case 150:
                $contentId = 'N150';
                $amount = 150;
                break;
            case 160:
                $contentId = 'N160';
                $amount = 160;
                break;
            case 200:
                $contentId = 'N200';
                $amount = 200;
                break;
            case 250:
                $contentId = 'N250';
                $amount = 250;
                break;
            default:
                break;
        }

        //echo $contentId,'-',$amount;die();

        if($contentId != null){
            $chargingXml = '<?xml version="1.0" encoding="UTF-8"?>
                        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
                        <soapenv:Header />
                            <soapenv:Body>
                                <char:charge>
                                    <inputMsg>
                                        <operation>debit</operation>
                                        <userId>'.$mobileNo.'</userId>
                                        <contentId>'.$contentId.'</contentId>
                                        <itemName>Content Purchase</itemName>
                                        <contentDescription>airtellive</contentDescription>
                                        <circleId></circleId>
                                        <lineOfBusiness></lineOfBusiness>
                                        <customerSegment></customerSegment>
                                        <contentMediaType>Mobile apps prices</contentMediaType>
                                        <serviceId></serviceId>
                                        <parentId></parentId>
                                        <actualPrice>'.$amount.'</actualPrice>
                                        <basePrice>3</basePrice>
                                        <discountApplied>1</discountApplied>
                                        <paymentMethod></paymentMethod>
                                        <revenuePercent></revenuePercent>
                                        <netShare></netShare>
                                        <cpId>NEXVA_NG</cpId>
                                        <customerClass></customerClass>
                                        <eventType>Content Purchase</eventType>
                                        <localTimeStamp></localTimeStamp>
                                        <transactionId></transactionId>
                                        <subscriptionTypeCode>abcd</subscriptionTypeCode>
                                        <subscriptionName>0</subscriptionName>
                                        <parentType></parentType>
                                        <deliveryChannel>SMS</deliveryChannel>
                                        <subscriptionExternalId>0</subscriptionExternalId>
                                        <contentSize></contentSize>
                                        <currency>NGN</currency>
                                        <copyrightId>mauj</copyrightId>
                                        <cpTransactionId>12345678</cpTransactionId>
                                        <copyrightDescription>copyright</copyrightDescription>
                                        <sMSkeyword>sms</sMSkeyword>
                                        <srcCode>54321</srcCode>
                                        <contentUrl>www.yahoo.com</contentUrl>
                                        <subscriptiondays>2</subscriptiondays>
                                    </inputMsg>
                                </char:charge>
                            </soapenv:Body>
                        </soapenv:Envelope>';
        } else {
            return null;
        }

        //$result = $client->send($chargingXml,'POST');
        $result = $client->send($chargingXml, 'https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1');
        

        $paymentTimeStamp = date('d-m-Y H:i:s');
        $buildUrl = null;

        if($result['outputMsg']['status'] == 'Success'){

            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            //$message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
            $message = 'Hello, your account was charged '. $price.' NGN at '.$paymentTimeStamp. ' for the successful transaction on the Airtel Nigeria App Store. Thank you.';
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

           
           
           $msg = '<?xml version="1.0" encoding="ISO-8859-15"?>
<message>
<sms type="mt">
<destination>
<address>
<number type="international">'.$mobileNo.'</number>
</address>
</destination>
<source>
<address>
<number type="international">Appstore</number>
</address>
</source>
<rsr type="all"/>
<ud type="text">'.$message.'</ud>
</sms>
</message>
           ';
           
        $url = "http://172.24.4.12:55000";
       
        
          $headers = array(
            "Content-type: text/xml",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Host: 196.46.244.58",
            "Pragma: no-cache",
            "User-Agent: ApplicationEmulator/1.0",
            "Content-length: ".strlen($msg)
        ); //SOAPAction: your op URL

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_TIMEOUT,        10000);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        // curl_setopt($ch, CURLOPT_CAINFO,  APPLICATION_PATH . '/configs/certrw.crt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
       curl_setopt($ch, CURLOPT_USERPWD, '2358160nx:GCW16$kn');
        curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
      
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
         //   echo 'cURL error (' . curl_errno($ch) . '): ' . curl_error($ch);
         //  Zend_Debug::dump(curl_error($ch), 'gggg');die();
        } else {
            curl_close($ch);
          ///  Zend_Debug::dump($data,'dd');die();
        }
        
        // Zend_Debug::dump($data,'dd');die();

      //  $doc = new DOMDocument();
        //$doc->loadXML($data);

      //  echo $doc->getElementsByTagName('status')->item(0)->nodeValue.'----';      
      //  echo $mobileNo.'##'.$message.'##'.$chapId;
      //  die();
        
        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            echo $doc->getElementsByTagName('status')->item(0)->nodeValue.'####';
             echo $mobileNo.'##'.$message.'##'.$chapId; die();
        }
        
        if($doc->getElementsByTagName('status')->item(0)->nodeValue == 'Success') {
            return true;
        } else {
            return false;
        }
        
        */
        
        return true;
    }

    public function getAirtelPricePoints($price) {

        $pricePoints = 0;
        $itemKey = 0;

        //Airtel price points array
        $array = array(5,10,15,20,25,50,100,150,160,200,250);

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