<?php
/**
 * This class is used to manage MTN
 */
class Nexva_MobileBilling_Type_HuaweiciAirtime extends Nexva_MobileBilling_Type_Abstract 
{
    
    public function __construct() {
        
      
    }
    
    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        //echo $chapId,'-',$buildId,'-',$appId,'-',$mobileNo,'-',$appName,'-',$price;die();
        
        ini_set('default_socket_timeout', 300);
        
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );

        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        

        $amount = ceil($currencyRate * $price);

        $client = new nusoap_client('http://196.201.33.98:8310/AmountChargingService/services/AmountCharging', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $spId =  '2250110001369';
        $spPass = 'Huawei2014';
        $timeStamp = date("Ymd").date("His");
        $timeStamp = '20150416090000';
        
        $password = strtoupper(MD5($spId.$spPass.$timeStamp));
        $password = '74761FCA5C2401A58225FC235371C0E4';	
        

        $xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" xmlns:loc="http://www.csapi.org/schema/parlayx/payment/amount_charging/v2_1/local">
                           <soapenv:Header>
                              <v2:RequestSOAPHeader>
                                 <v2:spId>'.$spId.'</v2:spId>
                                 <v2:spPassword>'.$password.'</v2:spPassword>
                                 <v2:serviceId>225012000011553</v2:serviceId>
                                 <v2:timeStamp>'.$timeStamp.'</v2:timeStamp>
                                 <v2:OA>'.$mobileNo.'</v2:OA>
                                 <v2:FA>'.$mobileNo.'</v2:FA>
                                 <v2:token/>
                              </v2:RequestSOAPHeader>
                           </soapenv:Header>
                           <soapenv:Body>
                              <loc:chargeAmount>
                                 <loc:endUserIdentifier>'.$mobileNo.'</loc:endUserIdentifier>
                                 <loc:charge>
                                 <description>charge</description>
                                    <currency>XOF</currency>
                                    <amount>'.$amount.'</amount>
                                    <code></code>
                                 </loc:charge>
                                 <loc:referenceCode>'.$timeStamp.'</loc:referenceCode>
                              </loc:chargeAmount>
                           </soapenv:Body>
                        </soapenv:Envelope>';

        $result = $client->send($xmlMsg, '', 0, 180);

        //Zend_Debug::dump($result);die();

      $err = $client->getError();
        if ($err) {
            //echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        }


        //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        // echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        //  echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
 

        $paymentTimeStamp = date('d-m-Y');

        $buildUrl = null;

        if(!$client->fault)
        {
            $error = $client->getError();
            
            if(!$error)
            {

                //Zend_Debug::dump($result);
                //die();
                
                if($result["faultcode"] == '') {
                
                //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);
                
                //todo, change message language
                $message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
                $this->sendsms($mobileNo, $message, $chapId);
                
                $paymentResult = 'Success';
                $paymentTransId = strtotime($paymentTimeStamp);

                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

                return $buildUrl;
                
                }
            }
        }
    }
    
    function sendsms($mobileNo, $message, $chapId = null)
    {
        
       include_once( APPLICATION_PATH.'/../public/vendors/nusoap.php' );
        
       $client = new nusoap_client('http://196.201.33.108:8310/SendSmsService/services/SendSms');
       $client->soap_defencoding = 'UTF-8';
       $client->decode_utf8 = false;

       $timeStamp = date("Ymd").date("His");
      // $timeStamp = 20141217125900;
        
        
       $spId =  2250110000653;
       $pass = '32EE5170661AB5C7016F129BE1193D34';
       $timeStamp = 20141217125900;
       
        
      // $password = md5($spId.$pass.$timeStamp);
        
       $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $pass, 'serviceId' => '225012000002019',  'timeStamp' => $timeStamp, 'OA' => $mobileNo, 'FA' => $mobileNo));
        
       $phone = array(
       		'addresses'     =>  'tel:'.$mobileNo,
       		'senderName'   =>  102,
       		'message'  => $message
       );
       

       $result = $client->call('sendSms', $phone, '', '', $header);
 
      
      //echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
      //echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES)  . '</pre>';
     // echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
      //Zend_Debug::dump($result);
   
    	 
    }
    
    
    public function chrage($mobileNo, $price, $currency)
    {
    	
    }

    
}
