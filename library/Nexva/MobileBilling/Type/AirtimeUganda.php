<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 4/10/14
 * Time: 11:36 AM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_MobileBilling_Type_AirtimeUganda extends Nexva_MobileBilling_Type_Abstract
{
    public function __construct()
    {
        include_once( APPLICATION_PATH . '/../public/vendors/nusoap.php' );
    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        //echo $chapId,'-',$buildId,'-',$appId,'-',$mobileNo,'-',$appName,'-',$price;die();

        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];

        $amount = ceil($currencyRate * $price);

        $client = new nusoap_client('http://172.25.48.43:8310/AmountChargingService/services/AmountCharging', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $spId =  '2560110000694';
        $spPass = 'Huawei2014';
        $timeStamp = date("Ymd").date("His");

        $password = strtoupper(MD5($spId.$spPass.$timeStamp));

        $xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" xmlns:loc="http://www.csapi.org/schema/parlayx/payment/amount_charging/v2_1/local">
                           <soapenv:Header>
                              <v2:RequestSOAPHeader>
                                 <v2:spId>'.$spId.'</v2:spId>
                                 <v2:spPassword>'.$password.'</v2:spPassword>
                                 <v2:serviceId></v2:serviceId>
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
                                    <currency>UGX</currency>
                                    <amount>'.$amount.'</amount>
                                    <code></code>
                                 </loc:charge>
                                 <loc:referenceCode>'.$timeStamp.'</loc:referenceCode>
                              </loc:chargeAmount>
                           </soapenv:Body>
                        </soapenv:Envelope>';

        $result = $client->send($xmlMsg, '', 0, 180);

        //Zend_Debug::dump($result);die();

        /*$err = $client->getError();
        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        }


        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
        die();*/

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

                $message = 'Hello, your account was charged '. $amount.' UGX at '.$paymentTimeStamp. ' for the successful transaction on the MTN Uganda App Store. Thank you.';
                $this->sendsms($mobileNo, $message, $chapId);

                $paymentResult = 'Success';
                $paymentTransId = strtotime($paymentTimeStamp);

                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

                return $buildUrl;
            }
        }
    }

    public function sendsms($mobileNo, $message, $chapId)
    {
        //echo $mobileNo,' - ',$message;die();
        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );

        $smppHost = '212.88.118.228';
        $smppPort = '5001';
        $systemId = 'mtnappshop';
        $password = 'n3Xv4';
        $systemType = 'vma';
        $from = 'MTNUganda';

        //echo 'Airtime';
        //echo '<pre>';
        $smpp = new SMPPClass();
        $smpp->debugmod(false);
        $smpp->SetSender($from);
        /* bind to smpp server */
        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
        /* send enquire link PDU to smpp server */
      //  $smpp->TestLink();
        /* send single message; large messages are automatically split */
       //$messageStatus = $smpp->Send('256775516494', 'neXva neXva neXva neXva neXva neXva test ');
        $messageStatus = $smpp->Send($mobileNo, $message);

        //Zend_Debug::dump($messageStatus,'ddd');

        $smpp->End();
        //echo '</pre>';
        //die();
    }
}