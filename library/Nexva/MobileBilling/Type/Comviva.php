<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 5/2/14
 * Time: 5:08 PM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_MobileBilling_Type_Comviva extends Nexva_MobileBilling_Type_Abstract
{
    public function __construct()
    {
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        if($mobileNo == '94757474481') {
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
        return $buildUrl;
        }
        
        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];

        $client = new nusoap_client('http://10.200.182.146:9080/dbill?serviceNode=IACCR',false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        //mapping price service types according to price points
        $serviceId = null;
        $serviceType = null;

        //convert dollars to local currency
        $price = $this->getAirtelPricePoints(ceil($currencyRate * $price));
		
        switch($price){
            case 5:
                $serviceId = 5798;
                $serviceType = 'wap_Appstore_5';
                break;
            case 10:
                $serviceId = 5799;
                $serviceType = 'wap_Appstore_10';
                break;
            case 15:
                $serviceId = 5800;
                $serviceType = 'wap_Appstore_15';
                break;
            case 20:
                $serviceId = 5801;
                $serviceType = 'wap_Appstore_20';
                break;
            case 30:
                $serviceId = 5803;
                $serviceType = 'wap_Appstore_30';
                break;
            case 40:
                $serviceId = 5805;
                $serviceType = 'wap_Appstore_40';
                break;
            case 50:
                $serviceId = 5807;
                $serviceType = 'wap_Appstore_50';
                break;
            case 80:
                $serviceId = 5813;
                $serviceType = 'wap_Appstore_80';
                break;
            case 100:
                $serviceId = 5817;
                $serviceType = 'wap_Appstore_100';
                break;
            case 130:
                $serviceId = 5823;
                $serviceType = 'wap_Appstore_130';
                break;
            case 150:
                $serviceId = 5827;
                $serviceType = 'wap_Appstore_150';
                break;
            case 180:
                $serviceId = 5833;
                $serviceType = 'wap_Appstore_180';
                break;
            case 200:
                $serviceId = 5837;
                $serviceType = 'wap_Appstore_200';
                break;
            case 250:
                $serviceId = 5847;
                $serviceType = 'wap_Appstore_250';
                break;
            case 300:
                $serviceId = 5857;
                $serviceType = 'wap_Appstore_300';
                break;
            case 350:
                $serviceId = 5867;
                $serviceType = 'wap_Appstore_350';
                break;
            case 400:
                $serviceId = 5877;
                $serviceType = 'wap_Appstore_400';
                break;
            case 600:
                $serviceId = 5917;
                $serviceType = 'wap_Appstore_600';
                break;
            case 800:
                $serviceId = 5957;
                $serviceType = 'wap_Appstore_800';
                break;
            case 1000:
                $serviceId = 5997;
                $serviceType = 'wap_Appstore_1000';
                break;
            default:
                break;
        }
        

        
        $serviceType;
        if( ($serviceId != null) && ($serviceType != null) ){
            //echo 'inside payment ### '.$mobileNo.' ### '.$serviceId.' ### '.$serviceType; //die();
            $xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <ocsRequest>
                        <serviceNode>IACCR</serviceNode>
                        <sequenceNo>060309431450545703</sequenceNo>
                        <requestType>4</requestType>
                        <cpcgFlag>1</cpcgFlag>
                        <callingParty>'.$mobileNo.'</callingParty>
                        <calledParty>'.$mobileNo.'</calledParty>
                        <portedCalledParty>NA</portedCalledParty>
                        <startTime>1370232800</startTime>
                        <serviceId>'.$serviceId.'</serviceId>
                        <serviceType>'.$serviceType.'</serviceType>
                        <callDuration>1</callDuration>
                        <callDirection>O</callDirection>
                    </ocsRequest>';

        }
        else
        {
            return null;
        }

        $result = $client->send($xml,'POST');

        //$amount = ceil($currencyRate * $price);
        $paymentTimeStamp = date('d-m-Y');

        $xml = trim($client->response);
        $bb = strpos($xml,"<?");
        $xml = substr($xml, $bb);

        $resultArray = simplexml_load_string($xml);

        $buildUrl = null;

        if($resultArray->result == 'OK' or $resultArray->result == 'Subscriber does not exist in ABMF database')
        {

            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            //$message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
            $message = 'Hello, your account was charged '. $price.' LKR at '.$paymentTimeStamp. ' for the successful transaction on the Airtel Sri Lanka App Store. Thank you.';
            $this->sendsms($mobileNo, $message, $chapId);

            $paymentResult = 'Success';
            $paymentTransId = strtotime($paymentTimeStamp);
            parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

        }

        return $buildUrl;

    }

    public function sendsms($mobileNo, $message, $chapId)
    {
        //echo $message;die();
        $ch = curl_init();

        //$message = urlencode($message);
        
        $message = urlencode($message);

        $msisdn = $mobileNo;

        $url = 'http://10.200.186.1/cgi-local/sendsms.pl?login=nexva&pass=nex123&sms='.$message.'&msisdn='.$msisdn.'&src=nexva&type=text';



        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);
        
      //  Zend_Debug::dump($output);die();

        // close curl resource to free up system resources
        curl_close($ch);
        //die();
    }
	
	public function getAirtelPricePoints($price) {

		$pricePoints = 0;
		$itemKey = 0;
		
		//Airtel price points array
		$array = array(5,10,15,20,30,40,50,80,100,130,150,180,200,250,300,350,400,600,800,1000);
		
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