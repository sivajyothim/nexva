<?php
/**
 * This class is used to manage Interop payments
 * 
 * Maheel
 */
class Nexva_MobileBilling_Type_Ericsson extends Nexva_MobileBilling_Type_Abstract 
{
     /**
     * Pay and download a premium app
     * In this function it will call the Interop Payment gateway to make a mobile payment and if succeeded, 
     * Returns the download url of the app. This is a direct link to the app file on S3 server wrapped by the 
     * relevent parameters (AWSAccessKeyId,Expires,Signature).
     *       
     * @param $chapId Chap ID
     * @param $appId App ID 
     * @param $buildId Build ID 
     * @param $mobileNo Mobile No 
     * @param $price Price
     * returns XML out put of the status
     */
    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
         $formattedPrice = $this->formatPriceAction($price);
        
        /*********************************************************************************************************************
          These are the parameters to be sent to Interop API in order make a mobile payment
         **********************************************************************************************************************       

          Sample Url = http://<IP_ADDRESS>/cgibin/cbapi.cgi?func=01&vendor_id=3&uname=aggr3g4to&passwrd=P455wrd&content_id=33678&
          dest_MDN=2395658381&tstamp=20040914124133&description=helloworld&price=00199

          functionName - Required, Defines the requested  action. Valid values  are:
          00 - Used to query for the presence of a subscriber in the Interop platform.
          01 - Used when the caller of the API is looking to debit the subscriber.
          vendorId - Required, A value supplied by Interop Technologies during the implementation process that is used to identify the content vendor
          uname - Required, A value supplied by Interop Technologies during the implementation process that is used to identify the system user
          passwrd - Required, A value supplied by Interop Technologies during the implementation process that is used to grant the system user access
          contentId - Optional, The short code identifying a particular campaign or piece of content. Required when doing a debit (func = â€˜01â€™)
          destMDN - Required,10-Â­â€�digit subscriber Mobile Directory Number (MDN)
          tstamp - Optional, The timestamp indicating when the transaction took place. Must be provided in YYYYMMDDHHMMSS format. Required when doing a debit (func = â€˜01â€™).
          description - Optional, Human-readable description of the content
          price - Optional, Retail price (in cents) of content piece in XXXXX format (for example, 00299 = $2.99). Required when doing a debit (func = â€˜01â€™)
          mode - If â€˜testâ€™, all transactions will be written to the test billing system; the default value is â€˜liveâ€™.

        ***************************************************************************************************************** */

        $functionName = '00';
        $vendorId = 2;
        $uname = '0p3nmark3t';
        $passwrd = '0987654321';
        $contentId = $chapId . '-' . $appId;
        $destMDN = $mobileNo;
        $tstamp = date('YmdHis');
        $description = $appName;
        $price = $formattedPrice;
        $mode = 'test';

        //API url
        $paymentRequest = "http://10.51.1.41/cgi-bin/cbapi.cgi?func=$functionName&vendor_id=$vendorId&uname=$uname&passwrd=$passwrd&dest_MDN=$destMDN&tstamp=$tstamp&mode=$mode&price=$price&content_id=$contentId&description=$description";

        //Call the API and get the output
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
        curl_setopt($curlHandle, CURLOPT_URL, $paymentRequest);
        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
        curl_setopt($curlHandle, CURLOPT_VERBOSE, 0);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, true);
        $payStatus = curl_exec($curlHandle);

        curl_close($curlHandle);

        //convert the XML string to an Object
        $payStatus = simplexml_load_string($payStatus);

        $paymentTimeStamp = $payStatus->Timestamp;
        $paymentResult = $payStatus->Result;
        $paymentTransId = $payStatus->TransactionID;
        $paymentStatusCode = $payStatus->StatusCode;
        $buildUrl = null; 
        
        //Check if payment was made success, Provide the download link
         if ($paymentStatusCode == '00' && $paymentResult == 'Success') 
         {
            //Get the S3 URL of the Relevant build
            $productDownloadCls = new Nexva_Api_ProductDownload();
            $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

            //Update the relevant Transaction record in the DB
            parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);
         }
         
         return $buildUrl;
    }
   
    function sendsms($mobileNo, $message, $chapId)
    {    
    	include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
        
        //SMPP Configurations of MTN Congo
        $configArray[33644] = array(
                                        'host' => '10.80.101.50',
                                        'port' => 8411,
                                        'systemId' => 'mtnappssm45',
                                        'password' => 'nosxvfn4',
                                        'systemType' => '',
                                        'sourceAddress' => 'MTNAPP'
                                     );     
        
        $smppHost = $configArray[$chapId]['host'];
        $smppPort = $configArray[$chapId]['port'];
        $systemId = $configArray[$chapId]['systemId'];
        $password = $configArray[$chapId]['password'];
        $systemType = $configArray[$chapId]['systemType'];
        $from = $configArray[$chapId]['sourceAddress'];

//        $smpp = new SMPPClass();
//        $smpp->SetSender($from);
//        /* bind to smpp server */
//        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
//        /* send enquire link PDU to smpp server */
//        $smpp->TestLink();
//        /* send single message; large messages are automatically split */
//        $messageStatus = $smpp->Send($mobileNo, $message);
//        
//        $smpp->End();
//        
//        return $messageStatus;
                /* send unicode message */
        //$smpp->Send("31648072766", "&#1589;&#1576;&#1575;&#1581;&#1575;&#1604;&#1582;&#1610;&#1585;", true);
        /* send message to multiple recipients at once */
        //$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
        /* unbind from smpp server */
          	 
    }
}
