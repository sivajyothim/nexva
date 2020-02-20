<?php
/**
 * This class is used to manage MTN
 */
class Nexva_MobileBilling_Type_Huaweici extends Nexva_MobileBilling_Type_Abstract 
{
    
    public function __construct() {
        
      
    }
    
 public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
                
        ini_set('default_socket_timeout', 600);
        
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
        
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        
        $amount = ceil($currencyRate * $price);

        /* testbed
        $spEndPoint =  'http://196.201.33.108:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v18';
        $spId =  '2250110000653'; 
        $spPass = '0A0A1855AD5BAFE193F787257F68C71E';
        
        $timeStamp = 20141217125900;        
        $password = '32EE5170661AB5C7016F129BE1193D34';
         */
        
        
        $spEndPoint =  'http://196.201.33.98:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v18';
        $spId =  '2250110001369';
        
        $timeStamp2 = date("Ymd").date("His").$appId;
        
        $timeStamp = 20150416090000;
        $password = '74761FCA5C2401A58225FC235371C0E4';
        
        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
         
        //testbed 225012000002019 serviceId
        

        $xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0">
			   <soapenv:Header>
				  <RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
					 <spId>'.$spId.'</spId>
					 <spPassword>'.$password.'</spPassword>
					 <serviceId>225012000011553</serviceId>
					 <timeStamp>'.$timeStamp.'</timeStamp>
					</RequestSOAPHeader>
			   </soapenv:Header>
			   <soapenv:Body>
				  <b2b:processRequest>
					 <serviceId>200</serviceId>
					 <parameter>
						<name>DueAmount</name>
						<value>'.$amount.'</value>
					 </parameter>
					 <parameter>
						<name>MSISDNNum</name>
						<value>'.$mobileNo.'</value>
					 </parameter>
					 <parameter>
						<name>ProcessingNumber</name>
						<value>'.$timeStamp2.'</value>
					 </parameter>
					 <parameter>
						<name>serviceId</name>
						<value>AS</value>
					 </parameter>
					 <parameter>
						<name>AcctRef</name>
						<value>1234</value>
					 </parameter>
					 <parameter>
						<name>AcctBalance</name>
						<value>0</value>
					 </parameter>
					 <parameter>
						<name>MinDueAmount</name>
						<value>1</value>
					 </parameter>
	                 <parameter>
						<name>Narration</name>
						<value>Please confirm the amount of '.$amount.' XOF to Complete Transaction.</value>
					 </parameter>
					 <parameter>
						<name>PrefLang</name>
						<value>en</value>
					 </parameter>
					 <parameter>
						<name></name>
						<value></value>
					 </parameter>
				  </b2b:processRequest>
			   </soapenv:Body>
			</soapenv:Envelope>';


        $result=$client->send($xmlMsg, 'POST', 0, 180);

        /*Zend_Debug::dump($result);die();

        $err = $client->getError();
        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        }



           echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
          echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
         echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
         die();
         
         */
        

            // die('aa');

        $paymentTimeStamp = date('Y-m-d H:i:s');

        $buildUrl = null;

        if(!$client->fault && ($result['return'][3]['value'] == '01') )
        {
            $error = $client->getError();
            {
                
                if(!($result['faultcode'])) {
                
                //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

                //todo, change message language
                $message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
                $this->sendsms($mobileNo, $message, $chapId);

                $paymentResult = 'Success';
                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $timeStamp2, $paymentResult, $buildUrl, htmlspecialchars($client->request, ENT_QUOTES) , htmlspecialchars($client->response, ENT_QUOTES));
                
             //   echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
             //   echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
             //   echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
            //    die();
                
                }
            }
        }
        
        return $buildUrl;
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
