<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 4/10/14
 * Time: 11:36 AM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_MobileBilling_Type_HuaweiUganda extends Nexva_MobileBilling_Type_Abstract
{
    public function __construct()
    {
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        //echo $chapId,' - ',$buildId,' - ',$appId,' - ',$mobileNo,' - ',$appName,' - ',$price;die();

        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];

        $spEndPoint =  'http://172.25.48.43:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v17';
        $spId =  '2560110000692';
        $spPass = 'Huawei2014';
        $spServiceId = '0013052000001422';

        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        
        $amount = ceil($currencyRate * $price);

        $timeStamp = date("Ymd").date("His");
        $password = strtoupper(MD5($spId.$spPass.$timeStamp));
        
        $timeStamp2 = date("Ymd").date("His").$appId;

        $xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0">
			   <soapenv:Header>
				  <RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
					 <spId>2560110000692</spId>
					 <spPassword>'.$password.'</spPassword>
					 <serviceId></serviceId>
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
						<value>Appstore</value>
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
						<name></name>
						<value></value>
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


        $result=$client->send($xmlMsg, 'POST');

        /*Zend_Debug::dump($result);die();

        $err = $client->getError();
        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        }


        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

        die();*/
        $amount = ceil($currencyRate * $price);
        $paymentTimeStamp = date('Y-m-d H:i:s');

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
                $paymentTransId = strtotime($paymentTimeStamp);
                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl, $client->request, $client->response);
                }
            }
        }
        
        return $buildUrl;
    }

  public function sendsms($mobileNo, $message, $chapId)
    {
        //die('Mobile Money');
        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );

        $smppHost = '212.88.118.228';
        $smppPort = '5001';
        $systemId = 'mtnappshop';
        $password = 'n3Xv4';
        $systemType = 'vma';
        $from = 'MTNUganda';

        //echo '<pre>';
        $smpp = new SMPPClass();
        $smpp->debugmod(false);
        $smpp->SetSender($from);
        /* bind to smpp server */
        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
        /* send enquire link PDU to smpp server */
        //$smpp->TestLink();
        /* send single message; large messages are automatically split */
        //$messageStatus = $smpp->Send('250731000057', 'neXva neXva neXva neXva neXva neXva ');
        $messageStatus = $smpp->Send($mobileNo, $message);

        //Zend_Debug::dump($messageStatus,'booo');

        $smpp->End();
        //echo '</pre>';
        //die();
    }
}