<?php

/**
 * This class is used to manage MTN
 */
class Nexva_MobileBilling_Type_Huawei extends Nexva_MobileBilling_Type_Abstract {

    public function __construct() {

        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );

        $config = Zend_Registry::get('config');

        // stage 
        //$spId = 2340110000211;
        //$spPass = 'nexT123';
        //$spServiceId = '';
        //$oa = 2347060553995;    	
        //$spEndPoint = 'http://41.206.4.219:8310/AmountChargingService/services/AmountCharging';
        
        // live
        $spEndPoint = 'http://41.206.4.162:8310/AmountChargingService/services/AmountCharging';

        $this->_client = new nusoap_client($spEndPoint);
        $this->_client->soap_defencoding = 'UTF-8';
        $this->_client->decode_utf8 = false;
   
       // $this->_header = array('RequestSOAPHeader' => array('spId' => $spId, 'spPassword' => $password, 'serviceId' => $spServiceId, 'timeStamp' => $timeStamp, 'OA' => $oa, 'FA' => $fa));

       
    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price) {
        
        $spId = 2340110000426;
        $spPass = 'nexT321';
        //$spServiceId = '234012000001923';
        $spServiceId = '234012000001927';
      //  $oa = 2347060553995;
      //  $fa = 2347060553995;
      
        $oa = $mobileNo;
        $fa = $mobileNo;
        
        $timeStamp = date("Ymd") . date("His");
        $password = md5($spId . $spPass . $timeStamp);
        
        $this->_header = array('RequestSOAPHeader' => array('spId' => $spId, 'spPassword' => $password, 'serviceId' => $spServiceId, 'timeStamp' => $timeStamp, 'OA' => $oa, 'FA' => $fa));
        
        //Charging code, which is relevant to the contract of the charged party.  [ Static code ]   = code 
        $error = '';

        $currency = new Api_Model_Currencies();
        $currencyRate = $currency->getCurrencyRate('NGN');

        $amountINngn = $currencyRate->rate * $price;

        $amount = ceil($currencyRate->rate * $price);


        $charge = array(
            'description' => $appId,
            'currency' => 'NGN',
            'amount' => $amount * 100,
            'code' => 10090
        );

        // 'amount' => ceil($amount),

        $timeStamp = date("Ymd") . date("His");


        // referenceCode - Unique ID of the request.  can take as transaction id 
        $paymentInfo = array(
            'endUserIdentifier' => $mobileNo,
            'charge' => $charge,
            'referenceCode' => $appId . '-' . $timeStamp
        );

        $result = $this->_client->call('chargeAmount', $paymentInfo, '', '', $this->_header);

        //Zend_Debug::dump( $result);
        // trun on this for debuging
        //echo '<pre>' . htmlspecialchars($this->_client->request, ENT_QUOTES) . '</pre>';
         //echo '<pre>' . htmlspecialchars($this->_client->response, ENT_QUOTES) . '</pre>';
        //die();

        $buildUrl = '';

        if ($this->_client->fault) {
            
            // there is a error. Payment is unsuccessful
        } else {
           
            $error = $this->_client->getError();
            if ($error) {

                // there is a error. Payment is unsuccessful
                $this->_client->getDebug();
            } else {
                //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

                $paymentTimeStamp = date('YmdHis');
                $paymentTransId = 'MTN ' . $appId . '-' . $timeStamp;
                $paymentResult = 'Success';

                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);


                $client1 = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
                $client1->soap_defencoding = 'UTF-8';
                $client1->decode_utf8 = false;

                $timeStamp = date("Ymd") . date("His");

                $spId = 2340110000426;
                $pass = 'nexT321';

                $password = md5($spId . $pass . $timeStamp);

                // commented out by chathura
                //$header = array('RequestSOAPHeader' => array('spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929', 'timeStamp' => $timeStamp, 'OA' => '2347060553995', 'FA' => '2347060553995'));

                $header = array('RequestSOAPHeader' => array('spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929', 'timeStamp' => $timeStamp, 'OA' => $mobileNo, 'FA' => $mobileNo));
                
                $phone = array(
                    'addresses' => 'tel:' . $mobileNo,
                    'senderName' => 99999998,
                    'message' => 'MTN SMS Alert! The amount of the transaction of ' . $amount . ' NGN was done on ' . $paymentTransId . ' MTN Nigeria APP STORE was deducted from your account.'
                );

                $result = $client1->call('sendSms', $phone, '', '', $header);
            }
        }


        return $buildUrl;
    }

}
