<?php
/**
 * This class is used to manage MTN
 */
class Nexva_MobileBilling_Type_HuaweibeninMobileMoney extends Nexva_MobileBilling_Type_Abstract 
{
    
    public function __construct() {
        
      
    }
    
    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
                
        ini_set('default_socket_timeout', 300);
        
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
        
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        
        $price = ceil($currencyRate * $price);


        $spEndPoint =  'http://41.206.4.219:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v18';
        
        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        
        $spId =  2290110003718;
        $pass = 'Huawei123';
        $timeStamp = date("Ymd").date("His");
        $password = md5($spId.$pass.$timeStamp);
        
        $xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0">
			   <soapenv:Header>
				  <RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
					 <spId>'.$spId.'</spId>
					 <spPassword>'.$password.'</spPassword>
					  <serviceId/>
					 <timeStamp>'.$timeStamp.'</timeStamp>
					</RequestSOAPHeader>
			   </soapenv:Header>
			   <soapenv:Body>
				  <b2b:processRequest>
					 <serviceId>'.$mobileNo.'@nexva.sp</serviceId>
					 <parameter>
						<name>DueAmount</name>
						<value>'.$price.'</value>
					 </parameter>
					 <parameter>
						<name>MSISDNNum</name>
						<value>'.$mobileNo.'</value>
					 </parameter>
					 <parameter>
						<name>ProcessingNumber</name>
						<value>'.$timeStamp.'</value>
					 </parameter>
					 <parameter>
						<name>serviceId</name>
						<value>2</value>
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
						<value>'.$price.'</value>
					 </parameter>
					 <parameter>
						<name>Narration</name>
						<value>Please confirm the amount of '.$price.' XOF to Complete Transaction.</value>
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

*/
        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

             die('aa');
        $amount = ceil($currencyRate * $price);
        $paymentTimeStamp = date('d-m-Y');

        $buildUrl = null;

        if(!$client->fault)
        {
            $error = $client->getError();

            if(!$error)
            {
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
            }
        }
    }
    
    function sendsms($mobileNo, $message, $chapId = null)
    {
    
       include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        
       //testbed
       $client = new nusoap_client('http://41.206.4.219:8310/SendSmsService/services/SendSms');
       //live
       $client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
       
       $client->soap_defencoding = 'UTF-8';
       $client->decode_utf8 = false;
        
       $timeStamp = date("Ymd").date("His");
       
       //testbed
       $spId =  2290110001128;
       $pass = 'Huawei123';
       
       //live
       $spId =  2290110002440;
       $pass = 'Huawei123';
       
       $password = md5($spId.$pass.$timeStamp);
        
       $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'timeStamp' => $timeStamp, 'OA' => $mobileNo, 'FA' => $mobileNo));
        
       $phone = array(
       		'addresses'     =>  'tel:'.$mobileNo,
       		'senderName'   =>  '7084',
       		'message'  => $message
       );
       

       $result = $client->call('sendSms', $phone, '', '', $header);
       
       
       
 
      
      echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
      echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES)  . '</pre>';
      echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
      Zend_Debug::dump($result);
      
      //.die();
       
        
       if($client->fault) {
       	// there is a error. Payment is unsuccessful
           return false;
       } else {
       	$error = $client->getError();
       	if($error) {
       	    
       	    return false;
       	} else {
       	    
       	    return true;
       	    
       	    }
       	}
       
    	 
    }
    
    
    public function chrage($mobileNo, $price, $currency)
    {
    	
    }

    
}
