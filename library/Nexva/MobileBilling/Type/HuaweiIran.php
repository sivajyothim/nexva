<?php

/**
 * This class is used to manage Interop payments
 *  
 * Maheel
 */
class Nexva_MobileBilling_Type_HuaweiIran extends Nexva_MobileBilling_Type_Abstract {

    public function __construct() {
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price) {
        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);

        $clientGetMsisdn = new nusoap_client('http://92.42.51.113:7001/MTNIranCell_Proxy', false);

        $clientGetMsisdn->soap_defencoding = 'UTF-8';
        $clientGetMsisdn->decode_utf8 = false;

        $objDateTime = new DateTime('NOW');
        $dateTime = $objDateTime->format('c');
        $responseArr = array();

        $msg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:open="http://www.openuri.org/" xmlns:env="http://eai.mtnn.iran/Envelope">
   <soapenv:Header>
      <pr:authentication soapenv:actor="http://schemas.xmlsoap.org/soap/actor/next" soapenv:mustUnderstand="0" xmlns:pr="http://webservices.irancell.com/ProxyService">
         <pr:user>mw_bks</pr:user>
         <pr:password>MW_bks032Mtn</pr:password>
      </pr:authentication>
   </soapenv:Header>
   <soapenv:Body>
      <ns:clientRequest xmlns:ns="http://www.openuri.org/">
         <EaiEnvelope xmlns="http://eai.mtnn.iran/Envelope" xmlns:cus="http://eai.mtn.iran/CustomerProfile">
            <Domain>abl_Portal</Domain>
            <Service>CustomerProfile</Service>
            <Sender>abl_bks</Sender>
            <MessageId>2102011B111621</MessageId>
            <Language>En</Language>
            <UserId>'.$mobileNo.'</UserId>
            <SentTimeStamp>'.$dateTime.'</SentTimeStamp>
            <Payload>
               <cus:CustomerProfile>
                  <cus:Request>
                     <cus:Operation_Name>GetMSISDNInfo</cus:Operation_Name>
                     <cus:CustDetails_InputData>
                        <cus:MSISDN>'.$mobileNo.'</cus:MSISDN>
                     </cus:CustDetails_InputData>
                  </cus:Request>
               </cus:CustomerProfile>
            </Payload>
         </EaiEnvelope>
      </ns:clientRequest>
   </soapenv:Body>
</soapenv:Envelope>
';

        $resultMsisdn = $clientGetMsisdn->send($msg, 'http://92.42.51.113:7001/MTNIranCell_Proxy');

        $headerResponse = Zend_Http_Response::fromString($clientGetMsisdn->response);
        $bodyGetMsisdn = $headerResponse->getRawBody();
        $doc = new DOMDocument();
        $doc->loadXML($bodyGetMsisdn);
        $customerType = $doc->getElementsByTagName('Customer_Type')->item(0)->nodeValue;
        $resultCode = $doc->getElementsByTagName('resultCode')->item(0)->nodeValue;

        /*if($_SERVER['REMOTE_ADDR'] == '119.235.2.157'){
            
            Zend_Debug::dump($resultMsisdn); 
            echo '<h2>Request</h2><pre>' . htmlspecialchars($clientGetMsisdn->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($clientGetMsisdn->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($clientGetMsisdn->debug_str, ENT_QUOTES) . '</pre>';
            
            echo $customerType.'##'.$resultCode; die();
        }*/
            
        if ($customerType == 'P' && $resultCode == 0) {
            //$client = new nusoap_client('http://92.42.55.91:8310/AmountChargingService/services/AmountCharging', false);
            $client = new nusoap_client('http://92.42.55.109:8310/AmountChargingService/services/AmountCharging', false);
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = false;

            $timeStamp = date("Ymd") . date("His");
            //$spId = '005407';
            $spId = '001808';
            //$serviceId = '0054072000001784';
            $serviceId = '0018082000002380';
            $pass = 'e6434ef249df55c7a21a0b45758a39bb';
            $spPass = md5($spId . $pass . $timeStamp);

            $currencyUserModel = new Api_Model_CurrencyUsers();
            $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
            $currencyRate = $currencyDetails['rate'];
            $currencyCode = $currencyDetails['code'];
            $amount = ceil($currencyRate * $price);

            /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
                $amount = 5;
            }*/
            
            $paymentTimeStamp = date('Y-m-d H:i:s');
            $paymentTransId = strtotime("now");
            //$this->_paymentId

            $msg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" xmlns:loc="http://www.csapi.org/schema/parlayx/payment/amount_charging/v2_1/local"> 
           <soapenv:Header> 
              <v2:RequestSOAPHeader> 
                 <v2:spId>' . $spId . '</v2:spId> 
                 <v2:spPassword></v2:spPassword> 
                 <v2:serviceId>' . $serviceId . '</v2:serviceId> 
                 <v2:timeStamp></v2:timeStamp> 
                 <v2:OA>' . $mobileNo . '</v2:OA>  
                 <v2:FA>' . $mobileNo . '</v2:FA> 
                 <v2:token/> 
                 <v2:namedParameters>
                    <v2:item>
                       <v2:key>contentId</v2:key>
                       <v2:value>' . $appId . '</v2:value>
                    </v2:item>
                    <v2:item>
                       <v2:key>country</v2:key>
                       <v2:value>' . $paymentTransId . '</v2:value>
                    </v2:item>
                 </v2:namedParameters>
              </v2:RequestSOAPHeader> 
           </soapenv:Header> 
           <soapenv:Body> 
              <loc:chargeAmount> 
                 <loc:endUserIdentifier>tel:' . $mobileNo . '</loc:endUserIdentifier> 
                 <loc:charge> 
                    <description>charging information</description> 
                    <currency>IRR</currency> 
                    <amount>' . $amount . '</amount> 
                    <code>10080</code> 
                 </loc:charge> 
                 <loc:referenceCode>' . $timeStamp . '</loc:referenceCode> 
              </loc:chargeAmount> 
           </soapenv:Body> 
        </soapenv:Envelope>
        ';

            $result = $client->send($msg, '');
          /*  if($_SERVER['REMOTE_ADDR'] == '119.235.2.157'){
             Zend_Debug::dump($result);
              echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
              echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
             echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
              echo $this->_paymentId;
              die();
              } */
            $buildUrl = '';

            $headerResponse = Zend_Http_Response::fromString($client->response);
            $headerStatus = $headerResponse->getStatus();


            //If the header status is 200 and not containing any body in response it's a success payment
            if ($headerStatus == 200) {
                //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

                //SMS gateway is not integrated since the new API not provided
                //$message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
                //$this->sendsms($mobileNo, $message, $chapId);

                $paymentResult = 'Success';

                $responseArr['build_url'] = $buildUrl;
                $responseArr['message'] = 'Success';
                $responseArr['trans_id'] = $paymentTransId;
                $responseArr['fault_code'] = '0';

                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
            } else {
                $paymentResult = 'Fail';

                $faultString = $result['faultstring'];

                $responseArr['build_url'] = null;
                $responseArr['message'] = $faultString;
                $responseArr['transaction_id'] = $paymentTransId;
                $responseArr['fault_code'] = '-1';

                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
            }
            /* if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){  
              echo $headerStatus.'###';
              print_r($responseArr);
              Zend_Debug::dump($result);
              echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
              echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
              echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
              die();
              } */

            /* if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){  
              print_r($responseArr); die();
              } */

            return $responseArr;
        } else {
            
            //$paymentResult = 'Premium app download restricted to post paid users';
            
            $paymentResult = 'مشترکین دائمی فعلا امکان خرید ندارند';
            $paymentResultStatus = 'Postpaid';
            
            $paymentTimeStamp = date('Y-m-d H:i:s');
            $paymentTransId = strtotime("now");

            $responseArr['build_url'] = null;
            $responseArr['message'] = $paymentResult;
            $responseArr['transaction_id'] = $paymentTransId;
            $responseArr['fault_code'] = '-15';
            $buildUrl = '';
            
            //Update the relevant Transaction record in the DB
            parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResultStatus, $buildUrl);
                
            return $responseArr;
        }
    }

    public function sendsms($mobileNo, $message, $chapId) {
        //todo, get chap SMS gateway details dynamically242068661314
        $client = new nusoap_client('http://92.42.55.109:8310/SendSmsService/services/SendSms');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd") . date("His");
        $spId = '001617';
        $spServiceId = '0016172000001407';
        $pass = '0f596813534b39c9429d6ba598f80b6d';

        $header = array(
            'RequestSOAPHeader' => array(
                'spId' => $spId,
                'spPassword' => '0f596813534b39c9429d6ba598f80b6d',
                'serviceId' => $spServiceId,
                'timeStamp' => $timeStamp,
                'OA' => $mobileNo,
                'FA' => $mobileNo
            )
        );

        $phone = array(
            'addresses' => 'tel:' . $mobileNo,
            'senderName' => 737920,
            'message' => $message,
            'receiptRequest' => array(
                'endpoint' => 'http://88.190.51.72/recv-irancell.php',
                'interfaceName' => 'SmsNotification',
                'correlator' => '1232',
            )
        );

        $result = $client->call('sendSms', $phone, '', '', $header);

        // trun on this for debuging

        /* if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
          Zend_Debug::dump( $result);
          } */

//       Zend_Debug::dump( $result);
//      echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
//     echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
//       die();
    }

}