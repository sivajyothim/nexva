<?php
/**
 * Created by JetBrains PhpStorm. ABc
 * User: viraj
 * Date: 4/22/14
 * Time: 12:15 PM
 * To change this template use File | Settings | File Templates.
 */

class Api_TestoneController extends Zend_Controller_Action
{


    public function init() {

        //include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );

        /* Initialize actionNexpayer controller here */
        $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
 
        //global $HTTP_RAW_POST_DATA;
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
    
    public function orangesendotpAction() {
        
        //		"partnerId": "PADOCK",
    //    "service": " ORANGESTORE",
     //   "partnerId": "PDKSUB",
     
   //     00224 628 996 873
        $msg = '{
	"challenge": {
		"method": "OTP-SMS-AUTH",
		"country": "GIN",
		"service": "ORANGESTORE",
		"partnerId": "PADOCK",
		"inputs": [{
			"type": "MSISDN",
			"value": "+224628996873"
		},  {
			"type": "message",
			"value": "To confirm your purchase please enter the code %OTP%"
		}, {
			"type": "otpLength",
			"value": "4"
		}, {
			"type": "senderName",
			"value": "ORANGESTORE"
		}]
	}
}';
        
        Zend_Debug::dump($msg);
        
     
 //   $url = "https://iosw-rest.orange.com/PDK/BE_API-1/challenge/v1/challenges/";
    $url = "https://iosw-rest.orange.com:443/PDK/BE_API-1/challenge/v1/challenges/";
   
    
 //  $url =   "https://iosw3sn-rest.orange.com:8443/PDK/BE_API-1"; 
$cert_file = APPLICATION_PATH . '/configs/orangeappstore.pem';
$cert_password = "abc123";
 
$ch = curl_init();
 
$options = array( 
    CURLOPT_RETURNTRANSFER => true,
    //CURLOPT_HEADER         => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
     
    CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
    CURLOPT_RETURNTRANSFER        => true,
    CURLOPT_HEADER        => true,
    CURLOPT_VERBOSE        => true,
    CURLOPT_URL => $url ,
    CURLOPT_SSLCERT => $cert_file ,
    CURLOPT_SSLCERTPASSWD => $cert_password ,
);


curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($msg))
);

curl_setopt_array($ch , $options);
curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);

 
$output = curl_exec($ch);
 
if(!$output)
{
    echo "Curl Error : " . curl_error($ch);
}
else
{
    echo 'ddd';
    $info = curl_getinfo($ch);
    print_r($info['request_header']);
     Zend_Debug::dump($output);
     
   
}
        
        
        die('ddd');
        
        
    }

    

    
    
    public function orangeconfirmotpAction() {
    
        $challnageId = $this->_request->challnageId;
        $confirmationCode = $this->_request->confirmationCode;
        
        $msg = '{
	"challenge": {
		"method": "OTP-SMS-AUTH",
		"country": "GIN",
		"service": "ORANGESTORE",
		"partnerId": "PADOCK",
		"inputs": [{
			"type": "MSISDN",
			"value": "+224628996873"
		}, {
			"type": "confirmationCode",
			"value": "'.$confirmationCode.'"
		}, {
			"type": "info",
			"value": "OrangeApiToken,ise2"
		}]
	}
}';
        

        Zend_Debug::dump($msg);
         
       // $url = "https://iosw-rest.orange.com/PDK/BE_API-1/challenge/v1/challenges/588a04777970dc2f67df6cde";
        $url = "https://iosw-rest.orange.com:443/PDK/BE_API-1/challenge/v1/challenges/".$challnageId;
       
    
        //  $url =   "https://iosw3sn-rest.orange.com:8443/PDK/BE_API-1";
        $cert_file = APPLICATION_PATH . '/configs/orangeappstore.pem';
        $cert_password = "abc123";
    
        $ch = curl_init();
    
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
             
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_RETURNTRANSFER        => true,
            CURLOPT_HEADER        => true,
            CURLOPT_VERBOSE        => true,
            CURLOPT_URL => $url ,
            CURLOPT_SSLCERT => $cert_file ,
            CURLOPT_SSLCERTPASSWD => $cert_password ,
        );
    
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($msg))
        );
    
        curl_setopt_array($ch , $options);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
    
    
        $output = curl_exec($ch);
    
        if(!$output)
        {
            echo "Curl Error : " . curl_error($ch);
        }
        else
        {
            echo 'ddd';
            $info = curl_getinfo($ch);
            Zend_Debug::dump($info['request_header'][5]);
            Zend_Debug::dump($output);
            Zend_Debug::dump($info, '$info');
            
            $arrray = preg_split("/\\r\\n|\\r|\\n/",$output);
            $arrayb = explode('/',$arrray[11]);
            Zend_Debug::dump($arrayb);
            
             $dd =   substr($output, strpos($output, '{')+strlen( '{'));
            
     
            
            Zend_Debug::dump($dd, 'vvvv');
            
           $aa = '{'.$dd;
        $ss=    json_decode($aa);
           
        Zend_Debug::dump($ss, 'json');
        
        Zend_Debug::dump($ss->challenge->result[0]->value, 'json');
             
        }
    
    
        die('ddd');
    
    
    }
    

    
    
    
    
    public function orangepaymentAction() {
    
        
        $token = $this->_request->token;
        $clientCorrelator = $this->_request->clientCorrelator;
        
           $token =  "B64T5L4OD/sE+GODDOc5tP7wC09ggeZ9razfqFgl0Hr+nPRe9inXH3zbjOn8YNBkamY/+DvRXkGd2MswarfDPGUVA==|MCO=OGC|tcd=1488533601|ted=1488533701|7Js7h0OPWt/RmVC7x6RcSJl0w9s=";
           $clientCorrelator = 100041579;
    
        $msg = '{
	"amountTransaction": {
		"clientCorrelator": "'.$clientCorrelator.'",
		"endUserId": "acr:OrangeAPIToken",
		"referenceCode": "REF-12346",
		"transactionOperationStatus": "Charged",
		"paymentAmount": {
			"chargingInformation": {
				"amount": "1",
				"currency": "GNF",
				"description": "TopEleven - 200 gold"
			},
			"chargingMetaData": {
				"serviceId": "ORANGESTORE",
				"onBehalfOf": "TopEleven",
				"purchaseCategoryCode": "Game"
			}
		}

	}
}';
        
        
           $msg = '{"amountTransaction": {
            "endUserId": "acr:OrangeAPIToken",
            "paymentAmount": {
            "chargingInformation": {
            "amount": "1",
            "currency": "GNF",
            "description": "Test Rania"
            },
            "chargingMetaData" : {
            "onBehalfOf" : "test123456789",
            "purchaseCategoryCode" : "Game",
            "channel" : "5",
            "serviceId" : "ORANGESTORE"
            }
        },
        "transactionOperationStatus": "Charged",
        "referenceCode": "REF-123456",
        "clientCorrelator": "'.$clientCorrelator.'"
        }}';
           
           
          $msg =    '{"amountTransaction": {
               "endUserId": "acr:OrangeAPIToken",
               "paymentAmount": {
               "chargingInformation": {
               "amount": "1",
               "currency": "GNF",
               "description": "Test Rania"
               },
               "chargingMetaData" : {
               "onBehalfOf" : "test123456789",
               "purchaseCategoryCode" : "Game",
               "channel" : "5",
               "serviceId" : "ORANGESTORE"
               }
           },
           "transactionOperationStatus": "Charged",
           "referenceCode": "REF-123456",
           "clientCorrelator": "'.$clientCorrelator.'"
           }}';
        
        Zend_Debug::dump($msg);
    
         
        $url = "https://iosw-rest.orange.com:443/PDK/OneAPI-1/payment/v1/acr:OrangeAPIToken/transactions/amount";
    
        //  $url =   "https://iosw3sn-rest.orange.com:8443/PDK/BE_API-1";
        $cert_file = APPLICATION_PATH . '/configs/orangeappstore.pem';
        $cert_password = "abc123";
    
        $ch = curl_init();
    
           $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER        => true,
            CURLOPT_HEADER        => true,
            CURLOPT_VERBOSE        => true,
            CURLOPT_URL => $url ,
            CURLOPT_SSLCERT => $cert_file ,
            CURLOPT_SSLCERTPASSWD => $cert_password ,
        );
           curl_setopt($ch, CURLINFO_HEADER_OUT, 1); //
           
          // B64TSi6g73jnMJg3ksvGJvyM4Y/amh5Hi3gQmYq0HZnr+cNGlYT7Ao8XBXjx5eZ0qLQXSMDE70aLdqUiSLMZZxVzQ==|MCO=OGC|tcd=1487157168|ted=1487157268|k+E0Z/esPSlBTcmOKgSCM/YfBVk=
         //  B64TSi6g73jnMJg3ksvGJvyM4Y/amh5Hi3gQmYq0HZnr%2BcNGlYT7Ao8XBXjx5eZ0qLQXSMDE70aLdqUiSLMZZxVzQ%3D%3D%257CMCO%3DOGC%257Ctcd%3D1487157168%257Cted%3D1487157268%257Ck%2BE0Z/esPSlBTcmOKgSCM/YfBVk%3D

        $headers = array(
            sprintf('OrangeAPIToken:%s', $token),
            'Accept: application/json;charset=\'utf-8\'',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($msg)
        );
        
        Zend_Debug::dump($headers);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
    
        curl_setopt_array($ch , $options);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
    
    
        $output = curl_exec($ch);
    
        if(!$output)
        {
            echo "Curl Error : " . curl_error($ch);
        }
        else
        {
            echo 'ddd';
            $info = curl_getinfo($ch);
            Zend_Debug::dump($info['request_header'][5]);
            Zend_Debug::dump($info['request_header']);
            Zend_Debug::dump($output);
             
             
        }
    
    
        die('ddd');
    
    
    }
    

    public function orangepAction() {
        
        $config = array( 'sslcert' => 'path/to/ca.crt', 'sslpassphrase' => 'p4ssw0rd');
        $adapter = new Zend_Http_Adapter_Socket();
        $adapter->setConfig($config);
    
    $client = new Zend_Http_Client($url);
    
    $client->setAdapter($adapter);
    
    $client->setHeaders(array(
        'User-Agent: Mozilla/5.0 Firefox/3.6.12',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9',
        'Accept-Language: en-us,en;q=0.5',
        'Accept-Encoding: deflate',
        'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
        'Content-Type: application/x-www-form-urlencoded',
        'Content-Length: 109'
    ));
    
    $client->setParameterPost(array(
        'merchantid' => $merchantId,
        'amount' => $price,
        'sessionid' => $sessionId,
        'purchaseref' => $this->getEnc($interopPaymentId . '#WEB#' . $sessionId),
        //'purchaseref' => $interopPaymentId.'#WEB#'.$sessionId
    ));
    
    $response = $client->request(Zend_Http_Client::POST);
    $token = $response->getRawBody();
    
    $pgData = array(
        'merchantid' => $merchantId,
        'token' => trim($token),
        'amount' => $price,
        'sessionid' => $sessionId,
        'purchaseref' => $this->getEnc($interopPaymentId . '#WEB#' . $sessionId),
        //'purchaseref' => $interopPaymentId.'#WEB#'.$sessionId
    );
    
    //print_r($pgData); die();
}
    
    
    public function orangesmsAction() {
    

        
        
        $msg = '{
	"outboundSMSMessageRequest": {
		"address": "tel:+224624245911",
		"outboundSMSTextMessage": {
			"message": "Hello from neXva"
		},
		  "senderAddress":"tel:+224624245911",
          "senderName":"Orange"
	}
}';
        
        

        $msg = '{
	"outboundSMSMessageRequest": {
		"address": "tel:+224628914540",
		"outboundSMSTextMessage": {
			"message": "Hello from é neXva"
		},
		  "senderAddress":"tel:+224624245911",
          "senderName":"Orange"
	}
}';
        
        
        
        $msg = '{
	"outboundSMSMessageRequest": {
		"address": "tel:+224628914540",
		"outboundSMSTextMessage": {
			"message": "Hello from e neXva"
		},
		  "senderAddress":"tel:+224624245911",
          "senderName":"Orange"
	}
}';
        
        
        //224624245911  224620768594
    
         
       // $url = "https://iosw3sn-rest.orange.com:_443/PDK/SMS_Wrapper-1";
        
          //$url =  "https://iosw3sn-rest.orange.com:443/PDK/SMS_Wrapper-1/smsmessaging/v1/outbound/tel%3A%2B1224620768594/requests";
       
          $url =  "https://iosw-rest.orange.com:443/PDK/SMS_Wrapper-1/smsmessaging/v1/outbound/tel:+224624245911/requests";
    
        //  $url =   "https://iosw3sn-rest.orange.com:8443/PDK/BE_API-1";
        $cert_file = APPLICATION_PATH . '/configs/orangeappstore.pem';
        $cert_password = "abc123";
    
        $ch = curl_init();
    
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
             
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_RETURNTRANSFER        => true,
            CURLOPT_HEADER        => true,
            CURLOPT_VERBOSE        => true,
            CURLOPT_URL => $url ,
            CURLOPT_SSLCERT => $cert_file ,
            CURLOPT_SSLCERTPASSWD => $cert_password ,
        );
    
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-OAPI-Application-Id: b2b-orangestore-41o59641s3',
            'X-OAPI-Contact-Id: b2b-orangestore-41o59641s3',
            'X-OAPI-Resource-Type: SMS_OSM',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($msg))
        );
        
        
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-OAPI-Application-Id: b2b-orangestore-72c67501t2',
            'X-OAPI-Contact-Id: b2b-orangestore-72c67501t2',
            'X-OAPI-Resource-Type: SMS_OSM',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($msg))
        );
        
    
        curl_setopt_array($ch , $options);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
    
    
        $output = curl_exec($ch);
    
        if(!$output)
        {
            echo "Curl Error : " . curl_error($ch);
        }
        else
        {
            echo 'ddd';
            $info = curl_getinfo($ch);
            print_r($info['request_header']);
            Zend_Debug::dump($output);
             
             
        }
    
    
        die('ddd');
    
    
    }
    
    
    public function orangesendotpaAction() {
        $msg = '{
    "challenge": {
        "method": "OTP-SMS-AUTH",
        "country": "GIN",
        "service": " ORANGESTORE",
        "partnerId": "PADOCK",
        "inputs": [
            {
                "type": "MSISDN",
                "value": “+224xxxxxxxxx"
            },
            {
                "type": "confirmationCode",
                "value": ""
            },
            {
                "type": "message",
                "value": "To confirm your purchase please enter the code %OTP%”
            },
	    {
		"type": "otpLength",
		"value": "4"
	     },
                   {
                                "type": "senderName",
                                "value": "ORANGESTORE"
                   }
        ]
    }}';
    
    
    
    
    
    
         
        $url = "https://iosw-rest.orange.com/PDK/BE_API-1/challenge/v1/challenges/";
        $cert_file = APPLICATION_PATH . '/configs/orangeappstore.pem';
        $cert_password = "abc123";
    
        $ch = curl_init();
    
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
             
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            //CURLOPT_VERBOSE        => true,
            CURLOPT_URL => $url ,
            CURLOPT_SSLCERT => $cert_file ,
            CURLOPT_SSLCERTPASSWD => $cert_password ,
        );
    
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
    
        curl_setopt_array($ch , $options);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
    
    
        $output = curl_exec($ch);
    
        if(!$output)
        {
            echo "Curl Error : " . curl_error($ch);
        }
        else
        {
            echo htmlentities($output);
        }
    
    }
    
    
    public function sendemailAction() {
        
        set_time_limit(0);
        $aa = ("sampson3333@163.com,admobearnings@gmail.com,dilan@dilan.me,abhilash.thippu@gmail.com,milan.jovanovski@sportypal.com,niets8@hotmail.com,service@brig8.com,contact@wattpad.com,hashir@mobware4u.com,team@mycityway.com,b.kunjadiya@zatun.com,content.manager@star-arcade.com,info@hoccer.com,info@mobi-app.com,contact@macropinch.com,support@m6.com,Partnerships@ebuddy.com,eric.hu@maxthon.com,stealthrabbiapps@gmail.com,itnguyen0104@gmail.com,blundell.app.dev@gmail.com,yangxiaoli@netqin.com,root@miumeet.com,googlekooper@yahoo.com,contact@rdmobile.fr,wyo.hunter@gmail.com,konstantin.pribluda@gmail.com,mail@mayergames.com,dharmin007@gmail.com,mat@pocketeers.co.uk,jp@mob4.com,david@betrescue.com,support@kik.com,info@aquilonis.com,info@htapplications.nl,mawaleed2010@yahoo.com,support@wjmc.com,mobilesupport@allslotscasino.com,factum@factum.ro,ringapps1@hotmail.com,josh@killermobile.com,sysadmin@cellectivity.com,simplifynowsoftware@gmail.com,support@square-enix.com,info@cadremploi.fr,sparklinc@hotmail.com,info@cloudnote.net,tsuda@johospace.com,androidfeed@gmail.com,
                mytikus@gmail.com,bruce.bradley@littleservices.com,francois.jacob.hello@gmail.com,marion.hong@handy-games.com,info@codeglue.com,androiddevelopergame@gmail.com,paolo@appymob.com,andry@coderminus.com,evilboy2107@gmail.com,99truewap@gmail.com,support@realjatt.com,info@mirko-paschke.de,yayazhou2010@gmail.com,1826799545@qq.com,info@agilebinary.com,vincent.dondaine@bulkypix.com,monukhan2@facebook.com,google@snapp.fr,cory@satsportsna.com,admin@sunprabha.com,dmitry@mobstudio.ru,santhgates@gmail.com,atsepko@macphun.com,mekdroid@gmail.com,info@pricerhythm.com,jerry.edfors@gmail.com,antonio.tonev@gmail.com,Mexican.dirtyfellow@gmail.com,support@irexsoft.com,info@mogam.ch,jtrencsenyi@casualgamestore.com,lourencepanongon@yahoo.com,som4tress@gmail.com,appslab.android@gmail.com,david@looplr.com,parashar.atul9@gmail.com,topdroidapps@gmail.com,alexander.bravve@nordcurrent.com,no-reply@begtiatbulom.com,javafree@funny-foto.cz,market@kitmaker.com,sanadhammika@sltnet.lk,marcioandreyoliveira@plicatibu.com,richard@lunaforte.com,qu185985@tom.com,chat4mob@gmail.com,mengfenke@sohu.com,
                zenmeng652027007@163.com,fanmouuaa@eyou.com,chenbinsad@eyou.com,chuangquan432474@163.com,langnesswil@sohu.com,stumpodobro@sohu.com,jiangshuydf@eyou.com,yg@wareninja.com,Root@pannous.net,nattta@mobitee.com,daniel.kuchvalek@aponia.com,sausanmhm@yahoo.com,passiondroid@gmail.com,dev@mobiwolf.com,teamaltis@gmail.com,yuris@wise-mon.com,MEHDI.AZHER@IBIBO.COM,submit@appcraft.org,helloohellooow@gmail.com,mitri@polarbit.com,sreimonenq@gmail.com,m.bhantooa@btinternet.com,,,,masha@viewdle.com,buonvianh_bui@yahoo.com.au,sarah@where.com,Picaud.laethier@gmail.com,darshan@goskoop.com,garf10@poczta.onet.pl,kitty@kittypad.com,eshops@shapeservices.com,Kritnu@gmail.com,linxmap@gmail.com,abisonjohn222@gmail.com,bahaduralam@gmail.com,
                smartybarlow@googlemail.com,gentleflower@ymail.com,subha@vipguyz.com,smaartmobile@gmail.com,jemmy_wu@hotmail.com,sales@appsfx.com,alex@sprite.net,razakiscleverboy@gmail.com,info@naughtycities.com,business@glamourfone.com,nimoniks@gmail.com,magazine@travellingwizards.co.uk,elbernoussi@live.fr,ratikant@migital.com,Fendi_com@hotmail.com,peter.a@janak.ch,asbclub77@gmail.com,techixoft@gmail.com,benjamin@webscape.fr,Hashim.thekkedanz@gmail.com,accounting@abzorbagames.com,niwito@gmail.com,complainappcom@gmail.com,commerce@pearmobile.com,testsstore@googlemail.com,welkaim@carlsonwagonlit.com,welcome@nevosoft.ru,android.marketing@nurogames.com,coolsaba2@yahoo.com,mroberts@metaflow.com,lizhihang@szboeye.com,contact@em-publish.com,info@foryourblackberry.com,cristached@gmail.com,paul@digitalleisure.com,support@fonesherpa.com,Ashyvenk@gmail.com,udrewicz@gmail.com,contact@kastorsoft.com,warg24@yahoo.co.uk,alexkirov.ok@gmail.com,ohoyoo@gmail.com,raunakwap.in83@yahoo.com,distributor@eye-watch.in,jannruus@gmail.com,steven.verbeek@crazymonkeystudios.com,matic.bitenc@toshl.com,jonasdonovan@gmail.com,
                omkarjoshi1986@gmail.com,sunny9495@gmail.com,fischerklas@gmail.com,jkangani@yahoo.com,4dsofttech@gmail.com,mospay@mail.com,maju.majidg@gmail.com,muang_li@rediffmail.com,appon.publish@gmail.com,en@baby-bus.com,friedger@gmail.com,kksancho@yahoo.com,mukobi@gmail.com,nadeer.netqin@gmail.com,ucwebasia@yahoo.in,acerapps01@gmail.com,srinivas.m@purpletalk.com,kalanvishwanath@gmail.com,info@appsfunder.com,darren.seet@gmail.com,pablo.reyas@kryptomail.net,davis.a.george@gmail.com,services@candelainfotech.com,vipgill786@yahoo.com,valeriy_skachko@ukr.net,gnassounouboris@gmail.com,mano.tech85@gmail.com,anruanjian@gmail.com,visakhvarghese@gmail.com,vipulparashar08@gmail.com,ware2llc@gmail.com,Arjun14350@yahoo.in,mandsaurbuildconprivateltd@yahoo.com,mytubelab@gmail.com,ilyaicegrigoryev@gmail.com,metaconnect@metaflow.com,wipapps@gmail.com,egdahl@gmail.com,drinklegit@gmail.com,kshitij@rechargestudio.com,systems@7seasent.com,naveedzanoon@hotmail.com,steve@advancedmobilesolutions.com,info@pressokentertainment.com,sherin.ms1@gmail.com,sayirin1@gmail.com,dit10c3-0567@my.sliit.lk,livetyping@gmail.com,mobiletuts4@googlemail.com,
                dsalanueva@cerberusteam.com,gehanb@wijeya.lk,kapil.raj@twistfuture.com,youcaowu@gmail.com,publishing@ideaworks3d.com,jay@digitalsoftwarehouse.com,klayrocha@gmail.com,apnamob@gmail.com,jismijobin@gmail.com,gulfimob@gmail.com,info@ilivemusic.mobi,ccounotte@gmail.com,gabnative@googlemail.com,dennis@rockislandgames.com,phong_cn@hotmail.com,tpqmobile@gmail.com,ipleanty@gmail.com,support@semusi.com,milan.vajda@plumlizard.com,gameshastrapublish@gmail.com,sree@schogini.com,go6game@gmail.com,imranhaidry@yahoo.com,ctechnoboy87@gmail.com,richard.achille@googlemail.com,aaronthomasirons@aol.com,sales@imagedirect.com.au,ul.sabit@gmail.com,tone.it.down.tigs@gmail.com,wwwsongspkpk@gmail.com,asem.gamil@gmail.com,gg4000@aol.com,jsivaguru@gmail.com,info@oneosolutions.com,tristit.apps@gmail.com,silvioapps@gmail.com,quasar.gamestudios@gmail.com,vgpprasad@rediffmail.com,rishubadar@gmail.com,frolova@cupid.com,hashimvavamon@gmail.com,drhu00@gmail.com,easy.browsers@gmail.com,tfox6464@gmail.com,forex2090@yahoo.com,apps@diwebtech.com,arunkam@gmail.com,bombilo2@narod.ru,ersin.gulbahar@gmail.com,mpiremobile@gmail.com,tristit.apps.turkey@gmail.com,
                tristit.apps.nederland@gmail.com,andrewchong@zoo-mobile.com,winmarketplace123@gmail.com,wangshimeng@gmail.com,olesyakin@u-f-t.com,emily@shoutz.com,info@callplanets.com,it@moonglabs.com,brian.oshaughnessy.bos@gmail.com,appwarehouse@q.com,ask@fuzd.com,eazyigz@gmail.com,lalaua99@gmail.com,x.pelya.x@gmail.com,pankajsengar5000@gmail.com,moatasem.m@genie9.com,apps@pixatel.com,charizard0008@gmail.com,shoby_ak@hotmail.com,totallyproducts@gmail.com,droiddev1982@gmail.com,nekto@hotmail.com,tharif.ta7@gmail.com,yeepeekayey@gmail.com,weerabahu@yahoo.com,localmediaapps@gmail.com,sachith.net@gmail.com,ftvapp2012@gmail.com,enlightenedapps@outlook.com,inquire@tbldevelopmentfirm.com,hmdmph@yahoo.com,julio_lemos@yahoo.com,kidga.games@gmail.com,shyamaldlr@myriadlk.com,tristit.apps.indonesian@gmail.com,die4vijay@gmail.com");
        $emails = explode(',', $aa);
        
        foreach($emails as $email) {
        sleep ( 1 );
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('Seasonal Greetings from - neXva... and thank you for your support.');
        $mailer->addTo($email);
        //    $mailer->addTo(explode(',', $config->nexva->application->dev->contact));
        $mailer->setLayout("generic_mail_template")
        ->sendHTMLMail('greetings.phtml');
        
        echo $email."<br>";
        
        }
        die();
    }
    
    
    public function royaltitestAction(){
        
        $amount = 30;
        
         $currencyUsers = new Api_Model_CurrencyUsers();
                
                $currencyDetails = $currencyUsers->getCurrencyDetailsByChap(21134);
 
                $amountInUsd = $amount/$currencyDetails['rate'];
                
                $amountInUsd = round($amountInUsd,2);
                

                $userAccount = new Model_UserAccount();
                
                $userAccount->saveRoyalitiesForInapp(45726, $amountInUsd, 'INAPP', 21134, null, 123);
        
    }
    
    
    private function __random_numbers($digits) {
    	$min = pow(10, $digits - 1);
    	$max = pow(10, $digits) - 1;
    	return mt_rand($min, $max);
    }
    
    public function testrandomAction()
    {
    	echo $this->__random_numbers(4);
    	die();
    }
    
    public function codengoAction()
    {
    	$aa = new Nexva_Util_Http_SendRequestCodengo;
    	$aa->send(47632);

    }
    
    
    
    public function congotestAction()
    {
    	$ss = new Nexva_MobileBilling_Type_Huaweinew();
    	$ss->sendsms('242064890768', 'test', $chapId);
    }
    


    public function sssAction()
    {

        $cache          = Zend_Registry::get('cache');
        $cacheKey       = 'PRODUCT_BASE_' . trim('ddddd');
        $cacheKey       = str_ireplace('-', '_', $cacheKey);

        Zend_Debug::Dump($cacheKey);
        $cache->remove($cacheKey);

        die();

    }

    public function ssssAction()
    {

        $cache          = Zend_Registry::get('cache');
        $cacheKey       = 'PRODUCT_BASE_' . trim('ddddd');
        $cacheKey       = str_ireplace('-', '_', $cacheKey);

        Zend_Debug::Dump($cacheKey);
        Zend_Debug::Dump($cache->get($cacheKey));
        echo 'aaa';
        if (($prod = $cache->get($cacheKey)) === false) {

            echo 'ddd';
            //get categories
            $prod['categories'] = 'cc';

            $prod['deleted']    = 'bb';

            $cache->set($prod, $cacheKey);




        }



        Zend_Debug::Dump($prod);

        die();



        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );

        $mobileNo = '08032006348';

        $amount = 150;
        $paymentTimeStamp = date('YmdHis');

        $paymentResult = 'Success';

        $client1 = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
        $client1->soap_defencoding = 'UTF-8';
        $client1->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

        $spId =  2340110000426;
        $pass = 'nexT321';

        $password = md5($spId.$pass.$timeStamp);

        $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => $mobileNo, 'FA' => $mobileNo));

        $phone = array(
            'addresses'     =>  'tel:'.$mobileNo,
            'senderName'   =>  99999998,
            'message'  => 'Hello, your account was charged '. $amount.'NGN at '.$paymentTimeStamp. ' for the successful transaction on the MTN Nextapps store. Thank you.'
        );

        $result = $client1->call('sendSms', $phone, '', '', $header);

        die($result);


        //$aa = new  Nexva_MobileBilling_Type_Huawei();
        //  $aa->doPayment(21134, 57945, 30828, '08032006348', 'test', '1');

        die();


        $message = 'SSSS SFD 353453b   trhrth rth rth ytj tyjyu jyujy jjy jyj yj.';

        $validator = new Zend_Validate_Alnum(array('allowWhiteSpace' => true));
        if($validator->isValid($message)) {
            echo 'b';
        } else {
            echo 'a';
        }

        die();
        /* Initialize action controller here */


    }




    public function userAgentAction() {


        $_SERVER['HTTP_USER_AGENT'];

        $to = 'chathura@nexva.com';
        $subject = "My subject";
        $txt = "Hello world!";
        $headers = "From: chathura@nexva.com" . "\r\n" ;//.
        //	"CC: shaun@nexva.com";

        mail($to,'User-Agent',$_SERVER['HTTP_USER_AGENT'],$headers);

        die('<b>Thank you</b> Your UA is :- '. $_SERVER['HTTP_USER_AGENT']);



    }


    public function ipTestAction() {

        //	$_SERVER['HTTP_USER_AGENT'];
 

        $to = 'chathura@nexva.com';
        $subject = "neXva test";
        $txt = "neXva test";
        $headers = "From: chathura@nexva.com" . "\r\n" .
            "CC: ";

     //   Zend_Debug::dump(apache_request_headers());
      //  Zend_Debug::dump($_SERVER['MSISDN'], 'X-MSISDN');
       // Zend_Debug::dump($_SERVER['msisdn'], 'msisdn');
        
        $headers = apache_request_headers();
        
        Zend_Debug::dump($_SERVER);
        Zend_Debug::dump($headers);

        //	$a = var_export($_SERVER);

        $dump = var_export($_SERVER, true);

        mail($to,'User-Agent',$dump,$headers);

        die('<b>Thank you</b>');



    }
    
    
    public function smsgbonAction() {
    
$dd = new Nexva_MobileBilling_Type_AirtelGabon();
$dd = $dd->sendsms('24107170274', 'test');

Zend_Debug::dump($dd);
die();
    
    
    }
    
    public function smszmAction() {
    
    	$dd = new Nexva_MobileBilling_Type_AirtelZm();
    	$dd = $dd->sendsms('24107170274', 'test');
    
    	Zend_Debug::dump($dd);
    	die();
    
    
    }



    public function smpp3Action() {

        include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/class.smpp.php' );

        $src = "3369570516"; // or text
        $dst = "336957051534534";
        $message = "Test Message";

        $s = new smpps;
        $s->debug=0;

        // $host,$port,$system_id,$password
        $s->open("pmg-acg-sms01.ref1.lightsurf.net", 8008, "1432","n3x41A99");



        // $source_addr,$destintation_addr,$short_message,$utf=0,$flash=0
        Zend_Debug::dump($s->send_long($src, $dst, $message));

        /* To send unicode
         $utf = true;
        $message = iconv('Windows-1256','UTF-16BE',$message);
        $s->send_long($src, $dst, $message, $utf);
        */

        $s->close();





        die();



    }
    
    public function testsmsciAction() {
        
       $ss = new Nexva_MobileBilling_Type_Huaweici();
       $ss->sendsms('22555913780', 'hello from neXva');
    }
    
    public function payciAction() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        
        
    	$ss = new Nexva_MobileBilling_Type_Huaweici();// 22555913780 // 
    $ss->addMobilePayment(110721, 39528, 71259, 22504844411, '0.01', 26);
    	$dd = $ss->doPayment(110721, 71259, 39528, 22504844411, 'Test App', '0.01');
    	
    	Zend_Debug::dump($dd);
    	
    	die('dd');
    }
    


    public function smpp2Action() {

        include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
        //  $smpp->bindTransmitter("1432","n3x41A99");

        // Optional connection specific overrides
        //pmg-acg-sms01.ref1.lightsurf.net

        print "<pre>";
        $tx=new SMPP('pmg-acg-sms01.ref1.lightsurf.net','8008');
        $tx->debug=true;
        // $tx->system_type="WWW";
        $tx->addr_npi=1;
        //print "open status: ".$tx->state."\n";
        Zend_Debug::dump( $tx->bindTransmitter("1432","n3x41A99"));


        $tx->sms_source_addr_npi=1;
        //$tx->sms_source_addr_ton=1;
        $tx->sms_dest_addr_ton=1;
        $tx->sms_dest_addr_npi=1;
        Zend_Debug::dump($tx->sendSMS("13369570516",'13369570515',"Hello world!"));
        $tx->close();
        unset($tx);
        print "</pre>";

        die('ddd');



    }

    public function testsmsAction() {

        $bb = new  Nexva_MobileBilling_Type_Huawei();

        $bb = $bb->sendSms('08103114401', 'test safasfas', $chapId=22);

        Zend_Debug::dump(   $bb );

        die();



    }


    public function testcwwAction() {


        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap(15267);

        $pgType = $pgDetails->gateway_id;
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        //Call Nexva_MobileBilling_Factory and create relevant instance
        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

        //$cc = $pgClass->sendsms('3369570515', 'test test', $chapId=22);

        $cc = $pgClass->doPayment(22, 37234, 15214, '3369570515', 'test', 1);

        Zend_Debug::dump($cc);



        //	$cc = $pgClass->sendsms('3369570515', 'test test', $chapId=22);

        //	Zend_Debug::dump($cc);




        die();



    }

    public function curlAction()
    {

        // $URL =  'http://api.mobilereloaded.com/huaweingn/sendsms/mobile_no/2347060553995/message/test';
        $URL =  'http://api.mobilereloaded.com/huaweingn/sendsms';

        $ch = curl_init($URL);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $fields = array(
            'mobile_no' => urlencode(2347060553995),
            'message' => urlencode('test')
        );

        $fields_string = '';

        //url-ify the data for the POST
        foreach($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }

        rtrim($fields_string, '&');

        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);


        $xml = json_decode(curl_exec($ch));

        Zend_Debug::dump($xml->status);
        curl_close($ch);

        die();


    }



    public function smppAction() {

        include_once( APPLICATION_PATH.'/../public/vendors/php-smpp/smppclient.class.php' );
        include_once( APPLICATION_PATH.'/../public/vendors/php-smpp/gsmencoder.class.php' );
        include_once( APPLICATION_PATH.'/../public/vendors/php-smpp/sockettransport.class.php' );


        // Construct transport and client
        //$transport = new SocketTransport(array('pmg-acg-sms01.ref1.lightsurf.net'),8008);

        $transport = new SocketTransport(array('pmg-acg-sms01.ref1.lightsurf.net'),'8008');



        $transport->setRecvTimeout(10000);
        $smpp = new SmppClient($transport);



        // Activate binary hex-output of server interaction
        $smpp->debug = true;
        $transport->debug = true;

        // Open the connection
        // $transport->open();
        $transport->open();

        $smpp->bindTransmitter("1432","n3x41A99");

        // Optional connection specific overrides
        SmppClient::$sms_null_terminate_octetstrings = false;
        SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
        SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;

        // Prepare message
        $message = 'Hello world';
        //  $encodedMessage = GsmEncoder::utf8_to_gsm0338($message);
        $encodedMessage = GsmEncoder::utf8_to_gsm0338($message);


        $from = new SmppAddress('13369570515', SMPP::TON_ALPHANUMERIC);
        $to = new SmppAddress('13369570515', SMPP::TON_INTERNATIONAL,SMPP::NPI_E164);



        // Send
        //  $smpp->sendSMS($from,$to,$encodedMessage,$tags);

        Zend_Debug::dump($smpp->sendSMS($from,$to,$encodedMessage));

        // Close connection
        $smpp->close();
        die('dd');

    }

    public function indexAction()
    {


        $mailer = new Nexva_Util_Mailer_Mailer();
        Zend_Debug::dump( $mailer->addTo('cj19820508@gmail.com', 'cj19820508@gmail.com')
            ->setSubject('Reset your neXva account password.')
            ->setLayout("generic_mail_template")
            ->setMailVar("name", 'chathura')
            ->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
            ->sendHTMLMail('forgotpassword.phtml'));


        die('dddd');

        $to = 'cj19820508@gmail.com';
        $subject = "My subject";
        $txt = "Hello world!";
        $headers = "From: cj19820508@gmail.com" . "\r\n" .
            "CC: chathura@nexva.com";

        Zend_Debug::dump(  mail($to,$subject,$txt,$headers));

        die();



        phpinfo();


        $to = 'cj19820508@gmail.com';
        $subject = "My subject";
        $txt = "Hello world!";
        $headers = "From: cj19820508@gmail.com" . "\r\n" .
            "CC: chathura@nexva.com";

        Zend_Debug::dump(  mail($to,$subject,$txt,$headers));

        Zend_Debug::dump(  mail('chathura@nexva.com',$subject,$txt,$headers));

        $mailer = new Nexva_Util_Mailer_Mailer();
        Zend_Debug::dump( $mailer->addTo('cj19820508@gmail.com', 'cj19820508@gmail.com')
            ->setSubject('Reset your neXva account password.')
            ->setLayout("generic_mail_template")
            ->setMailVar("name", 'chathura')
            ->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
            ->sendHTMLMail('forgotpassword.phtml'));


        die('dddd');
        phpinfo();

        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->addTo('cj19820508@gmail.com', 'cj19820508@gmail.com')
            ->setSubject('Verify your neXva account')
            ->setLayout("generic_mail_template")
            ->setMailVar("name",'chathura')
            ->setMailVar("email", 'cj19820508@mail.com')
            ->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
            ->sendHTMLMail('verify_account_cpbo.phtml');

        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->addTo('cj19820508@gmail.com', 'cj19820508@gmail.com')
            ->setSubject('Reset your neXva account password.')
            ->setLayout("generic_mail_template")
            ->setMailVar("name", 'chathura')
            ->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
            ->sendHTMLMail('forgotpassword.phtml');




        $to = 'cj19820508@gmail.com';
        $subject = "My subject";
        $txt = "Hello world!";
        $headers = "From: cj19820508@gmail.com" . "\r\n" .
            "CC: chathura@nexva.com";

        mail($to,$subject,$txt,$headers);



        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        // initialize SOAP client
        $options = array(
            'location' => 'http://pmg-acg-mdn-validate.ref1.lightsurf.net',
            'uri'      => 'http://pmg-acg-mdn-validate.ref1.lightsurf.net'
        );

        try {
            // add a new product
            // get and display product ID
            $user = array('aggregatorId' => '1432', 'pwd' => 'n3x41A99');

            $p = array(
                'user'     => $user,
                'version' => '1.0',
                'min'     => 15403057987,
                'productId'  => 'xxx_nt_999',
                'shortCode' => 'xxxx',
                'carrierId' => 401,
                'sendUAProf' => 'Y',
                'sendSpendingCapability' => 'Y'
            );

            $client = new Zend_Soap_Client('', $options);
            $id = $client->validateMDN($p);

            Zend_Debug::dump(Zend_Soap_Client::getLastRequest());


            echo 'Added product with ID';
        } catch (SoapFault $s) {
            die('ERROR: [' . $s->faultcode . '] ' . $s->faultstring);
        } catch (Exception $e) {

            Zend_Debug::dump($e->getTraceAsString());
            Zend_Debug::dump( $e->getMessage());


            die('ERROR: ' . $e->getMessage());
        }



    }





    public function validateMdnAction() {

        $err = '';


        //$client = new nusoap_client('http://pmg-acg-mdn-validate.ref1.lightsurf.net/pmgvalidate/services/validate');


        //  $client = new nusoap_client('http://pmg-acg-mdn-validate.verisign-grs.net/pmgvalidate/services/validate');

        $client = new nusoap_client('http://pmg-acg-validate.syniverse.com/pmgvalidate/services/validate');

        //  $client = new nusoap_client('http://pmg-acg-mdn-validate.verisign-grs.net/pmgvalidate/services/validate');


        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;


        $user = array('aggregatorId' => 1423, 'pwd' => 'n3x41A99', 'version' => '1.0');


        //3369570515

        //18042161341

        //

        $p = array(
            'user'     => $user,
            'min'     => '13369570515',
            'productId'  => 'nexva_nt_059',
            'shortCode' => '6171',
            'carrierId' => 404,
            'sendUAProf' => 'Y',
            'sendSpendingCapability' => 'Y'
        );

        $result = $client->call('validateMDN', $p);


        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {
            $err = $client->getError();
            if ($err) {
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            } else {
                echo '<h2>Result</h2><pre>';
                // Decode the result: it so happens we sent Latin-1 characters
                if (isset($result['return'])) {
                    $result1 = utf8_decode($result['return']);
                } elseif (!is_array($result)) {
                    $result1 = utf8_decode($result);
                } else {
                    $result1 = $result;
                }
                print_r($result1);
                echo '</pre>';
            }
        }


        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2>';


        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            echo '<h2>Debug</h2>';
            echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';

        }

        die();
        Zend_Debug::dump($client->getDebug());
    }
    
    public function mtnnigeriatestAction() {
        

        
       $ss= new Nexva_MobileBilling_Type_Huawei();
       $ss->sendsms('08100504589', 'test', $chapId = null);
    }
    
    public function mtnnigeriatesAction() {
    
        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(-1);
    
    	$ss= new Nexva_MobileBilling_Type_Huawei();
    	Zend_Debug::dump( $ss->sendsms('08100504589', 'test', $chapId = null));
    	die('dd');
    }
    


    public function chargeSubscriberAction() {


        $err = '';

        //http://pmg-acg-mdn-validate.verisign-grs.net

        //$client = new nusoap_client('http://pmg-acg-billing.ref1.lightsurf.net/pmgxml/services/premiumcharging');

        //$client = new nusoap_client('http://pmg-acg-billing.verisign-grs.net/pmgxml/services/premiumcharging');

        $client = new nusoap_client('http://pmg-acg-billing.syniverse.com/pmgxml/services/premiumcharging');

        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;


        $user = array('aggregatorId' => 1423, 'pwd' => 'n3x41A99', 'version' => '1.0');

        $p = array(
            'user'     => $user,
            'min'     => '13364013614',
            'productId'  => 'nexva_nt_100',
            'shortCode' => '6171',
            'carrierId' => 404,
            'chargeId' => '114'.date("Ymd").date("His"),
            'msgtxid' => 'NOID'
        );

        $result = $client->call('chargeSubscriber', $p);


        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {
            $err = $client->getError();
            if ($err) {
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            } else {
                echo '<h2>Result</h2><pre>';
                // Decode the result: it so happens we sent Latin-1 characters
                if (isset($result['return'])) {
                    $result1 = utf8_decode($result['return']);
                } elseif (!is_array($result)) {
                    $result1 = utf8_decode($result);
                } else {
                    $result1 = $result;
                }
                print_r($result1);
                echo '</pre>';
            }
        }


        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2>';


        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            echo '<h2>Debug</h2>';
            echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';

        }

        die();
        Zend_Debug::dump($client->getDebug());
    }



    public function chargeSubscriberMtnTestCaseTestbedAction() {

        $err = '';

        $client = new nusoap_client('http://41.206.4.219:8310/AmountChargingService/services/AmountCharging');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

        $spId = 2340110000211;
        $pass = 'nexT123';

        $password = md5($spId.$pass.$timeStamp);

        $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '', 'timeStamp' => $timeStamp, 'OA' => '2347060553995'));

        //<v2:OA>2347060553995</v2:OA>
        //<v2:FA>2347060553995</v2:FA>

        $charge = array(

            'description' => 'this is test',
            'currency' => 'NGN',
            'amount' => 100,
            'code' => 10090
        );


        //2348034571296

        $paymentInfo = array(
            'endUserIdentifier'     => '2348034571296',
            'charge'   => $charge,
            'referenceCode'  => 'text123'
        );

        $paymentInfo = array(
            'endUserIdentifier'     => '2348034571296',
            'charge'   => $charge,
            'referenceCode'  => 'text123'
        );

        $result = $client->call('chargeAmount', $paymentInfo, '', '', $header);


        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            print_r('dddddddd');
            echo '</pre>';

            echo '<h2>FaultT</h2><pre>';

            Zend_Debug::dump($result["faultcode"]);


        } else {
            $err = $client->getError();
            if ($err) {
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            } else {
                echo '<h2>Result</h2><pre>';
                // Decode the result: it so happens we sent Latin-1 characters
                if (isset($result['return'])) {
                    $result1 = utf8_decode($result['return']);
                } elseif (!is_array($result)) {
                    $result1 = utf8_decode($result);
                } else {
                    $result1 = $result;
                }
                print_r($result1);
                echo '</pre>';
            }
        }


        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2>';




        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            echo '<h2>Debug</h2>';
            echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';

        }

        die();

    }


    public function sendSmsProductionAction() {

        $err = '';

        $client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

        $spId =  2340110000426;
        $pass = 'nexT321';

        //08032001535

        $password = md5($spId.$pass.$timeStamp);

        $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => '2347060553995', 'FA' => '2347060553995'));

        //'addresses'     =>  'tel:2347060553995',


        $phone = array(
            'addresses'     =>  'tel:08032001535',
            'senderName'   =>  99999998,
            'message'  => 'Hello from nexva'
        );

        $result = $client->call('sendSms', $phone, '', '', $header);


        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {
            $err = $client->getError();
            if ($err) {
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            } else {
                echo '<h2>Result</h2><pre>';
                // Decode the result: it so happens we sent Latin-1 characters
                if (isset($result['return'])) {
                    $result1 = utf8_decode($result['return']);
                } elseif (!is_array($result)) {
                    $result1 = utf8_decode($result);
                } else {
                    $result1 = $result;
                }
                print_r($result1);
                echo '</pre>';
            }
        }


        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2>';


        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            echo '<h2>Debug</h2>';
            echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';

        }

        die();

    }


    public function sendSmsProductionAbcAction() {



        $err = '';

        $client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

        $spId =  2340110000426;
        $pass = 'nexT321';

        $password = md5($spId.$pass.$timeStamp);



        $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => '2347060553995', 'FA' => '2347060553995'));

        $phone = array(
            'addresses'     =>  'tel:2347060553995',
            'senderName'   =>  99999998,
            'message'  => 'MTN Nigeria ABC '
        );

        $result = $client->call('sendSms', $phone, '', '', $header);




        if ($client->fault) {
            //echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
        } else {

            $err = $client->getError();
            //Zend_Debug::dump($client->getDebug());

            if ($err) {
                //Zend_Debug::dump($err);
                //Zend_Debug::dump($client->getDebug());

            } else {
                Zend_Debug::dump($result);
            }

        }
        //	Zend_Debug::dump($result);



        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2>';

        die('ddd');
    }


    public function sendSmsProduction2Action() {



        $err = '';

        $client = new nusoap_client('http://41.206.4.162:8310/SendSmsService/services/SendSms');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

        $spId =  2340110000426;
        $pass = 'nexT321';

        $password = md5($spId.$pass.$timeStamp);



        $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001929',  'timeStamp' => $timeStamp, 'OA' => '08032001535', 'FA' => '08032001535'));

        //    			'addresses'     =>  'tel:08063805368',
        //	+2348032001535
        $phone = array(
            'addresses'     =>  'tel:08032001535',
            'senderName'   =>  99999998,
            'message'  => 'MTN Nigeria ABC '
        );

        $result = $client->call('sendSms', $phone, '', '', $header);




        if ($client->fault) {
            //echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
        } else {

            $err = $client->getError();
            Zend_Debug::dump($client->getDebug());

            if ($err) {
                //Zend_Debug::dump($err);
                //Zend_Debug::dump($client->getDebug());

            } else {
                Zend_Debug::dump($result);
            }

        }
        //	Zend_Debug::dump($result);



        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2>';

        die('ddd');
    }




    public function refundAction() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );

        $err = '';

        $client = new nusoap_client('http://41.206.4.162:8310/AmountChargingService/services/AmountCharging');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

        $spId =  2340110000426;

        $pass = 'nexT321';

        $password = md5($spId.$pass.$timeStamp);

        $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001927',  'timeStamp' => $timeStamp, 'OA' => '2347060553995', 'FA' => '2347060553995'));

        $charge = array(

            'description' => 'this is test',
            'currency' => 'NGN',
            'amount' => 1000,
            'code' => 10090
        );



        $paymentInfo = array(
            'endUserIdentifier'     => '2347060553995',
            'charge'   => $charge,
            'referenceCode'  => 'text123'
        );

        $result = $client->call('chargeAmount', $paymentInfo, '', '', $header);
        //$result = $client->call('refundAmount', $paymentInfo, '', '', $header);

        Zend_Debug::dump($result);die();





        if ($client->fault) {
            //echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
        } else {

            $err = $client->getError();
            //Zend_Debug::dump($client->getDebug());

            if ($err) {
                //Zend_Debug::dump($err);
                //Zend_Debug::dump($client->getDebug());

            } else {
                Zend_Debug::dump($result);
            }

        }
        //	Zend_Debug::dump($result);



        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2>';

        die('ddd');
    }



    public function chargeSubscriberMtnTestCaseProductionAction() {

        $err = '';

        $client = new nusoap_client('http://41.206.4.162:8310/AmountChargingService/services/AmountCharging');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

        $spId =  2340110000426;
        $pass = 'nexT321';



        //	'serviceId' => '234012000001927',

        //	'serviceId' =>	234012000001927',

        //'serviceId' => '234012000001923',

        $password = md5($spId.$pass.$timeStamp);

        $header = array('RequestSOAPHeader' => array ( 'spId' => $spId, 'spPassword' => $password, 'serviceId' => '234012000001927', 'timeStamp' => $timeStamp, 'OA' => '08103114401', 'FA' => '08103114401'));

        $charge = array(

            'description' => 'this is test',
            'currency' => 'NGN',
            'amount' => 400,
            'code' => 10090
        );


        //'endUserIdentifier'     => '2347060553995',
        //	'endUserIdentifier'     => '08103114401',
        //  	        'endUserIdentifier'     => '8032001535',

        $paymentInfo = array(

            'endUserIdentifier'     => '08103114401',
            'charge'   => $charge,
            'referenceCode'  => '23124'
        );



        $result = $client->call('chargeAmount', $paymentInfo, '', '', $header);


        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {
            $err = $client->getError();
            if ($err) {
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            } else {
                echo '<h2>Result</h2><pre>';
                // Decode the result: it so happens we sent Latin-1 characters
                if (isset($result['return'])) {
                    $result1 = utf8_decode($result['return']);
                } elseif (!is_array($result)) {
                    $result1 = utf8_decode($result);
                } else {
                    $result1 = $result;
                }
                print_r($result1);
                echo '</pre>';
            }
        }


        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2>';


        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            echo '<h2>Debug</h2>';
            echo '<pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';

        }

        die();

    }




    public function testAbcAction () {



        $client = new nusoap_client('http://pmg-acg-mdn-validate.ref1.lightsurf.net/pmgvalidate/services/validate');

        $user = array('aggregatorId' => '1432', 'pwd' => 'n3x41A99');

        $p = array(
            'user'     => $user,
            'version' => '1.0',
            'min'     => 5403057987,
            'productId'  => 'xxx_nt_999',
            'shortCode' => 'xxxx',
            'carrierId' => 401,
            'sendUAProf' => 'Y',
            'sendSpendingCapability' => 'Y'
        );



        $result = $client->call('validateMDN', $p);
        // Display the result
        // print_r($result);
        Zend_Debug::dump($result);
        Zend_Debug::dump($err = $client->getError());

        die();


    }


    /**
     * Send apple push notifications through apple push notification service (APNS)
     * @param - $message notification message
     * @param - $device_token device token
     */
    private function send_notifications_to_apple($device_token, $message)
    {
        $apns_host_pro = 'gateway.push.apple.com';
        $apns_host_dev = 'gateway.sandbox.push.apple.com';

        $apns_port = 2195;

        $apns_cert_pro = './resources/certificates/pushPemProduction.pem';
        $apns_cert_dev = './resources/certificates/pushPemDevelopment.pem';

        $apns_password_pro = 'art#23GoV!Pro';
        $apns_password_dev = 'art#23GoV!';

        //create payload body
        $payload['aps'] = array('alert' => $message, 'badge' => '', 'sound' => 'default');
        $payload = json_encode($payload);

        $stream_context = stream_context_create();
        stream_context_set_option($stream_context, 'ssl', 'local_cert', $apns_cert_pro);
        stream_context_set_option($stream_context, 'ssl', 'passphrase', $apns_password_pro);

        // Open a connection to the APNS server
        // remove 'sandbox' in production server
        // Please make sure the port number 2195 is open in your server
        $apns = stream_socket_client('ssl://' . $apns_host_pro . ':' . $apns_port, $error, $error_string,
            60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $stream_context);
        if(!$apns)
        {
            return false;
            exit();
        }

        // Build the binary notification
        $apns_message = chr(0) .  pack('n', 32) . pack('H*', str_replace(' ', '', $device_token)) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $reuslt = fwrite($apns, $apns_message, strlen($apns_message));

        // Close the connection
        socket_close($apns);
        fclose($apns);

        return true;

    }


    public function testCurlAction()
    {

        //Initialize handle and set options
        $username = 'metaconnect@metaflow.com1';
        $password = 'strip0';

        $payloadName = array('ddd' => 'ddd');

        $path_to_log_file = APPLICATION_PATH . "/../corn/provision.zip";

        // $file_name_with_full_path = '';

        $post['provisions'] = '@'.$path_to_log_file;


        $process = curl_init('http://api.nexva.com/metaflow/submit');
        //curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
        // curl_setopt($process, CURLOPT_HEADER, 1);
        curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($process, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, $post);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
        $return = curl_exec($process);


        Zend_Debug::dump( $return );
        die();


        $request_xml = null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.nexva.com/metaflow/submit ');
        curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_xml);
        //  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));

        //Execute the request and also time the transaction ( optional )
        $start = array_sum(explode(' ', microtime()));
        $result = curl_exec($ch);
        $stop = array_sum(explode(' ', microtime()));
        $totalTime = $stop - $start;

        //Check for errors ( again optional )
        if ( curl_errno($ch) ) {
            $result = 'ERROR -> ' . curl_errno($ch) . ': ' . curl_error($ch);
        } else {
            $returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            switch($returnCode){
                case 200:
                    break;
                default:
                    $result = 'HTTP ERROR -> ' . $returnCode;
                    break;
            }
        }

        //Close the handle
        curl_close($ch);

        //Output the results and time
        echo 'Total time for request: ' . $totalTime . "\n";
        echo $result;


    }

    public function test2Action() {

        $tt = '0.99';

        $amount = 100 * $tt;

        $amount = str_pad($amount, 3, '0', STR_PAD_LEFT);


        Zend_Debug::dump( $amount);

        die();

        $aa = new Nexva_MobileBilling_Type_Syniverse();
        $buildUrl =  $aa->doPayment(15267, 38652, 16294, '13369570515', 'ddddddd', '1.00');

        Zend_Debug::dump($buildUrl );

        die('dd');


    }


    public function deviceAction() {
        
        

        ini_set('display_startup_errors',1);
        ini_set('display_errors',1);
     
 
        $deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
        $deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
        //If this is not a wireless device redirect to the main site
        $isWireless = $deviceInfo->is_mobile_device;
        

        	Zend_Debug::dump( $deviceInfo,'ddd');die();
        	die();
   
        /*
        /*
         $deviceModel = new Model_Device();
        // Check whether this is a device previosly detected by the WURFL if the use WURFL
        // check for neXva device table for user agnet
        $row = $deviceModel->fetchRow("useragent = '".$_SERVER['HTTP_USER_AGENT']."' and detection_type = 'wurfl'");
        
        if(!is_null($row)) {
        
        $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
        $exactMatch = $deviceDetector->detectDeviceByUserAgent();
        //If this is not a wireless device redirect to the main site
        $isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');
        
        // get properties from the Wurfl
        $brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');
        $modelName = $deviceDetector->getDeviceAttribute('model_name', 'product_info');
        $marketing_name = $deviceDetector->getDeviceAttribute('marketing_name', 'product_info');
        $inputMethod = $deviceDetector->getDeviceAttribute('pointing_method', 'product_info');
        $osVersion = $deviceDetector->getDeviceAttribute('device_os_version', 'product_info');
        $isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');
        //get nexva device Id
        $deviceId = $deviceDetector->getNexvaDeviceId();
        
        
        
        
        
        } else {
        
        $deviceDetection =  Nexva_DeviceDetection_Adapter_HandsetDetection::getInstance();
        $deviceInfo = $deviceDetection->getNexvaDeviceId($_SERVER['HTTP_USER_AGENT']);
        //If this is not a wireless device redirect to the main site
        $isWireless = $deviceInfo->is_mobile_device;
        
        
        // get properties from the Wurfl
        $brandName = $deviceInfo->brand;
        $modelName = $deviceInfo->model;
        $marketing_name = $deviceInfo->marketing_name;
        $inputMethod = $deviceInfo->pointing_method;
        $osVersion = $deviceInfo->device_os_version;
        $exactMatch = $deviceInfo;
        //get nexva device Id
        $deviceId = $deviceInfo->id;
        $isWireless = $deviceInfo->is_mobile_device;
        
        
        
        
        
        }
        
        
        Zend_Debug::dump($isWireless);
        Zend_Debug::dump($deviceId);
        die();
        
        */

        $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();
        $exactMatch = $deviceDetector->detectDeviceByUserAgent();
        //If this is not a wireless device redirect to the main site
        $isWireless = $deviceDetector->getDeviceAttribute('is_wireless_device', 'product_info');
        
        echo $isWireless;

        die('dddd');
        // get properties from the Wurfl
        $brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info');
        $modelName = $deviceDetector->getDeviceAttribute('model_name', 'product_info');
        $marketing_name = $deviceDetector->getDeviceAttribute('marketing_name', 'product_info');
        $inputMethod = $deviceDetector->getDeviceAttribute('pointing_method', 'product_info');
        $osVersion = $deviceDetector->getDeviceAttribute('device_os_version', 'product_info');
        //get nexva device Id
        $deviceId = $deviceDetector->getNexvaDeviceId();

        // Set device id and name to session
        $deviceSession->deviceId = $deviceId;
        $model = !empty($marketing_name) ? $marketing_name : $modelName;
        $deviceSession->deviceName = $brandName . ' ' . $model;
        $deviceSession->inputMethod = $inputMethod;
        $deviceSession->isWireless = $isWireless;
        $deviceSession->exactMatch = $exactMatch;

        if ($osVersion) {
            $deviceSession->osVersion = $osVersion;

        }

        Zend_Debug::dump($deviceSession);
        Zend_Debug::dump($deviceId);

        die();
        
        /*
    }


    public function testmAction() {

        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->addTo('cj19820508@mail.com', 'cj19820508@mail.com')
            ->setSubject('Verify your neXva account')
            ->setLayout("generic_mail_template")
            ->setMailVar("name",'chathura')
            ->setMailVar("email", 'cj19820508@mail.com')
            ->setMailVar("resetlink", 'sdfsfsdfsdfjosdpgjdpogjposdg')
            ->sendHTMLMail('verify_account_cpbo.phtml');

    }


    public function testCWWCAction() {


    }

    public function avgTestAction()
    {

        //$client = new Zend_Http_Client('http://scan.avgmobilation.com/scan');
        $client = new Zend_Http_Client('http://scan.avgmobilation.com/');
        $client->setHeaders(array(
            'Content-Type' => 'application/octet-stream',
            'scanKey' => 'bnn738459949320599483'));

        $client->setFileUpload('../files/upload/Nexva-mtn.apk','loadApk');
        $client->request(Zend_Http_Client::POST);
        echo $client->getLastResponse();
        //echo $client->getmessage();
        //$response = $client->request('POST');
        //Zend_Debug::dump($response);
        die();



        //$client = new Zend_Http_Client('http://scan.avgmobilation.com/');

        //$client->setHeaders('Content-Type','application/octet-stream');
        //$client->setHeaders('scanKey','bnn738459949320599483');

        /*$client->setHeaders(array(
         'Content-Type' => 'application/octet-stream',
                'scanKey' => 'bnn738459949320599483'));*/

        //$client->setFileUpload('../files/upload/Nexva-mtn.apk','loadApk');
        //$client->request(Zend_Http_Client::POST);
        //$response = $client->request('GET');
        //echo $response->getMessage();
        //Zend_Debug::dump($response);
        //echo $client->getLastResponse();

        //die();
    }


    function avgTesttAction()
    {

        $awsKey = 'AKIAIB7MH7NAQK55BKOQ';
        $awsSecretKey = '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';
        $bucketName = 'production.applications.nexva.com';

        $s3 = new Zend_Service_Amazon_S3($awsKey, $awsSecretKey);

        $data = $s3->getObject($bucketName.'/productfile/0/cccc.txt');


        file_put_contents( APPLICATION_PATH.'/../files/test.txt', $data);

        $file = fopen("../files/testst.txt","w");
        fwrite($file,"Hello World. Testing!");
        fclose($file);

        echo  file_get_contents('../files/testst.txt');

        // echo $data;
        die();

        $list = $s3->getObjectsByBucket($bucketName);
        foreach($list as $name) {
            $i++;
            echo Zend_Debug::dump($name);
            //  $data = $s3->getObject($bucketName.'productfile/100/Spades_signed_S60v3_OTA_002.sis');
            //  echo "with data: $data\n";
            if($i > 1000) exit;



            $client = new Zend_Http_Client('http://scan.avgmobilation.com/');
            $client->setHeaders(array(
                'Content-Type' => 'application/octet-stream',
                'scanKey' => 'bnn738459949320599483'));
            $client->setFileUpload('../files/upload/Nexva-mtn.apk','loadApk');
            $client->request(Zend_Http_Client::POST);
            if(!is_null($client->getLastResponse()))
            {
                return $client->getLastResponse();
            }
            else
            {
                return 'Try Again Later!';



            }







            //Zend_Debug::dump($response);



            $bucket = 'my-bucket' . strtolower('AKIAIB7MH7NAQK55BKOQ');

            $file_resource = fopen('large_video.mov', 'w+');

            $response = $s3->get_object($bucket, 'large_video.mov', array(
                'fileDownload' => $file_resource
            ));

            // Success?
            var_dump($response->isOK());


            /*$client = new Zend_Http_Client('http://scan.avgmobilation.com/');
             $client->setHeaders(array(
                     'Content-Type' => 'application/octet-stream',
                     'scanKey' => 'bnn738459949320599483'));
            $client->setFileUpload('../files/upload/Nexva-mtn.apk','loadApk');
            $client->request(Zend_Http_Client::POST);
            if(!is_null($client->getLastResponse()))
            {
            return $client->getLastResponse();
            }
            else
            {
            return 'Try Again Later!';
            }*/

        }

    }

    function mobileAction()
    {
        $validator = new Zend_Validate_EmailAddress();

        Zend_Debug::dump($validator->isValid('bayop@pledge51.com'));

        die();


        if(!($validator->isValid('bayop@pledge51.com')))

            echo @$_SERVER['HTTP_MSISDN'].'#';
        echo @$_SERVER['REMOTE_ADDR'].'<br/>';
        die();
    }

    public function getSessionAction() {
        $sessionUser = new Zend_Session_Namespace('partner_user');
        $sessionUser->id = 111531;
        echo $sessionId = Zend_Session::getId();
        print_r($_SESSION);
        echo 'test';
        die();
    }



    public function nusoapAction()
    {

        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        
        error_reporting(E_ALL ^ E_NOTICE);
        ini_set('soap.wsdl_cache_enabled',0);
        ini_set('soap.wsdl_cache_ttl',0);
        
        
        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

       // $client = new nusoap_client('https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort', false);
        $client = new nusoap_client('https://AgUser:AgUser@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort', false);
        
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

     //   0735285444  250734014536
        $msg = '<?xml version="1.0" encoding="UTF-8"?>
	    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
   <soapenv:Header/>
   <soapenv:Body>
      <char:charge>
         <inputMsg>
            <operation>debit</operation>
            <userId>250735285444</userId>
            <contentId>111</contentId>
            <itemName>SRKPic</itemName>
            <contentDescription>Apps</contentDescription>
            <circleId/>
            <lineOfBusiness/>
            <customerSegment/>
            <contentMediaType>Apps</contentMediaType>
            <serviceId>1</serviceId>
            <parentId/>
            <actualPrice>70</actualPrice>
            <basePrice>0</basePrice>
            <discountApplied>0</discountApplied>
            <paymentMethod/>
            <revenuePercent/>
            <netShare>0</netShare>
            <cpId>AIRTELAPPSSTORERW</cpId>
            <customerClass/>
            <eventType>Content Purchase</eventType>
            <localTimeStamp/>
            <transactionId/>
            <subscriptionName>SRKPic</subscriptionName>
            <parentType/>
            <deliveryChannel>sms</deliveryChannel>
            <subscriptionTypeCode>abcd</subscriptionTypeCode>
            <subscriptionExternalId>2</subscriptionExternalId>
            <contentSize/>
            <currency>RWF</currency>
            <copyrightId>xxx</copyrightId>
            <cpTransactionId>201313121113_256759542190</cpTransactionId>
            <copyrightDescription>copyright</copyrightDescription>
            <sMSkeyword>sms</sMSkeyword>
            <srcCode>abcd</srcCode>
            <contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl>
            <subscriptiondays>1</subscriptiondays>
         </inputMsg>
      </char:charge>
   </soapenv:Body>
</soapenv:Envelope>';



        $result=$client->send($msg, 'https://AgUser:AgUser@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort');

        //    echo '<br/><br/>'.$client->request.'<br/><br/>';

        // echo $client->response;

        Zend_Debug::dump($result);
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
         echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';


        $err = $client->getError();
        if ($err) {
            //echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        }


        // echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        //  echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        //  echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';


        //   die();




        die();
    }
    
    public function soap2Action(){
        
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
        
        $spEndPoint =  'https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort';
        
        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
        
        $msg = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
        <soapenv:Header/>
        <soapenv:Body>
        <char:charge>
        <inputMsg>
        <operation>debit</operation>
        <userId>250735285444</userId>
        <contentId>111</contentId>
        <itemName>SRKPic</itemName>
        <contentDescription>Apps</contentDescription>
        <circleId/>
        <lineOfBusiness/>
        <customerSegment/>
        <contentMediaType>Apps</contentMediaType>
        <serviceId>1</serviceId>
        <parentId/>
        <actualPrice>70</actualPrice>
        <basePrice>0</basePrice>
        <discountApplied>0</discountApplied>
        <paymentMethod/>
        <revenuePercent/>
        <netShare>0</netShare>
        <cpId>AIRTELAPPSSTORERW</cpId>
        <customerClass/>
        <eventType>Content Purchase</eventType>
        <localTimeStamp/>
        <transactionId/>
        <subscriptionName>SRKPic</subscriptionName>
        <parentType/>
        <deliveryChannel>sms</deliveryChannel>
        <subscriptionTypeCode>abcd</subscriptionTypeCode>
        <subscriptionExternalId>2</subscriptionExternalId>
        <contentSize/>
        <currency>RWF</currency>
        <copyrightId>xxx</copyrightId>
        <cpTransactionId>201313121113_256759542182</cpTransactionId>
        <copyrightDescription>copyright</copyrightDescription>
        <sMSkeyword>sms</sMSkeyword>
        <srcCode>abcd</srcCode>
        <contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl>
        <subscriptiondays>1</subscriptiondays>
        </inputMsg>
        </char:charge>
        </soapenv:Body>
        </soapenv:Envelope>';
        
        $result = $client->send($msg, 'https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort');
        //Zend_Debug::dump($result);die();
       Zend_Debug::dump($result);
         //echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        //echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        //echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
        
        die();
        
    }
    
    public function soapnigerAction(){

    
    	error_reporting(E_ALL);
    	ini_set('display_errors', 1);
    
    	include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
    
    	$spEndPoint =  'https://NEXVA_NE:NEXVA_NE!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1';
    
    	$client = new nusoap_client($spEndPoint);
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$msg = '<?xml version="1.0" encoding="UTF-8"?>
    	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
    	<soapenv:Header/>
    	<soapenv:Body>
    	<char:charge>
    	<inputMsg>
    	<operation>debit</operation>
    	<userId>22796952191</userId>
    	<contentId>1</contentId>
    	<itemName>SRKPic</itemName>
    	<contentDescription>Content Purchase</contentDescription>
    	<circleId/>
    	<lineOfBusiness/>
    	<customerSegment/>
    	<contentMediaType>Apps</contentMediaType>
    	<serviceId></serviceId>
    	<parentId/>
    	<actualPrice>200</actualPrice>
    	<basePrice>0</basePrice>
    	<discountApplied>0</discountApplied>
    	<paymentMethod/>
    	<revenuePercent/>
    	<netShare>0</netShare>
    	<cpId>NEXVA_NE</cpId>
    	<customerClass/>
    	<eventType>Content Purchase</eventType>
    	<localTimeStamp/>
    	<transactionId/>
    	<subscriptionName>SRKPic</subscriptionName>
    	<parentType/>
    	<deliveryChannel>sms</deliveryChannel>
    	<subscriptionTypeCode>abcd</subscriptionTypeCode>
    	<subscriptionExternalId>2</subscriptionExternalId>
    	<contentSize/>
    	<currency>XOF</currency>
    	<copyrightId>xxx</copyrightId>
    	<cpTransactionId>201313121113_256759542182</cpTransactionId>
    	<copyrightDescription>copyright</copyrightDescription>
    	<sMSkeyword>sms</sMSkeyword>
    	<srcCode>abcd</srcCode>
    	<contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl>
    	<subscriptiondays>1</subscriptiondays>
    	</inputMsg>
    	</char:charge>
    	</soapenv:Body>
    	</soapenv:Envelope>';
    	
    	//22796686660
    	   	$msg = '<?xml version="1.0" encoding="UTF-8"?>
    	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
    	<soapenv:Header/>
    	<soapenv:Body>
    	<char:charge>
    	<inputMsg>
    	<operation>debit</operation>
    	<userId>22796884045</userId>
    	<contentId>100</contentId>
    	<itemName>APPS</itemName>
    	<contentDescription>TEST</contentDescription>
    	<circleId></circleId>
    	<lineOfBusiness></lineOfBusiness>
    	<customerSegment></customerSegment>
    	<contentMediaType>AIRTELAPPSSTORERW</contentMediaType>
    	<serviceId></serviceId>
    	<parentId></parentId>
    	<actualPrice>400</actualPrice>
    	<basePrice>0</basePrice>
    	<discountApplied>0</discountApplied>
    	<paymentMethod></paymentMethod>
    	<revenuePercent></revenuePercent>
    	<netShare></netShare>
    	<cpId>NEXVA_NE</cpId>
    	<customerClass></customerClass>
    	<eventType>Content Purchase</eventType>
    	<localTimeStamp></localTimeStamp>
    	<transactionId></transactionId>
    	<subscriptionTypeCode>1</subscriptionTypeCode>
    	<subscriptionName>TEST</subscriptionName>
    	<parentType></parentType>
    	<deliveryChannel>sms</deliveryChannel>
    	<subscriptionExternalId>AIRTEL</subscriptionExternalId>
    	<contentSize></contentSize>
    	<currency>XOF</currency>
    	<copyrightId>1234</copyrightId>
    	<sMSkeyword>123</sMSkeyword>
    	<srcCode>121</srcCode>
    	<contentUrl>http://www.airtel.in</contentUrl>
    	<subscriptiondays>1</subscriptiondays>
    	<!--Optional:-->
    	<cpTransactionId></cpTransactionId>
    	<copyrightDescription></copyrightDescription>
    	</inputMsg>
    	</char:charge>
    	</soapenv:Body>
    	</soapenv:Envelope>';
    	 
    
    	$result=$client->send($msg, 'https://NEXVA_NE:NEXVA_NE!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1');
    	//Zend_Debug::dump($result);die();
    	Zend_Debug::dump($result);
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
    
    	die();
    
    }
    
    
    public function soapgabonAction(){
    
    	error_reporting(E_ALL);
    	ini_set('display_errors', 1);
    
    	include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
    
    	$spEndPoint =  'https://AgUser:AgUser@154.0.177.3:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort';
    
    	$client = new nusoap_client($spEndPoint);
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$msg = '<?xml version="1.0" encoding="UTF-8"?>
    	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
    	<soapenv:Header/>
    	<soapenv:Body>
    	<char:charge>
    	<inputMsg>
    	<operation>debit</operation>
    	<userId>24104187540</userId>
    	<contentId>1</contentId>
    	<itemName>SRKPic</itemName>
    	<contentDescription>Content Purchase</contentDescription>
    	<circleId/>
    	<lineOfBusiness/>
    	<customerSegment/>
    	<contentMediaType>Apps</contentMediaType>
    	<serviceId></serviceId>
    	<parentId/>
    	<actualPrice>200</actualPrice>
    	<basePrice>0</basePrice>
    	<discountApplied>0</discountApplied>
    	<paymentMethod/>
    	<revenuePercent/>
    	<netShare>0</netShare>
    	<cpId>AIRTELAPPSSTORE</cpId>
    	<customerClass/>
    	<eventType>Content Purchase</eventType>
    	<localTimeStamp/>
    	<transactionId/>
    	<subscriptionName>SRKPic</subscriptionName>
    	<parentType/>
    	<deliveryChannel>sms</deliveryChannel>
    	<subscriptionTypeCode>abcd</subscriptionTypeCode>
    	<subscriptionExternalId>2</subscriptionExternalId>
    	<contentSize/>
    	<currency>CFA</currency>
    	<copyrightId>xxx</copyrightId>
    	<cpTransactionId>201313121113_256759542182</cpTransactionId>
    	<copyrightDescription>copyright</copyrightDescription>
    	<sMSkeyword>sms</sMSkeyword>
    	<srcCode>abcd</srcCode>
    	<contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl>
    	<subscriptiondays>1</subscriptiondays>
    	</inputMsg>
    	</char:charge>
    	</soapenv:Body>
    	</soapenv:Envelope>';
    
    
    
    	$result=$client->send($msg, 'https://AgUser:AgUser@154.0.177.3:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort');
    	//Zend_Debug::dump($result);die();
    	Zend_Debug::dump($result);
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
    
    	die();
    
    }
    
    
    
    public function soapmalawiAction(){
    
    	error_reporting(E_ALL);
    	ini_set('display_errors', 1);
    
    	include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
    
    	$spEndPoint =  'https://NEXVA_MW:NEXVA_MW!ibm123@41.223.58.133:8443/ChargingServiceFlowWeb/sca/ChargingExport1';
    
    	$client = new nusoap_client($spEndPoint);
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$msg = '<?xml version="1.0" encoding="UTF-8"?>
    	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
    	<soapenv:Header/>
    	<soapenv:Body>
    	<char:charge>
    	<inputMsg>
    	<operation>debit</operation>
    	<userId>265995889560</userId>
    	<contentId>1</contentId>
    	<itemName>test</itemName>
    	<contentDescription>Content Purchase</contentDescription>
    	<circleId/>
    	<lineOfBusiness/>
    	<customerSegment/>
    	<contentMediaType>Apps100</contentMediaType>
    	<serviceId></serviceId>
    	<parentId/>
    	<actualPrice>100</actualPrice>
    	<basePrice>0</basePrice>
    	<discountApplied>0</discountApplied>
    	<paymentMethod/>
    	<revenuePercent/>
    	<netShare>0</netShare>
    	<cpId>NEXVA_MW</cpId>
    	<customerClass/>
    	<eventType>Content Purchase</eventType>
    	<localTimeStamp/>
    	<transactionId/>
    	<subscriptionName>SRKPic</subscriptionName>
    	<parentType/>
    	<deliveryChannel>sms</deliveryChannel>
    	<subscriptionTypeCode>abcd</subscriptionTypeCode>
    	<subscriptionExternalId>2</subscriptionExternalId>
    	<contentSize/>
    	<currency>MWK</currency>
    	<copyrightId>xxx</copyrightId>
    	<cpTransactionId>201313121113_256759542182</cpTransactionId>
    	<copyrightDescription>copyright</copyrightDescription>
    	<sMSkeyword>sms</sMSkeyword>
    	<srcCode>abcd</srcCode>
    	<contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl>
    	<subscriptiondays>1</subscriptiondays>
    	</inputMsg>
    	</char:charge>
    	</soapenv:Body>
    	</soapenv:Envelope>';
    
    
    
    	$result=$client->send($msg, 'https://NEXVA_MW:NEXVA_MW!ibm123@41.223.58.133:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort');
    	//Zend_Debug::dump($result);die();
    	Zend_Debug::dump($result);
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
    
    	die();
    
    }
    
    
    public function soapdrcAction(){
    
    	error_reporting(E_ALL);
    	ini_set('display_errors', 1);
    
    	include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
    
   // 	$spEndPoint =  'https://NEXVA_MW:NEXVA_MW!ibm123@41.223.58.133:8443/ChargingServiceFlowWeb/sca/ChargingExport1';
    	$spEndPoint =  'https://AgUser:AgUser@41.222.198.77:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort';
    	

    	$client = new nusoap_client($spEndPoint);
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$msg = '<?xml version="1.0" encoding="UTF-8"?>
    	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
    	<soapenv:Header/>
    	<soapenv:Body>
    	<char:charge>
    	<inputMsg>
    	<operation>debit</operation>
    	<userId>243973570968</userId>
    	<contentId>500</contentId>
    	<itemName>test</itemName>
    	<contentDescription>AppsPurchases500</contentDescription>
    	<circleId/>
    	<lineOfBusiness/>
    	<customerSegment/>
    	<contentMediaType>Apps</contentMediaType>
    	<serviceId></serviceId>
    	<parentId/>
    	<actualPrice>500</actualPrice>
    	<basePrice>0</basePrice>
    	<discountApplied>0</discountApplied>
    	<paymentMethod/>
    	<revenuePercent/>
    	<netShare>0</netShare>
        <cpId>AIRTELAPPSSTORE</cpId>
    	<customerClass/>
    	<eventType>Content Purchase</eventType>
    	<localTimeStamp/>
    	<transactionId/>
    	<subscriptionName>SRKPic</subscriptionName>
    	<parentType/>
    	<deliveryChannel>sms</deliveryChannel>
    	<subscriptionTypeCode>abcd</subscriptionTypeCode>
    	<subscriptionExternalId>2</subscriptionExternalId>
    	<contentSize/>
    	<currency>CDF</currency>
    	<copyrightId>xxx</copyrightId>
    	<cpTransactionId>201313121113_256759542182</cpTransactionId>
    	<copyrightDescription>copyright</copyrightDescription>
    	<sMSkeyword>sms</sMSkeyword>
    	<srcCode>abcd</srcCode>
    	<contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl>
    	<subscriptiondays>1</subscriptiondays>
    	</inputMsg>
    	</char:charge>
    	</soapenv:Body>
    	</soapenv:Envelope>';
    
    
    
    	$result=$client->send($msg, 'https://AgUser:AgUser@41.222.198.77:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort');
    	//Zend_Debug::dump($result);die();
    	Zend_Debug::dump($result);
    	echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
    
    	die();
    
    }
    
    
    
    public function airteldrcAction(){
    
        $soapUrl = 'https://41.222.198.77:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort';

        $msg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:char="
        http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
        <soapenv:Header/>
        <soapenv:Body>
        <char:charge>
        <inputMsg>
        <operation>debit</operation>
        <userId>243999967533</userId>
        <contentId>111</contentId>
        <itemName>SRKP</itemName>
        <contentDescription>Apps</contentDescription>
        <circleId/>
        <lineOfBusiness/>
        <customerSegment/>
        <contentMediaType>AppsPurchases750</contentMediaType>
        <serviceId>1</serviceId>
        <parentId/>
        <actualPrice>75.0</actualPrice>
        <basePrice>75.0</basePrice>
        <discountApplied>0</discountApplied>
        <paymentMethod/>
        <revenuePercent/>
        <netShare>0</netShare>
        <cpId>AIRTELAPPSSTORE</cpId>
        <customerClass/>
        <eventType>Content Purchase</eventType>
        <localTimeStamp/>
        <transactionId/>
        <subscriptionName>SRKPic</subscriptionName>
        <parentType/>
        <deliveryChannel>sms</deliveryChannel>
        <subscriptionTypeCode>abcd</subscriptionTypeCode>
        <subscriptionExternalId>2</subscriptionExternalId>
        <contentSize/>
        <currency>UNI</currency>
        <copyrightId>xxx</copyrightId>
        <cpTransactionId>rhKJJH</cpTransactionId>
        <copyrightDescription>copyright</copyrightDescription>
        <sMSkeyword>sms</sMSkeyword>
        <srcCode>abcd</srcCode>
        <contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl> (
        		http://www.ibm.com%29%3C/contentUrl%3E)
        		<subscriptiondays>1</subscriptiondays>
        		</inputMsg>
        		</char:charge>
        		</soapenv:Body>
        		</soapenv:Envelope>';

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: https://AgUser:AgUser@41.222.198.77:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort",
            "Content-length: ".strlen($msg),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_TIMEOUT,        10000);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        // curl_setopt($ch, CURLOPT_CAINFO,  APPLICATION_PATH . '/configs/certrw.crt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'AgUser:AgUser');
        curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);

        $result = curl_exec($ch);
        
        Zend_Debug::dump($result);
        die();

        $doc = new DOMDocument();
        $doc->loadXML($result);

        $paymentTimeStamp = date('d-m-Y');

        $buildUrl = null;
    }
    
    

    
    function mtnciAction()
    {
    	 
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    	$sss = new Nexva_MobileBilling_Type_HuaweiciAirtime();
    
    	// $dd = $sss->sendsms('256771002651', "test", 47413);
    
    	//die();
    	//$sss->addMobilePayment(80184, 74316, 44217, 256772120767, '0.01', 7);
    	$dd = $sss->doPayment(80184,  '74318', '44216', 22504844411, $appName, '0.005');
    	Zend_Debug::dump($dd);
    	die();
    	 
    }
     
    
    
   function mtnugtestAction()
   {
       

    
    
  $sss = new Nexva_MobileBilling_Type_MobilemoneyUganda();
    
   // $dd = $sss->sendsms('256771002651', "test", 47413);
    
    //die();774574475 256779999508
    $sss->addMobilePayment(80184, 74316, 44217, 256771002651, '0.01', 7);
    $dd = $sss->doPayment(80184,  '74318', '44216', 256772121681, $appName, '0.005');//0772121681 // 256771002651
       Zend_Debug::dump($dd);
       die();
       
   }

   
   function airtelugandasmsAction(){
       include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
       
       $smppHost = '41.223.58.132';
       $smppPort = '31110';
       $systemId = 'nex7va';
       $password = 'nex7va';
       $systemType = 'SMPP';
       $from = 'AppStore';
       
       
       echo "<pre>";
       $smpp = new SMPPClass();
       $smpp->SetSender($from);
       $smpp->_debug = true;
       /* bind to smpp server */
       $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
       /* send enquire link PDU to smpp server */
       //$smpp->TestLink();
       /* send single message; large messages are automatically split + 243999967533 */
       $messageStatus = $smpp->Send(256200205748, 'test', true);
       echo "</pre>";
       $smpp->End();

   }
   
   
   
   
   function drctestAction()
   {
   	 
   	$sss = new Nexva_MobileBilling_Type_AirtelDrc();
   	$dd = $sss->doPayment(280316, '79085', '44214', 256771002651, $appName, 1);
   	Zend_Debug::dump($dd);
   	die();
   	 
   }
   
    
    
  
    
    
    function sendsmstzAction()
    {
        
        ini_set('display_startup_errors',1);
        ini_set('display_errors',1);
        error_reporting(-1);
    
        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
    
    	$smppHost = '41.223.58.132';
    	$smppPort = '31110';
    	$systemId = 'nex2v';
    	$password = 'nex2v';
    	$systemType = 'smpp';
    	$from = 'AirtelTZ';
    
    	
    	echo '<pre>';
    	$smpp = new SMPPClass();
    	$smpp->SetSender($from);
    	/* bind to smpp server */
    	$smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
    	/* send enquire link PDU to smpp server */
    	$smpp->TestLink();
    	/* send single message; large messages are automatically split */
    	$messageStatus = $smpp->Send('265999959557', 'test test from neXva', true);
    	
    	Zend_Debug::dump($messageStatus,'ddd');
    	
    	
    	$smpp->End();
    	echo '</pre>';
    	die();
    	
    	
    	die();
    	/* send unicode message */
    	///$smpp->Send("731000057", "731000057", true);
    	/* send message to multiple recipients at once */
    	//$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
    	/* unbind from smpp server */
    
    }
    
    
    function sendsmsmwAction()
    {
    
    	ini_set('display_startup_errors',1);
    	ini_set('display_errors',1);
    	error_reporting(-1);
    
    	include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
    
    	$smppHost = '41.223.58.132';
    	$smppPort = '31110';
    	$systemId = 'nex7v';
    	$password = 'v@12xne';
    	$systemType = 'smpp';
    	$from = 'AppStore';
    
    	 
    	echo '<pre>';
    	$smpp = new SMPPClass();
    	$smpp->SetSender($from);
    	/* bind to smpp server */
    	$smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
    	/* send enquire link PDU to smpp server */
    	$smpp->TestLink();
    	/* send single message; large messages are automatically split */
    	$messageStatus = $smpp->Send('265997201172', 'test test from neXva', true);

    	//	$messageStatus = $smpp->Send('265992606718', 'test test from neXva', true);
    		
    	Zend_Debug::dump($messageStatus,'ddd');
    	 
    	 
    	$smpp->End();
    	echo '</pre>';
    	die();
    	 
    	 
    	die();
    	/* send unicode message */
    	///$smpp->Send("731000057", "731000057", true);
    	/* send message to multiple recipients at once */
    	//$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
    	/* unbind from smpp server */
    
    }
    
    function sendsmsdrcAction()
    {
       
    	ini_set('display_startup_errors',1);
    	ini_set('display_errors',1);
    	error_reporting(-1);
    
    	include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
    
    	$smppHost = '41.222.198.73';
    	$smppPort = '31110';
    	$systemId = 'appstore';
    	$password = 'app1234';
    	$systemType = 'SMPP';
    	$from = 'AppStore';
    
    
    	echo '<pre>';
    	$smpp = new SMPPClass();
    	$smpp->SetSender($from);
    	/* bind to smpp server */
    	$smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
    	/* send enquire link PDU to smpp server */
    	$smpp->TestLink();
    	/* send single message; large messages are automatically split + 243999967533 */
    	$messageStatus = $smpp->Send('243999967533', 'test test from neXva', true);
    
    	Zend_Debug::dump($messageStatus,'ddd');
    
    
    	$smpp->End();
    	echo '</pre>';
    	die();
    
    
    	die();
    	/* send unicode message */
    	///$smpp->Send("731000057", "731000057", true);
    	/* send message to multiple recipients at once */
    	//$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
    	/* unbind from smpp server */
    
    }
    
    
    
    function sendgabonAction()
    {
        
        
        ini_set('display_startup_errors',1);
        ini_set('display_errors',1);
        error_reporting(-1);
    	include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );
    
    	$from = $this->getRequest()->getParam('from');
    	$to = $this->getRequest()->getParam('to');
    
    	$smppHost = '192.168.1.61';
    	$smppPort = '16920';
    	$systemId = 'NEXVA';
    	$password = 'NEXVA';
    	$systemType = 'smpp';
    	$from = 'AirtelAPPS';
    
    
    	echo '<pre>';
    	$smpp = new SMPPClass();
    	$smpp->SetSender($from);
    	/* bind to smpp server */
    	$smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
    	/* send enquire link PDU to smpp server */
    	$smpp->TestLink();
    	/* send single message; large messages are automatically split */
        //$messageStatus = $smpp->Send('24107655962', 'test test', true);
    	
        $messageStatus = $smpp->Send('24107302572', 'test test', true);
        
       // 24107280184
    	
    	//$messageStatus = $smpp->Send('265992663470', 'test test', true);
    
    	Zend_Debug::dump($messageStatus,'ddd');
    
    
    	$smpp->End();
    	echo '</pre>';
    	die();
    	/* send unicode message */
    	///$smpp->Send("731000057", "731000057", true);
    	/* send message to multiple recipients at once */
    	//$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
    	/* unbind from smpp server */
    
    }
    
    

    public function curltestAction()
    {

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $soapUrl = "https://197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort"; // asmx URL of WSDL
        $soapUser = "AirtelAppStore_RW";  //  username
        $soapPassword = "AirtelAppStore_RW!ibm123"; // password

        // xml post structure

        $xml_post_string = '<?xml version="1.0" encoding="UTF-8"?>
	    <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
   <soapenv:Header/>
   <soapenv:Body>
      <char:charge>
         <inputMsg>
            <operation>debit</operation>
            <userId>0734014536</userId>
            <contentId>111</contentId>
            <itemName>SRKPic</itemName>
            <contentDescription>SRK - Subscription Service</contentDescription>
            <circleId/>
            <lineOfBusiness/>
            <customerSegment/>
            <contentMediaType>WallPaper</contentMediaType>
            <serviceId>1</serviceId>
            <parentId/>
            <actualPrice>0.1</actualPrice>
            <basePrice>0</basePrice>
            <discountApplied>0</discountApplied>
            <paymentMethod/>
            <revenuePercent/>
            <netShare>0</netShare>
            <cpId>TESTRWANDACP</cpId>
            <customerClass/>
            <eventType>Content Purchase</eventType>
            <localTimeStamp/>
            <transactionId/>
            <subscriptionName>SRKPic</subscriptionName>
            <parentType/>
            <deliveryChannel>sms</deliveryChannel>
            <subscriptionTypeCode>abcd</subscriptionTypeCode>
            <subscriptionExternalId>2</subscriptionExternalId>
            <contentSize/>
            <currency>RWF</currency>
            <copyrightId>xxx</copyrightId>
            <cpTransactionId>201313121113_256759542182</cpTransactionId>
            <copyrightDescription>copyright</copyrightDescription>
            <sMSkeyword>sms</sMSkeyword>
            <srcCode>abcd</srcCode>
            <contentUrl>www.ibm.com (http://www.ibm.com)</contentUrl>
            <subscriptiondays>1</subscriptiondays>
         </inputMsg>
      </char:charge>
   </soapenv:Body>
</soapenv:Envelope>';   // data from the form, e.g. some ID number

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: https://197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort",
            "Content-length: ".strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;
        
        
    //    $ch = curl_init();
    //    curl_setopt($ch, CURLOPT_URL, $url);
     //   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
     //   curl_setopt($ch, CURLOPT_TIMEOUT,        10000);
     //   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
     //   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
     //   curl_setopt ($ch, CURLOPT_CAINFO,  APPLICATION_PATH . '/configs/ChGw_Kenya_LB.crt');
    //    curl_easy_setopt(curl, CURLOPT_CAPATH, '');
    //    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //    curl_setopt($ch, CURLOPT_USERPWD, 'AirtelAppStore_RW:AirtelAppStore_RW!ibm123');
    //    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
  //      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
        
        
        /*
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
        
        // Turn on SSL certificate verfication
        curl_setopt($ch, CURLOPT_CAPATH,  APPLICATION_PATH . '/configs/ChGw_Kenya_LB.crt');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        
        // Tell the curl instance to talk to the server using HTTP POST
        curl_setopt($ch, CURLOPT_POST, 1);
        
        // 1 second for a connection timeout with curl
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        // Try using this instead of the php set_time_limit function call
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        // Causes curl to return the result on success which should help us avoid using the writeback option
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
              curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
           curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
        
     
        

*/

        /*

        // PHP cURL  for https connection with auth
      
        $ch = curl_init();
        /*
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 3);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //  curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc


        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'AirtelAppStore_RW:AirtelAppStore_RW!ibm123');


        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_TIMEOUT,        10000);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
       //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 3);
       curl_setopt ($ch, CURLOPT_CAINFO,  APPLICATION_PATH . '/configs/ChGw_Kenya_LB.crt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'AirtelAppStore_RW:AirtelAppStore_RW!ibm123');
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        
        

*/
        
        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_TIMEOUT,        10000);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt ($ch, CURLOPT_CAINFO,  APPLICATION_PATH . '/configs/certrw.crt');
//        curl_setopt($ch, CURLOPT_CAPATH,  APPLICATION_PATH. "/../ssl");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'AgUser:AgUser');
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
        
        
        $sss = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = curl_exec($ch);
        
     Zend_Debug::dump($sss);

        if(curl_errno($ch))
        {
            echo 'Curl error: ' . curl_error($ch);
        }
        else
        {
            echo $result;
        }

        curl_close($ch);


        Zend_Debug::dump($result);
        die();
        curl_close($ch);

        // converting
        $response1 = str_replace("<soap:Body>","",$result);
        $response2 = str_replace("</soap:Body>","",$response1);



        // convertingc to XML
        $parser = simplexml_load_string($response2);
        // user $parser to get your data out of XML response and to display it.



    }

    public function smpprAction() {

        include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
        //  $smpp->bindTransmitter("1432","n3x41A99");

        // Optional connection specific overrides
        //pmg-acg-sms01.ref1.lightsurf.net


        $tx=new SMPP('197.157.129.20','31120');
        $tx->debug=true;
        $tx->system_type="smpp";
        $tx->interface_version='3.4';

        $tx->addr_ton=1;
        $tx->addr_npi=1;
        //print "open status: ".$tx->state."\n";
        Zend_Debug::dump( $tx->bindTransmitter("neXvarw","neXvarw"));


        //$tx->sms_source_addr_npi=1;
        //$tx->sms_source_addr_ton=1;
        //	$tx->sms_dest_addr_ton=1;
        //	$tx->sms_dest_addr_npi=1;

        $tx->sms_source_addr_npi=1;
        $tx->sms_source_addr_ton=0;
        $tx->sms_dest_addr_ton=1;
        $tx->sms_dest_addr_npi=0;
        Zend_Debug::dump($tx->sendSMS("22799338585",'22799338585',"Hello from neXva"));
        $tx->close();
        unset($tx);


        die('ddd');



    }

    function sendnigerAction()
    {
        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );

        $smppHost = '197.157.129.20';
        $smppPort = '31120';
        $systemId = 'neXvarw';
        $password = 'neXvarw';
        $systemType = 'smpp';
        $from = 'AppStore-RW';

        
/*         
        $smppHost = '196.46.244.58';
        $smppPort = '31110';
        $systemId = 'ninexva';
        $password = 'ninexva';
        $systemType = 'smpp';
        $from = 'AppStore-NE'; */


        echo '<pre>';
        $smpp = new SMPPClass();
        $smpp->SetSender($from);
        /* bind to smpp server */
        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
        /* send enquire link PDU to smpp server */
        $smpp->TestLink();
        /* send single message; large messages are automatically split */
       // $messageStatus = $smpp->Send('250735802002', 'neXva neXva neXva neXva neXva neXva ');
        $messageStatus = $smpp->Send('250731000057', 'neXva neXva neXva neXva neXva neXva ');
        //$messageStatus = $smpp->Send('22799338585', 'neXva neXva neXva neXva neXva neXva ');

        Zend_Debug::dump($messageStatus,'ddd');


        $smpp->End();
        echo '</pre>';
        die();
        /* send unicode message */
        ///$smpp->Send("731000057", "731000057", true);
        /* send message to multiple recipients at once */
        //$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
        /* unbind from smpp server */

    }

    

    public function smppgabonAction() {

        include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
        //  $smpp->bindTransmitter("1432","n3x41A99");

        // Optional connection specific overrides
        //pmg-acg-sms01.ref1.lightsurf.net


        $tx=new SMPP('192.168.1.61','16920');
        $tx->debug=true;
        //	$tx->system_type="smpp";
        //	$tx->interface_version='3.4';

        $tx->addr_ton=1;
        $tx->addr_npi=1;
        //print "open status: ".$tx->state."\n";
        Zend_Debug::dump( $tx->bindTransmitter("NEXVA","NEXVA"));


        //$tx->sms_source_addr_npi=1;
        //$tx->sms_source_addr_ton=1;
        //	$tx->sms_dest_addr_ton=1;
        //	$tx->sms_dest_addr_npi=1;

        $tx->sms_source_addr_npi=1;
        $tx->sms_source_addr_ton=1;
        $tx->sms_dest_addr_ton=1;
        $tx->sms_dest_addr_npi=1;
        Zend_Debug::dump($tx->sendSMS("+94757474481",'+94757474481',"Hello from neXva"));
        $tx->close();
        unset($tx);


        die('ddd');



    }

    public function smpp2gabonaAction() {

        include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
        //  $smpp->bindTransmitter("1432","n3x41A99");

        // Optional connection specific overrides
        //pmg-acg-sms01.ref1.lightsurf.net

        print "<pre>";
        $tx=new SMPP('192.168.1.61','16920');
        $tx->debug=true;
        // $tx->system_type="WWW";
        //	$tx->addr_npi=1;
        print "open status: ".$tx->state."\n";
        Zend_Debug::dump( $tx->bindTransmitter("NEXVA","NEXVA"));


        //	$tx->sms_source_addr_npi=1;
        //$tx->sms_source_addr_ton=1;
        //	$tx->sms_dest_addr_ton=1;
        //	$tx->sms_dest_addr_npi=1;
        Zend_Debug::dump($tx->sendSMS("094757474481",'094757474481',"Hello world!"));
        $tx->close();
        unset($tx);
        print "</pre>";

        die('ddd');



    }


    public function smpp2mtnUgandaAction() {

        include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
        //  $smpp->bindTransmitter("1432","n3x41A99");

        // Optional connection specific overrides
        //pmg-acg-sms01.ref1.lightsurf.net

        print "<pre>";
        $tx=new SMPP('10.170.10.58','5001');
        $tx->debug=true;
        // $tx->system_type="WWW";
        //	$tx->addr_npi=1;
        print "open status: ".$tx->state."\n";
        Zend_Debug::dump( $tx->bindTransmitter("mtnappshop","n3Xv4"));


        //	$tx->sms_source_addr_npi=1;
        //$tx->sms_source_addr_ton=1;
        //	$tx->sms_dest_addr_ton=1;
        //	$tx->sms_dest_addr_npi=1;
        Zend_Debug::dump($tx->sendSMS("094757474481",'094757474481',"Hello world!"));
        $tx->close();
        unset($tx);
        print "</pre>";

        die('ddd');



    }

    public function smppmtnUgandaAction() {

        include_once( APPLICATION_PATH.'/../public/vendors/easy_smpp/smpp.php' );
        //  $smpp->bindTransmitter("1432","n3x41A99");

        // Optional connection specific overrides
        //pmg-acg-sms01.ref1.lightsurf.net


        $tx=new SMPP('10.170.10.58','5001');
        $tx->debug=true;
        $tx->system_type="vma";
        $tx->interface_version='3.4';

        $tx->addr_ton=1;
        $tx->addr_npi=1;
        //print "open status: ".$tx->state."\n";
        Zend_Debug::dump( $tx->bindTransmitter("mtnappshop","n3Xv4"));


        //$tx->sms_source_addr_npi=1;
        //$tx->sms_source_addr_ton=1;
        //	$tx->sms_dest_addr_ton=1;
        //	$tx->sms_dest_addr_npi=1;

        $tx->sms_source_addr_npi=1;
        $tx->sms_source_addr_ton=0;
        $tx->sms_dest_addr_ton=1;
        $tx->sms_dest_addr_npi=0;
        Zend_Debug::dump($tx->sendSMS("731000057",'731000058',"Hello from neXva"));
        $tx->close();
        unset($tx);


        die('ddd');



    }

    public function airtelChargingAction(){

        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

        //$client = new nusoap_client('https://196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1', array('username'=> "neXva_gh", 'password' => "AirtelAppStore_RW!ibm123"));
        //$client = new nusoap_client('https://AirtelAppStore_RW:AirtelAppStore_RW!ibm123@197.157.129.24:8443/ChargingWeb/services/ChargingExport1_ChargingHttpPort', false);
        $client = new nusoap_client('https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1', false);
        //$client = new nusoap_client('https://196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1', array('username'=> "neXva_gh", 'password' => "neXva_gh!ibm123"));
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $err = $client->getError();
        if ($err) {
            die("client construction error: {$err}\n");
        }

        //08087565209
        //07012966965
        $chargingXml = '<?xml version="1.0" encoding="UTF-8"?>
                        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:char="http://ChargingProcess/com/ibm/sdp/services/charging/abstraction/Charging">
                        <soapenv:Header />
                            <soapenv:Body>
                                <char:charge>
                                    <inputMsg>
                                        <operation>debit</operation>
                                        <userId>2347012966965</userId>
                                        <contentId>N150</contentId>
                                        <itemName>test</itemName>
                                        <contentDescription>airtellive</contentDescription>
                                        <circleId></circleId>
                                        <lineOfBusiness></lineOfBusiness>
                                        <customerSegment></customerSegment>
                                        <contentMediaType>Mobile apps prices</contentMediaType>
                                        <serviceId></serviceId>
                                        <parentId></parentId>
                                        <actualPrice>5</actualPrice>
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

        /*$chargingXml = '<?xml version="1.0" encoding="UTF-8"?>
                        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sms="http://sms.ibm.com">
                        <soapenv:Header/>
                        <soapenv:Body>
                        <sms:sendSMS>
                        <inputMsg>
                        <msisdn>2347012966965</msisdn>
                        <sendername>?</sendername>
                        <smsMsg>?</smsMsg>
                        </inputMsg>
                        </sms:sendSMS>
                        </soapenv:Body>
                        </soapenv:Envelope>';*/


            $result=$client->send($chargingXml, 'https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1');
        echo '-----------------------------------------------Request-------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($client->request);
        echo '-----------------------------------------------Response--------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($client->response);
        echo '--------------------------------------------------------------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($result);die();
        //$err = $client->getError();
        //$client->setCredentials('neXva_gh', 'neXva_gh!ibm123', 'digest');
        //=$client->send($msg, 'https://196.46.244.21:8443/ChargingServiceFlowWeb/sca/ChargingExport1');
        //Zend_Debug::dump($result);

        //$result=$client->send($msg, 'http://196.46.244.21:8443');
        //Zend_Debug::dump($client->response);
        //Zend_Debug::dump($client->getDebug());
        //$result=$client->send($msg, 'POST');

        //Zend_Debug::dump($err);
        //return $result;
        //die();
    }
    
    
    public function airtelSms2Action(){
        

        
        $msg = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sms="http://sms.ibm.com">
        <soapenv:Header/>
        <soapenv:Body>
        <sms:sendSMS>
        <inputMsg>
        <msisdn>2348022225672</msisdn>
        <sendername>Airtel APP Store</sendername>
        <smsMsg>this is test message from chathura</smsMsg>
        </inputMsg>
        </sms:sendSMS>
        </soapenv:Body>
        </soapenv:Envelope>';
        
        //        <msisdn>2348089447985</msisdn>
        
        $url = "https://196.46.244.21:8443/SMSNetworkService/services/SMS";
        $header[] = "Accept-Encoding: gzip,deflate";
        $header[] = "Content-Type: text/xml;charset=UTF-8";
        $header[] = "SOAPAction: 'sendSMS'";
        $header[] = "User-Agent: Jakarta Commons-HttpClient/3.1";
        $header[] = "Authorization: Basic QWdVc2VyOkFnVXNlcg==";
        $header[] = "Content-length: ".strlen($msg);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_TIMEOUT,        10000);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt ($ch, CURLOPT_CAINFO,  APPLICATION_PATH . '/configs/server.crt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "AgUser:AgUser"); // u
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
        
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
        	Zend_Debug::dump(curl_error($ch));
        } else {
        	curl_close($ch);
        	Zend_Debug::dump($data);
        }
        
        
      //  htmlentities($xmldata->asXML()));

        /* enable incase of debugsug
        $xml = simplexml_load_string($data);
        $xml->registerXPathNamespace('soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('NS', 'urn:MobileCommIntf-IMobileComm');
        $nodes = $xml->xpath('//soapenv:Envelope/soapenv:Body');
        print_r($nodes[0]->asXML());
        die();
        */

      // print_r($xml);
        
        $doc = new DOMDocument();
        $doc->loadXML($data);
        echo $doc->getElementsByTagName('status')->item(0)->nodeValue;

        if($doc->getElementsByTagName('status')->item(0)->nodeValue == 'Success') {


        }
   


        die();
        // converting
        $response1 = str_replace("<soap:Body>","",$data);
        $response2 = str_replace("</soap:Body>","",$data);
        
        // convertingc to XML
        $parser = simplexml_load_string($data);
        
        Zend_Debug::dump($parser);
         
        die();
        
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $CURL = curl_init();
        
        $msg = '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sms="http://sms.ibm.com">
        <soapenv:Header/>
        <soapenv:Body>
        <sms:sendSMS>
        <inputMsg><msisdn>2348089447985</msisdn><sendername>AgUser</sendername><smsMsg>this is test message </smsMsg></inputMsg>
        </sms:sendSMS>
        </soapenv:Body>
        </soapenv:Envelope>';
        
        curl_setopt($CURL, CURLOPT_URL, 'https://196.46.244.21:8443/SMSNetworkService/services/SMS');
        curl_setopt($CURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($CURL, CURLOPT_POST, 1);
        curl_setopt($CURL, CURLOPT_POSTFIELDS, $msg);
        curl_setopt($CURL, CURLOPT_HEADER, false);
        curl_setopt($CURL, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($CURL, CURLOPT_HTTPHEADER, array('Accept: text/xml','Content-Type: text/xml'));
        curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
        $xmlResponse = curl_exec($CURL);
        
        Zend_Debug::dump($xmlResponse);
        die();
        
        return $xmlResponse;
        
        
        $dataFromTheForm = $_POST['fieldName']; // request data from the form
        $soapUrl = "https://196.46.244.21:8443/SMSNetworkService/services/SMS"; // asmx URL of WSDL
        $soapUser = "username";  //
        $soapPassword = "password"; // password
        
        // xml post structure
        
        $msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sms="http://sms.ibm.com">
   <soapenv:Header/>
   <soapenv:Body>
      <sms:sendSMS>
         <inputMsg><msisdn>2348089447985</msisdn><sendername>AgUser</sendername><smsMsg>this is test message </smsMsg></inputMsg>
      </sms:sendSMS>
   </soapenv:Body>
</soapenv:Envelope>';
        
        $headers = array(
        		"Content-type: text/xml;charset=\"utf-8\"",
        		"Accept: text/xml",
                "SOAPAction: 'sendSMS'",
        		"Cache-Control: no-cache",
        		"Pragma: no-cache",
                "Host: 196.46.244.21:8443",
        		"Content-length: ".strlen($msg),
        ); //SOAPAction: your op URL "SOAPAction: https://196.46.244.21:8443",
        
        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "AgUser:AgUser"); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // converting
        $response = curl_exec($ch);
        curl_close($ch);
        
        Zend_Debug::dump($response);
        die();
        
        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);
        
        // convertingc to XML
        $parser = simplexml_load_string($response2);
        // user $parser to get your data out of XML response and to display it.
        
    }

    public function airtelSmsAction(){

        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

        //$client = new nusoap_client('https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443');
        //$client = new nusoap_client('https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443/SMSNetworkService/services/SMS');
    //    $client = new nusoap_client('https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443/ShortMessageService/services/SendSms');
     //   $client = new nusoap_client('https://AgUser:AgUser@196.46.244.21:8443/SMSNetworkService/services/SMS/wsdl/SMS.wsdl');
      $client = new nusoap_client('https://196.46.244.21:8443/ShortMessageService/services/SendSms');
       
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $err = $client->getError();
        if ($err) {
            die("client construction error: {$err}\n");
            
            
        }
        
        
 
        
      //  $client->authtype = 'certificate';
     // /  $client->decode_utf8 = 0;
     //   $client->soap_defencoding = 'UTF-8';
   //     $client->certRequest['sslcertfile'] = APPLICATION_PATH . '/configs/server.crt';
      ///  $client->certRequest['sslkeyfile'] = $private;
    //   $client->certRequest['cainfofile'] = $cacert;
        //

//     <sendername>AgUser</sendername>

        //SMS Service
        $msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sms="http://sms.ibm.com">
                <soapenv:Header/>
                    <soapenv:Body>
                        <sms:sendSMS>
                            <inputMsg>
                                <msisdn>1234567890</msisdn>
                                <sendername>AG102</sendername>
                                <smsMsg>test-message</smsMsg>
                            </inputMsg>
                        </sms:sendSMS>
                    </soapenv:Body>
                </soapenv:Envelope>';

        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sms="http://sms.ibm.com">
                <soapenv:Header/>
                <soapenv:Body>
                <sms:sendSMS>
                <inputMsg>
                <msisdn>?</msisdn>
                <sendername>?</sendername>
                <smsMsg>?</smsMsg>
                </inputMsg>
                </sms:sendSMS>
                </soapenv:Body>
                </soapenv:Envelope>';

            //$result = $client->call('sendSms', $phone, '', '', $header);
        //echo 'Before send';//die();
        $result = $client->send($msg, 'POST');
        //$result = $client->send($msg);

        //$result = $client->call('',$msg);
        //echo 'After send';die();
        echo '-----------------------------------------------Request-------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($client->request);
        echo '-----------------------------------------------Response--------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($client->response);
        echo '-----------------------------------------------Result--------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($result);die();

        //WAP Push Service
        /*$msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:wap="http://wappush.ibm.com">
                <soapenv:Header/>
                <soapenv:Body>
                <wap:sendWAPPush>
                <inputMsg>
                <msisdn>?</msisdn>
                <sendername>?</sendername>
                <text>?</text>
                <href>?</href>
                </inputMsg>
                </wap:sendWAPPush>
                </soapenv:Body>
                </soapenv:Envelope>';*/

        //MMS Service
        /*$msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:mms="http://mms.ibm.com/">
                <soapenv:Header/>
                <soapenv:Body>
                <mms:sendMMS>
                <inputMsg>
                <data>?</data>
                <fname>?</fname>
                <msisdn>?</msisdn>
                <sendername>?</sendername>
                <subject>?</subject>
                </inputMsg>
                </mms:sendMMS>
                </soapenv:Body>
                </soapenv:Envelope>';*/

        //Terminal Location Service
        /*$msg = '<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope
                xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                xmlns:ibm="http://ibm.com">
                <soapenv:Header/>
                <soapenv:Body>
                <ibm:getLocation>
                <inputMsg>
                <phoneNumber>?</phoneNumber>
                </inputMsg>
                </ibm:getLocation>
                </soapenv:Body>
                </soapenv:Envelope>';*/
    }
    
    public function smsngAction() {
        
        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(-1);
        
       $aa =  new Nexva_MobileBilling_Type_AirtelNigeria();
      $aa->sendsms('2348022225672', 'test from neXva', $chapId);
      
      die();
     //  $aa->sendsms('2348028712172', 'test from neXva', $chapId);
       
       $dd =  $aa->doPayment(81449, 74319, 44217, '2348028712172', $appName, '1');
    }


    public function mobilemoneytAction() {
        
       // error_reporting(E_ALL);
    //    ini_set('display_errors', 1);
     //   ini_set('default_socket_timeout', 300);

        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

        $amount = 500;
     //  $mobileNo = '256771002651';
      $mobileNo = '256772121481';
      
      $mobileNo = '256771002651';
        
     //   $mobileNo = '256772444774';
        
        //echo $chapId,' - ',$buildId,' - ',$appId,' - ',$mobileNo,' - ',$appName,' - ',$price,' - ',$amount;die();

        $client = new nusoap_client('http://172.25.48.36:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v15', false);
        //$client = new nusoap_client('http://api.nexva.com/test/test-uganda-final-request', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

       //  $spId =  '2560110000692';  old id fundomo 
       $spId =  '2560110002883';
        $spPass = 'Pasword24';

        
        $password = strtoupper(MD5($spId.$spPass.$timeStamp));

        $xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0">
			   <soapenv:Header>
				  <RequestSOAPHeader xmlns="http://www.huawei.com.cn/schema/common/v2_1">
					 <spId>'.$spId.'</spId>
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
						<value>'.$timeStamp.'</value>
					 </parameter>
					 <parameter>
						<name>serviceId</name>
						<value>MTNApp.SP1</value>
					 </parameter>
					 <parameter>
						<name>AcctRef</name>
						<value>'.$timeStamp.'</value>
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
						<value>Please confirm the amount of '.$amount.' UGX to Complete Transaction.</value>
					 </parameter>
					 <parameter>
						<name>PrefLang</name>
						<value>en</value>
					 </parameter>
					 <parameter>
						<name>OpCoID</name>
						<value>25601</value>
					 </parameter>
				  </b2b:processRequest>
			   </soapenv:Body>
			</soapenv:Envelope>';
// AppStore.sp 25601
      
        $result = $client->send($xmlMsg, 'POST', 0, 180);




      //  Zend_Debug::dump($result);
       //  echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
       //  echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
         //   echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';


        $err = $client->getError();
        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        }


        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

        die();
    }
    
    public function ugtestAction(){
        
       $ss = new Nexva_MobileBilling_Type_MobilemoneyUganda();
    // $dd =  $ss->doPayment(80184, 74319, 44217, '256771002651', $appName, '1');
    //$dd =  $ss->sendsms(256771002651, 'test', 44217);
     
   $dd = $ss->testsms();
     
     echo  $dd;
     die();
        
    }
    
    
    public function smsmwAction(){
         $ss = new Nexva_MobileBilling_Type_AirtelMalawi();
         $dd =  $ss->sendsms(265999959557, 'test', 44217);
         
         die();	
    
    }
    

    public function smsAction(){

        //die('zshgdvfzyus');

        $mobileNo = 989373390219;
        $message = 'Hi This is a testing message';
        $chapId = 23045;

        //todo, get chap SMS gateway details dynamically242068661314
        $client = new nusoap_client('http://92.42.55.109:8310/SendSmsService/services/SendSms');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");
        $spId =  '001617';
        $spServiceId = '0016172000001407';
        $pass = '0f596813534b39c9429d6ba598f80b6d';

        $header = array(
            'RequestSOAPHeader' => array (
                'spId' => $spId,
                'spPassword' => '0f596813534b39c9429d6ba598f80b6d',
                'serviceId' => $spServiceId,
                'timeStamp' => $timeStamp,
                'OA' => $mobileNo,
                'FA' => $mobileNo
            )
        );

        $phone = array(
            'addresses'     =>  'tel:'.$mobileNo,
            'senderName'   =>  737920,
            'message'  => $message,
            'receiptRequest' => array(
                'endpoint'     =>  'http://88.190.51.72/recv-irancell.php',
                'interfaceName'   =>  'SmsNotification',
                'correlator'  => '1232',
            )
        );

        $result = $client->call('sendSms', $phone, '', '', $header);
        Zend_Debug::dump($result);die();
    }

    function sendsmsbAction()
    {

        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );

        $smppHost = '212.88.118.228';
        $smppPort = '5001';
        $systemId = 'mtnappshop';
        $password = 'n3Xv4';
        $systemType = 'vma';
        $from = 'MTNUganda';


        echo '<pre>';
        $smpp = new SMPPClass();
        $smpp->SetSender($from);
        /* bind to smpp server */
        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
        /* send enquire link PDU to smpp server */
        $smpp->TestLink();
        /* send single message; large messages are automatically split */
        $messageStatus = $smpp->Send('256775516494', 'neXva neXva neXva neXva neXva neXva test ');

        Zend_Debug::dump($messageStatus,'ddd');


        $smpp->End();
        echo '</pre>';
        die();
        /* send unicode message */
        ///$smpp->Send("731000057", "731000057", true);
        /* send message to multiple recipients at once */
        //$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
        /* unbind from smpp server */

    }


    public function mobileMoneySendRequestAction() {
        print_r($_POST);
        if(isset($_POST)){
            print_r($_POST);
        }

        die();
    }


    public function mobileMoneyXmlUgandaTestAction() {

        //echo 123; die();
        include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );

        $client = new nusoap_client('http://172.25.48.43:8310/ThirdPartyServiceUMMImpl/UMMServiceService/RequestPayment/v17', false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $timeStamp = date("Ymd").date("His");

        //$timeStamp = '20140401112201';
        $spId =  '2560110000692';
        $spPass = 'Huawei2014';

        $password = strtoupper(MD5($spId.$spPass.$timeStamp));

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
						<value>10</value>
					 </parameter>
					 <parameter>
						<name>MSISDNNum</name>
						<value>256775516494</value>
					 </parameter>
					 <parameter>
						<name>ProcessingNumber</name>
						<value>'.$timeStamp.'</value>
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
						<name>Narration</name>
						<value>121212</value>
					 </parameter>
					 <parameter>
						<name>PrefLang</name>
						<value>en</value>
					 </parameter>
					 <parameter>
						<name>OpCoID</name>
						<value>25601</value>
					 </parameter>
				  </b2b:processRequest>
			   </soapenv:Body>
			</soapenv:Envelope>';


        $result=$client->send($xmlMsg, 'POST');




        Zend_Debug::dump($result);

        $err = $client->getError();
        if ($err) {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
        }


        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

        die();
    }



    public function soapServerAction()
    {
        // Create the server instance
        $server = new soap_server();
        // Initialize WSDL support
        $server->configureWSDL('hellowsdl', 'urn:hellowsdl');

        // Register the method to expose
        $server->register('hello',                // method name
            array('name' => 'xsd:string'),        // input parameters
            array('return' => 'xsd:string'),      // output parameters
            'urn:hellowsdl',                      // namespace
            'urn:hellowsdl#hello',                // soapaction
            'rpc',                                // style
            'encoded',                            // use
            'Says hello to the caller'            // documentation
        );

        // Use the request to (try to) invoke the service
        $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
        $server->service($HTTP_RAW_POST_DATA);
    }

    // Define the method as a PHP function
    function hello($name) {

        return 'Hello, ' . $name;


    }

    // Define the method as a PHP function
    public function helloArray($person) {
        $greeting = 'Hello, ' . $person['firstname'] .
            '. It is nice to meet a ' . $person['age'] .
            ' year old ' . $person['gender'] . '.';

        $winner = $person['firstname'] == 'Scott';

        return array(
            'greeting' => $greeting,
            'winner' => $winner
        );
    }

    public function soapClientAction()
    {
        $client = new nusoap_client('http://api.nexva.com/test/soap-server?wsdl', true);
        /*$client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $msg = '<?xml version="1.0" encoding="ISO-8859-1"?>
                <SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
                                   xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
                                   xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                   xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
                                   xmlns:si="http://soapinterop.org/xsd"
                                   xmlns:tns="urn:hellowsdl2">
                    <SOAP-ENV:Body>
                        <tns:hello xmlns:tns="urn:hellowsdl2">
                            <person xsi:type="tns:Person">
                                <firstname xsi:type="xsd:string">Willi</firstname>
                                <age xsi:type="xsd:int">22</age>
                                <gender xsi:type="xsd:string">male</gender>
                            </person>
                        </tns:hello>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';

         $result=$client->send($msg, 'hellowsdl2#hello');

         echo $client->request.'<br/><br/>';

         echo $client->response;
         echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
         echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
         die();*/

        // Check for an error
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            // At this point, you know the call that follows will fail
        }
        // Call the SOAP method
        $result = $client->call('hello', array('name' => 'Scott'));
        // Check for a fault
        if ($client->fault) {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        } else {
            // Check for errors
            $err = $client->getError();
            if ($err) {
                // Display the error
                echo '<h2>Error</h2><pre>' . $err . '</pre>';
            } else {
                // Display the result
                echo '<h2>Result</h2><pre>';
                print_r($result);
                echo '</pre>';
            }
        }
        // Display the request and response
        echo '<h2>Request</h2>';
        echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
        // Display the debug messages
        echo '<h2>Debug</h2>';
        echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

        $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

    }
    
    public function testgongosmsAction(){
        
      $abc = new Nexva_MobileBilling_Type_Huaweinew();
    Zend_Debug::
    dump($abc->sendsms('242069152625', "ssss", '50'));
      die();
        
    }

    public function confirmThirdPartyPaymentRequestAction()
    {
        // initialize server and set URI
        $server = new Zend_Soap_Server(null, array('uri' => 'http://api.nexva.com/test/confirm-third-party-payment-request'));

        // set SOAP service class
        $server->setClass('Uganda_Soap_Functions');

        $server->setEncoding('UTF-8');
        //$server->setReturnResponse(false);

        // handle request
        $server->handle();


        // Handle the request and generate suitable response

        /* if (null === $request) {
          $request = file_get_contents('php://input');
         }
         // Parse request, generate a static/dynamic response and return it.

         // return parent::handle($request); // Actual response

       // Custom response
       $doc = new DOMDocument();
       libxml_use_internal_errors(true);
       $doc->loadHTML($request);
       libxml_clear_errors();
       $xml = $doc->saveXML($doc->documentElement);
       $xml = simplexml_load_string($xml);
       $int = $xml->body->envelope->body->getdouble->int;
       $value = $int * 2;*/

        /*$result = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0/">
                       <soapenv:Header/> <soapenv:Body>
                       <b2b:processRequestResponse>';

             $result .= '<return>
                          <name>'.$p2->name.'</name>
                          <value>'.$p2->value.'</value>
                        </return>
                        <return>
                          <name>'.$p10->name.'</name>
                          <value>'.$p10->value.'</value>
                        </return>
                        <return>
                          <name></name>
                          <value></value>
                        </return>
                        <return>
                          <name>ThirdPartyAcctRef</name>
                          <value>'.$p4->value.'</value>
                        </return>
                        <return>
                          <name></name>
                          <value></value>
                        </return>
                        ';

            $result .= '</b2b:processRequestResponse>
                        </soapenv:Body>
                        </soapenv:Envelope>';

            echo $result;*/

    }

    public function testsoapAction()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        $spEndPoint =  'http://api.nexva.com/mobilemoney/confirm-third-party-payment-request-mtn-benin';

        $client = new nusoap_client($spEndPoint);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $this->_header = array();

        $msg = '<?xml version="1.0" encoding="utf-8" ?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <soapenv:Header>
    <ns1:NotifySOAPHeader xmlns:ns1="http://www.huawei.com.cn/schema/common/v2_1">
      <ns1:traceUniqueID>504021502871404111059030443002</ns1:traceUniqueID>
    </ns1:NotifySOAPHeader>
  </soapenv:Header>
  <soapenv:Body>
    <ns2:processRequest xmlns:ns2="http://b2b.mobilemoney.mtn.ug_v1.0/">
      <serviceId>101</serviceId>
      <parameter>
        <name>ProcessingNumber</name>
        <value>2087103</value>
      </parameter>
      <parameter>
        <name>SenderID</name>
        <value>MOM</value>
      </parameter>
      <parameter>
        <name>AcctRef</name>
        <value>1234</value>
      </parameter>
      <parameter>
        <name>RequestAmount</name>
        <value>10</value>
      </parameter>
      <parameter>
        <name>PaymentRef</name>
        <value>20140411075833</value>
      </parameter>
      <parameter>
        <name>ThirdPartyTransactionID</name>
        <value>20140411075833</value>
      </parameter>
      <parameter>
        <name>MOMAcctNum</name>
        <value></value>
      </parameter>
      <parameter>
        <name>CustName</name>
        <value/>
      </parameter>
      <parameter>
        <name>StatusCode</name>
        <value>01</value>
      </parameter>
      <parameter>
        <name>TXNType</name>
        <value/>
      </parameter>
      <parameter>
        <name>OpCoID</name>
        <value>25601</value>
      </parameter>
    </ns2:processRequest>
  </soapenv:Body>
</soapenv:Envelope>';

        $result=$client->send($msg, 'POST');
        //$result = $client->call('test', array('a' => 3, 'v' => 5), '', '', $this->_header);
        Zend_Debug::dump($result);
        //trun on this for debuging
        echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
        echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES)  . '</pre>';
        echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
        Zend_Debug::dump($result);

        Zend_Debug::dump($client->getDebug());
        Zend_Debug::dump($error = $client->getError());



    }


    public function airtelSriLankaAction(){
        
        include_once( APPLICATION_PATH.'/../public/vendors/Nusoap/lib/nusoap.php' );
        ini_set('display_startup_errors',1);
        ini_set('display_errors',1);
        error_reporting(-1);
        //$client = new nusoap_client('http://172.16.1.185:8080/dbill?serviceNode=SMSC',false);
        $client = new nusoap_client('http://10.200.182.146:9080/dbill?serviceNode=IACCR',false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        /*$xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <ocsRequest>
                        <serviceNode>SMSC</serviceNode>
                        <sequenceNo>060309431450545700</sequenceNo>
                        <requestType>4</requestType>
                        <cpcgFlag>1</cpcgFlag>
                        <callingParty>94755841077</callingParty>
                        <calledParty>9810599383</calledParty>
                        <startTime>1370232800</startTime>
                        <serviceId>SMSO</serviceId>
                        <serviceType>SMS</serviceType>
                        <callDuration>1</callDuration>
                        <callDirection>O</callDirection>
                    </ocsRequest>';*/

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <ocsRequest>
                        <serviceNode>IACCR</serviceNode>
                        <sequenceNo>060309431450545703</sequenceNo>
                        <requestType>4</requestType>
                        <cpcgFlag>1</cpcgFlag>
                        <callingParty>94757474481</callingParty>
                        <calledParty>94757474481</calledParty>
                        <portedCalledParty>NA</portedCalledParty>
                        <startTime>1370232800</startTime>
                        <serviceId>5798</serviceId>
                        <serviceType>wap_Appstore_5</serviceType>
                        <callDuration>1</callDuration>
                        <callDirection>O</callDirection>
                    </ocsRequest>';
        //94757474481
        $result=$client->send($xml,'POST');
       
        echo '-----------------------------------------------Request-------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($client->request);
        echo '-----------------------------------------------Response--------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($client->response);
        echo '--------------------------------------------------------------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($result);die();
    }


    public function comVivaAction(){

        $client = new nusoap_client('http://172.16.1.185:8080/dbill?serviceNode=SMSC',false);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        //$err = $client->getError();
        //$err = $client->response();
        //Zend_Debug::dump($err);die();

        /*$xmlMsg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
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
						<value>10</value>
					 </parameter>
					 <parameter>
						<name>MSISDNNum</name>
						<value>256775516494</value>
					 </parameter>
					 <parameter>
						<name>ProcessingNumber</name>
						<value>'.$timeStamp.'</value>
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
						<name>Narration</name>
						<value>121212</value>
					 </parameter>
					 <parameter>
						<name>PrefLang</name>
						<value>en</value>
					 </parameter>
					 <parameter>
						<name>OpCoID</name>
						<value>25601</value>
					 </parameter>
				  </b2b:processRequest>
			   </soapenv:Body>
			</soapenv:Envelope>';*/


        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <ocsRequest>
                        <serviceNode>SMSC</serviceNode>
                        <sequenceNo>123456</sequenceNo>
                        <requestType>4</requestType>
                        <cpcgFlag>1</cpcgFlag>
                        <callingParty>9810599382</callingParty>
                        <calledParty>9810599383</calledParty>
                        <startTime>1173336303233</startTime>
                        <serviceType>SMS</serviceType>
                        <serviceId>SMSO</serviceId>
                        <callDuration>1</callDuration>
                        <callDirection>O</callDirection>
                    </ocsRequest>
                ';

        $soapXml = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
                        <SOAP-ENV:Body>
                        <ocsRequest
                            serviceNode="SMSC"
                            sequenceNo="123545"
                            requestType=”4”
                            cpcgFlag=”1”
                            callingParty="9810599382"
                            calledParty="9810599383"
                            startTime="1173336303233"
                            serviceType="SMS"
                            serviceId="SMSO"
                            callDuration=”1”
                            callDirection="O" />
                        </SOAP-ENV:Body>
                    </SOAP-ENV:Envelope>';

        $result = $client->send($soapXml, 'POST');
        $fault = $client->fault;
        Zend_Debug::dump($fault);die();
        Zend_Debug::dump($result);die();
    }

    function mobileMoneyAction(){

        include_once( APPLICATION_PATH . '/../public/vendors/smpp/3/smppclass_binary.php' );

        /*$smppHost = '197.157.129.20';
        $smppPort = '31120';
        $systemId = 'neXvarw';
        $password = 'neXvarw';
        $systemType = 'smpp';
        $from = 'Mobile Money Uganda';*/

        /*$smppHost = '212.88.118.228';
        $smppPort = '5001';
        $systemId = 'mtnappshop';
        $password = 'n3Xv4';
        $systemType = 'vma';
        $from = 'MTNUganda';*/

        $smppHost = '212.88.118.228';
        $smppPort = '5001';
        $systemId = 'mtnappshop';
        $password = 'n3Xv4';
        $systemType = 'vma';
        $from = 'MTNUganda';


        //echo '<pre>';

        $smpp = new SMPPClass();
        $smpp->SetSender($from);
        /* bind to smpp server */
        $smpp->Start($smppHost, $smppPort, $systemId, $password, $systemType);
        /* send enquire link PDU to smpp server */
        $smpp->TestLink();
        /* send single message; large messages are automatically split */
        $messageStatus = $smpp->Send('256789999550', 'This is a test SMS from neXva.');
        //$messageStatus = $smpp->Send($mobileNo, $message);
        //Zend_Debug::dump($messageStatus,'booo');
        $smpp->End();

        //echo '</pre>';
        //die();
    }
    
    public function aironeAction() {
    
        
     $xml = '<?xml version="1.0"?>
         <methodCall>
        <methodName>GetFaFList</methodName>
        <params>
        <param>
        <value>
        <struct>
        <member>
        <name>originNodeType</name>
        <value><string>IVR</string></value>
        </member>
        <member>
        <name>originHostName</name>
        <value><string>ivr001</string></value>
        </member>
        <member>
        <name>originTransactionID</name>
        <value><string>566612</string></value>
        </member>
        <member>
        <name>originTimeStamp</name>
        <value><dateTime.iso8601>20050422T14:15:21+0200</dateTime.iso8601></value>
        </member>
        <member>
        <name>subscriberNumber</name>
        <value><string>0703105300</string></value>
        </member>
        <member>
        <name>requestedOwner</name>
        <value><int>1</int></value>
        </member>
        </struct>
        </value>
        </param>
        </params>
        </methodCall>';

        
    
    	$host = '10.87.64.48';
    	$port = '10010';
    	$url = "http://$host:$port/";
    	$header[] = "Content-type: text/xml";
    	$header[] = "Content-length: ".strlen($xml);
    
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    
    	$data = curl_exec($ch);
    	if (curl_errno($ch)) {
    		Zend_Debug::dump(curl_error($ch));
    	} else {
    		curl_close($ch);
    		Zend_Debug::dump($data);
    	}
    	
    	die();
    }
    

    public function airAction(){

        include_once( APPLICATION_PATH.'/../public/vendors/incutio/IXR_Library.php' );

        $client = new IXR_Client('http://192.168.1.5:10010');
    //    $client = new IXR_Client('192.168.1.5','','10010');
      ///  $client = new IXR_Client('http://10.87.64.48:10010');
       //$client = new IXR_Client('10.87.64.48','','10010');
        $client->getErrorMessage();
       // Zend_Debug::dump($client);
    

       $client->query('UpdateAccountDetails');
        if (!$client->query('UpdateAccountDetails')) {
            die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
        }
        Zend_Debug::dump($client->getResponse());
        die();
        

        $params    = array(
            'value'  => array(
                'struct'    =>  array(
                    array(
                        'name'  =>  'originNodeType',
                        'value' =>  'IVR'
                    ),
                    array(
                        'name'  =>  'originHostName',
                        'value' =>  'ivr001'
                    ),
                    array(
                        'name'  =>  'originTransactionID',
                        'value' =>  '566612'
                    ),
                    array(
                        'name'  =>  'originTimeStamp',
                        'value' =>  date(DATE_ISO8601)
                    ),
                    array(
                        'name'  =>  'subscriberNumber',
                        'value' =>  '0703105300'
                    ),
                    array(
                        'name'  =>  'requestedOwner',
                        'value' =>  '1'
                    )
                )
            )
        );

        if (!$client->query('UpdateAccountDetails',$params)) {
            die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
        }
        Zend_Debug::dump($client->getResponse());
        die();


        /*$conn  = new Zend_XmlRpc_Client('http://scripts.incutio.com/xmlrpc/simpleserver.php');
        $result = $conn->call('test.getTime');
        echo $result, "\n\n";
        //Zend_Debug::dump($conn);
        die();

        $client->query('UpdateAccountDetails');
        echo $client->getErrorCode(),' ',$client->getErrorMessage();
        die();*/

        /*$client = new Zend_Http_Client('http://88.190.51.72:10010');
        Zend_Debug::dump($client);
        die();*/

        //$client = new Zend_XmlRpc_Client('http://192.168.1.4:10010/UpdateAccountDetails');
        /*$client = new Zend_XmlRpc_Client('http://88.190.51.72:10010');
        Zend_Debug::dump($client, 'bbb');
        Zend_Debug::dump($client->getProxy(), 'ddd');*/


        /*$xml = '<?xml version="1.0"?>
                <methodCall>
                    <methodName>GetFaFList</methodName>
                        <params>
                            <param>
                                <value>
                                    <struct>
                                        <member>
                                            <name>originNodeType</name>
                                            <value><string>IVR</string></value>
                                        </member>
                                        <member>
                                            <name>originHostName</name>
                                            <value><string>ivr001</string></value>
                                        </member>
                                        <member>
                                            <name>originTransactionID</name>
                                            <value><string>566612</string></value>
                                        </member>
                                        <member>
                                            <name>originTimeStamp</name>
                                            <value><dateTime.iso8601>20050422T14:15:21+0200</dateTime.iso8601></value>
                                        </member>
                                        <member>
                                            <name>subscriberNumber</name>
                                            <value><string>0703105300</string></value>
                                        </member>
                                        <member>
                                            <name>requestedOwner</name>
                                            <value><int>1</int></value>
                                        </member>
                                    </struct>
                                </value>
                            </param>
                        </params>
                </methodCall>';
        */

        /*$params = array(
            array(
                'methodName' => 'GetFaFList',
                'params'     => array(
                    'value'  => array(
                        'struct'    =>  array(
                            array(
                                'name'  =>  'originNodeType',
                                'value' =>  'IVR'
                            ),
                            array(
                                'name'  =>  'originHostName',
                                'value' =>  'ivr001'
                            ),
                            array(
                                'name'  =>  'originTransactionID',
                                'value' =>  '566612'
                            ),
                            array(
                                'name'  =>  'originTimeStamp',
                                'value' =>  date(DATE_ISO8601)
                            ),
                            array(
                                'name'  =>  'subscriberNumber',
                                'value' =>  '0703105300'
                            ),
                            array(
                                'name'  =>  'requestedOwner',
                                'value' =>  '1'
                            )
                        )
                    )
                )
            )
        );

        $result = $client->call('UpdateAccountDetails',$params);

        //$lastRequest = $client->getLastRequest();

        Zend_Debug::dump($result,'ssss');
        die();


        echo 'Start';
        //echo $client->call('GetFaFList',$xml);
        echo 'End';
        die();

        $result=$client->send($xml,'POST');

        echo '-----------------------------------------------Request-------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($client->request);
        echo '-----------------------------------------------Response--------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($client->response);
        echo '--------------------------------------------------------------------------------------------------------','<br/>';
        echo '--------------------------------------------------------------------------------------------------------';
        Zend_Debug::dump($result);die();*/

    }

    /*public function airnewAction(){
        include_once( APPLICATION_PATH.'/../public/vendors/incutio/IXR_Library.php' );

        $client = new IXR_Client('http://192.168.1.4:10010');
        ///  $client = new IXR_Client('http://10.87.64.48:10010');
        //$client = new IXR_Client('10.87.64.48','','10010');
        //echo $client->getErrorMessage();
        Zend_Debug::dump($client);//die();

        $client->query('UpdateAccountDetails');
        if (!$client->query('UpdateAccountDetails')) {
            die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
        }
        Zend_Debug::dump($client->getResponse());
        die();
    }*/

    public function socketTestOneAction(){
        error_reporting(E_ALL);

        /* Allow the script to hang around waiting for connections. */
        set_time_limit(0);

        /* Turn on implicit output flushing so we see what we're getting
         * as it comes in. */
        ob_implicit_flush();

        $address = '192.168.1.5';
        $port = 10010;

        if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        }
        //die();
        if (socket_bind($sock, $address, $port) === false) {
            echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        }

        if (socket_listen($sock, 5) === false) {
            echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        }

        do {
            if (($msgsock = socket_accept($sock)) === false) {
                echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
                break;
            }
            /* Send instructions. */
            $msg = "\nWelcome to the PHP Test Server. \n" .
                "To quit, type 'quit'. To shut down the server type 'shutdown'.\n";
            socket_write($msgsock, $msg, strlen($msg));

            do {
                if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
                    echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
                    break 2;
                }
                if (!$buf = trim($buf)) {
                    continue;
                }
                if ($buf == 'quit') {
                    break;
                }
                if ($buf == 'shutdown') {
                    socket_close($msgsock);
                    break 2;
                }
                $talkback = "PHP: You said '$buf'.\n";
                socket_write($msgsock, $talkback, strlen($talkback));
                echo "$buf\n";
            } while (true);
            socket_close($msgsock);
        } while (true);

        socket_close($sock);
        
        die();
    }
    
    
    public function socketthreeAction(){
    	error_reporting(E_ALL);
    
    	/* Allow the script to hang around waiting for connections. */
    	set_time_limit(0);
    	
    	// NOTE: Contact an ClientCare to identify the correct namespace and domain for your company.
    	

    	$host      = '192.168.1.5';
    	$rsid      = "rsid";
    	$vid       = "";
    	$ip        = "10.0.0.1";
    	$page_url  = "";
    	$pageName  = "Test Page";
    	$timestamp = "2008-10-21T17:33:22-07";
    	$port = '10010';
    	
    	// create opening XML tags
    	$xml  = "<?xml version=1.0 encoding=UTF-8?>\n";
        $xml .="<methodCall>
<methodName>Request.getName</methodName>
<params>
<param>
<value>
<struct>
<member>
<name>RequestNumber</name>
<value><string>00471627612</string></value>
</member>
</struct>
</value>
</param>
</params>
</methodCall>\r\n";
    	
    	// Create POST, Host and Content-Length headers
    	$head = "POST /Air HTTP/1.1";
    	//$head .= "Content-Length: ".(string)strlen($xml)."";
    	$head .= "Content-Type: text/xml\n\n";
    	//$head .= "Date: Tue, 06 June 2014 13:17:39 MEST";
    	//$head .= "Host: $host:$port";
    	//$head .= "User-Agent:UGw Server/3.1/1.0";
    	//$head .= "Authorization: Basic Y2dzZHBjaGFyZ2luZzpjZ3NkcGNoYXJnaW5nQDEyMw==\n\n";

    	
    	// combine the head and XML
    	$request = $head.$xml;
    	
    	$fp=fsockopen($host,$port,$errno,$errstr,60);
    	// Use this function in place of the call above if you have PHP 4.3.0 or
    	//   higher and have compiled OpenSSL into the build.
    	//
    	// $fp = pfsockopen("ssl://".$host, 443, $errno, $errstr);
    	//
    	

    	
    	if( $fp ) {
    	
    	// send data
    	fwrite($fp,$request);
    	
    	// get response
    	$response="";
    	while( !feof($fp) ){
    	$response .= fgets($fp,1028);
    	}
    	fclose($fp);
    	
    	// display results
    	echo "RESULTS:\n";
    	Zend_Debug::dump($response);
    	echo "\n";
    	
    	}
    	
    	die();
  
    	/*
    	$host      = '192.168.1.5';
    	$rsid      = "rsid";
    	$vid       = "";
    	$ip        = "10.0.0.1";
    	$page_url  = "";
    	$pageName  = "Test Page";
    	$timestamp = "2008-10-21T17:33:22-07";
    	$port = '10010';
    //	request = xmlrpc_encode_request("weblogUpdates.ping", array("Copenhagen Ruby Brigade", "http://copenhagenrb.dk/") );
    	$xml  = "<?xml version=1.0 encoding=UTF-8?>\n";
    	$xml .="<methodCall>
    	<methodName>Request.getName</methodName>
    	<params>
    	<param>
    	<value>
    	<struct>
    	<member>
    	<name>RequestNumber</name>
    	<value><string>00471627612</string></value>
    	</member>
    	</struct>
    	</value>
    	</param>
    	</params>
    	</methodCall>\r\n";
    	 
    	// Create POST, Host and Content-Length headers
    	$header[]   = "POST /Air HTTP/1.1";
    	$header[]  .= "Content-Length: ".(string)strlen($xml)."";
    	$header[]  .= "Content-Type: text/xml";
    	$header[]  .= "Date: Tue, 06 June 2014 13:17:39 MEST";
    	$header[]  .= "Host: $host:$port";
     	$header[] .= "User-Agent:UGw Server/3.1/1.0";
     	$header[] .= "Authorization: Basic Y2dzZHBjaGFyZ2luZzpjZ3NkcGNoYXJnaW5nQDEyMw==\n\n";
     	$header[] .= $xml;
    	


    	$header[] = $xml;


    	$ch = curl_init();
    	//curl_setopt($ch, CURLOPT_PORT, '54512');
    	curl_setopt( $ch, CURLOPT_URL, "http://192.168.1.5:10010"); # URL to post to
    	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable

    	curl_setopt( $ch, CURLOPT_HTTPHEADER, $header ); # custom headers, see above

    	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' ); # This POST is special, and uses its specified Content-type

    	$result = curl_exec( $ch ); # run!

    	curl_close($ch);

    	

    	Zend_Debug::dump($result);
    	die();
    	
    	
    	*/

    	
    }

    public function socketTestTwoAction(){
        error_reporting(E_ALL);
        
        

        /* Get the port for the WWW service. */
        $service_port = getservbyname('www', 'tcp');
        
        $service_port = 10010;

        /* Get the IP address for the target host. */
        //$address = gethostbyname('www.example.com');
        $address = '192.168.1.5';

        /* Create a TCP/IP socket. */
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        } else {
            echo "OK.\n";
        }

        echo "Attempting to connect to '$address' on port '$service_port'...";
        $result = socket_connect($socket, $address, $service_port);
        if ($result === false) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        } else {
            echo "OK.\n";
        }

        $in = "POST /Air HTTP/1.1";
        $in .= "Content-Length: 500\r\n";
        $in .= "Content-Type: text/xml\r\n";
        $in .= "Date: Mon, 2 Jun 2014 13:17:39\r\n";
        $in .= "Host: 192.168.1.5:10010\r\n";
        $in .= "User-Agent:UGw Server/3.1/1.0\r\n";
        //$in .= "Authorization: Basic TmVYdmE6TmVYdmFAMTIz\r\n";
        $in .= "Authorization: Basic Y2dzZHBjaGFyZ2luZzpjZ3NkcGNoYXJnaW5nQDEyMw==\r\n";
        $in .="<?xml version='1.0'?><methodCall>
<methodName>Request.getName</methodName>
<params>
<param>
<value>
<struct>
<member>
<name>RequestNumber</name>
<value><string>00471627612</string></value>
</member>
</struct>
</value>
</param>
</params>
</methodCall>\r\n";
   //     $in .= "Connection: Close\r\n\r\n";
        $out = '';

        echo '<br/><br/><br/>Here we goes ....',$in,'<br/>';//die();

        echo "Sending HTTP HEAD request...";
        socket_write($socket, $in, strlen($in));
        echo "OK.\n";

        echo "Reading response:\n\n";
        Zend_Debug::dump(socket_read($socket, 2048));
        while ($out = socket_read($socket, 2048)) {
            echo '----',Zend_Debug::dump($out);
        }

        echo "Closing socket...";
        socket_close($socket);
        echo "OK.\n\n";
        
        die();
    }



public function socketfourAction(){
	error_reporting(E_ALL);



	/* Get the port for the WWW service. */
	$service_port = getservbyname('www', 'tcp');

	$service_port = 10010;

	/* Get the IP address for the target host. */
	//$address = gethostbyname('www.example.com');
	$address = '192.168.1.5';
	$service_url = 'http://192.168.1.5:10010/Air';
	
	$xml  = "<?xml version=1.0 encoding=UTF-8?>\n";
	$xml .="<methodCall>
	<methodName>Request.getName</methodName>
	<params>
	<param>
	<value>
	<struct>
	<member>
	<name></name>
	<value><string>00471627612</string></value>
	</member>
	</struct>
	</value>
	</param>
	</params>
	</methodCall>\r\n";
	

	
//	$request = xmlrpc_encode_request($method, array($user, $pass));
	$req = curl_init($service_url);
	
	// Using the cURL extension to send it off,  first creating a custom header block
	$headers = array();
	array_push($headers,'Content-Type: text/xml');
	//array_push($headers,'Date: Mon, 2 Jun 2014 13:17:39');
	array_push($headers,'Host: 192.168.1.5:10010');
	array_push($headers,'User-Agent:UGw Server/3.1/1.0');
	array_push($headers,'Content-Length: '.strlen($xml));
	array_push($headers,'"Authorization: Basic Y2dzZHBjaGFyZ2luZzpjZ3NkcGNoYXJnaW5nQDEyMw==');
	array_push($headers,'\r\n');

	//URL to post to
	curl_setopt($req, CURLOPT_URL, $service_url);
	
	//Setting options for a secure SSL based xmlrpc server
	curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($req, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt( $req, CURLOPT_CUSTOMREQUEST, 'POST' );
	curl_setopt($req, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($req, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $req, CURLOPT_POSTFIELDS, $xml );
	
	//Finally run
	$response = curl_exec($req);
	
	Zend_Debug::dump($response);
	die();
	
	//Close the cURL connection
	curl_close($req);
	
	//Decoding the response to be displayed
//	echo xmlrpc_decode($response);
		}


		
		public function socketfiveAction(){
			error_reporting(E_ALL);
		
		
		
			/* Get the port for the WWW service. */
			$service_port = getservbyname('www', 'tcp');
		
			$service_port = 10010;
		
			/* Get the IP address for the target host. */
			//$address = gethostbyname('www.example.com');
			$address = '192.168.1.5';
			$service_url = 'https://neXva_gh:neXva_gh!ibm123@196.46.244.21:8443/ShortMessageService/services/SendSms';
		
			$xml  = "<?xml version=1.0 encoding=UTF-8?>\n";
			$xml .="<methodCall>
			<methodName>Request.getName</methodName>
			<params>
			<param>
			<value>
			<struct>
			<member>
			<name></name>
			<value><string>00471627612</string></value>
			</member>
			</struct>
			</value>
			</param>
			</params>
			</methodCall>\r\n";
		
		
		
			//	$request = xmlrpc_encode_request($method, array($user, $pass));
			$req = curl_init($service_url);
		
			// Using the cURL extension to send it off,  first creating a custom header block
			$headers = array();
			array_push($headers,'Content-Type: text/xml');
			//array_push($headers,'Date: Mon, 2 Jun 2014 13:17:39');
			//array_push($headers,'Host: 196.46.244.21:8443');
			//array_push($headers,'User-Agent:UGw Server/3.1/1.0');
			////	array_push($headers,'Content-Length: '.strlen($xml));
			//	array_push($headers,'"Authorization: Basic Y2dzZHBjaGFyZ2luZzpjZ3NkcGNoYXJnaW5nQDEyMw==');
			//	array_push($headers,'\r\n');
		
			//URL to post to
			curl_setopt($req, CURLOPT_URL, $service_url);
		
			//Setting options for a secure SSL based xmlrpc server
			curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($req, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($req, CURLOPT_USERAGENTm, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0');
			curl_setopt( $req, CURLOPT_CUSTOMREQUEST, 'POST' );
			curl_setopt($req, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt($req, CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $req, CURLOPT_POSTFIELDS, $xml );
		
			//Finally run
			$response = curl_exec($req);
		
			Zend_Debug::dump($response);
			die();
		
			//Close the cURL connection
			curl_close($req);
		
			//Decoding the response to be displayed
			//	echo xmlrpc_decode($response);
		}

    public function airtelNigeriaAction(){
        $chapId = 81449;
        $appId = 39528;
        $price = 5.00;
        $mobileNo = 2348089447985;
        $buildId = 71259;
        $appName = 'Nexva Test App';

        //Get payment gateway Id of the CHAP
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

        $pgType = $pgDetails->gateway_id;
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        //Call Nexva_MobileBilling_Factory and create relevant instance
        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

        //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
        //$pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId);

        //Do the transaction and get the build url
        $buildUrl = $pgClass->doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price);
        Zend_Debug::dump($buildUrl);die();
    }


    
    public function testcongoAction()
    {
        
        $ddd = new Nexva_MobileBilling_Type_Huaweinew();
        
        $ddd->chrage('242069152625', 10, 'XAF');
    }
    
    
    public function testtedAction(){
    	error_reporting(E_ALL);
    
    
    
    	$xml  = "<?xml version=1.0 encoding=UTF-8?>\n";
    	$xml .="<methodCall>
    	<methodName>UpdateAccountDetails</methodName>
    	<params>
    	<param>
    	<value>
    	<struct>
    	<member>
    	<name></name>
    	<value><string>00471627612</string></value>
    	</member>
    	</struct>
    	</value>
    	</param>
    	</params>
    	</methodCall>\r\n";
    	
    		$xml  = '<xmlversion="1.0">
    	<methodCall>
    	<methodName>UpdateBalanceAndDate</methodName><params><param><value><struct><member><name>originNodeType</name><value><string>EXT</string></value></member>
    	<member><name>originHostName</name><value><string>SAIR</string></value></member>
    	<member><name>originTransactionID</name><value><string>1307000605380</string></value></member>
    	<member><name>subscriberNumberNAI</name><value><i4>1</i4></value></member>
    	<member><name>subscriberNumber</name><value><string>24107592315</string></value></member>
    	<member><name>transactionCurrency</name><value><string>CFA</string></value></member>
    	<member><name>dedicatedAccountUpdateInformation</name><value><array><data><value><struct>
    	<member><name>dedicatedAccountID</name><value><int>25</int></value></member>
    	<member><name>adjustmentAmountRelative</name><value><string>120</string></value></member>
    	</struct></value></param></params>
    	</methodCall>\r\n';
    	
    		//    	<member><name>originTimeStamp</name><value><dateTime.iso8601>20130704T12:10:54+0100</dateTime.iso8601></value></member>
    	//	<member><name>expiryDate</name><value><dateTime.iso8601>-</dateTime.iso8601></value></member></struct></value></data></array></value></member>
    
    
    
    	$url = "http://192.168.1.5:10010";
    	$header[] = "Content-Type: text/xml";
    	$header[] = "Host: 192.168.1.5:10010";
    	$header[] = "User-Agent:UGw Server/3.1/1.0";
    	$header[] = "User-Agent: Jakarta Commons-HttpClient/3.1";
    	$header[] = "Authorization: Basic Y2dzZHBjaGFyZ2luZzpjZ3NkcGNoYXJnaW5nQDEyMw==";
    	$header[] = "Content-length: ".strlen($xml);
    
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100000);
    	curl_setopt($ch, CURLOPT_TIMEOUT,        100000);
    	//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    	//	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
    	//	curl_setopt ($ch, CURLOPT_CAINFO,  APPLICATION_PATH . '/configs/server.crt');
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    	curl_setopt($ch, CURLOPT_USERPWD, "AgUser:AgUser"); // u
    	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    	curl_setopt($ch, CURLOPT_HEADER, 1);
    	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    
    	$data = curl_exec($ch);
    	if (curl_errno($ch)) {
    		Zend_Debug::dump(curl_error($ch));
    	} else {
    		curl_close($ch);
    		Zend_Debug::dump($data);
    		Zend_Debug::dump(curl_getinfo($ch));
    	}
    
    
    }
    
    
    
    public function testteddAction(){
    	error_reporting(E_ALL);
    
    
    /*
    	$xml  = "<?xml version=1.0 encoding=UTF-8?>\n";
    	$xml .="<methodCall>
    	<methodName>UpdateAccountDetails</methodName>
    	<params>
    	<param>
    	<value>
    	<struct>
    	<member>
    	<name></name>
    	<value><string>00471627612</string></value>
    	</member>
    	</struct>
    	</value>
    	</param>
    	</params>
    	</methodCall>\r\n";
    	
    	*/
    	 
    	$xml  = '<?xml version=1.0 encoding=UTF-8?>
    	<methodCall>
    	<methodName>UpdateBalanceAndDate</methodName><params><param><value><struct><member><name>originNodeType</name><value><string>EXT</string></value></member>
    	<member><name>originHostName</name><value><string>SAIR</string></value></member>
    	<member><name>originTransactionID</name><value><string>1307000605380</string></value></member>
    	<member><name>subscriberNumberNAI</name><value><i4>1</i4></value></member>
    	<member><name>subscriberNumber</name><value><string>24107592315</string></value></member>
    	<member><name>transactionCurrency</name><value><string>CFA</string></value></member>
    	<member><name>dedicatedAccountUpdateInformation</name><value><array><data><value><struct>
    	<member><name>dedicatedAccountID</name><value><int>25</int></value></member>
    	<member><name>adjustmentAmountRelative</name><value><string>120</string></value></member>
    	</struct></value></param></params>
    	</methodCall>\r\n';
    	 
    	//    	<member><name>originTimeStamp</name><value><dateTime.iso8601>20130704T12:10:54+0100</dateTime.iso8601></value></member>
    	//	<member><name>expiryDate</name><value><dateTime.iso8601>-</dateTime.iso8601></value></member></struct></value></data></array></value></member>
    
    
    
    	$url = "http://10.87.64.48:10010/Air";
    	$header[] = "Content-Type: text/xml";
    	$header[] = "Host: 10.87.64.48:10010";
    	$header[] = "User-Agent:NeXva /3.1/1.0'";
   // 	$header[] = "Authorization: Basic TmVYdmE6TmVYdmFAMTIz";
    	$header[] = "Content-length: ".strlen($xml);
    
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100000);
    	curl_setopt($ch, CURLOPT_TIMEOUT,        100000);
    	//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    	//	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
    	//	curl_setopt ($ch, CURLOPT_CAINFO,  APPLICATION_PATH . '/configs/server.crt');
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "NeXva:NeXv@123"); // u
    	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    	curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    
    	$data = curl_exec($ch);
    	if (curl_errno($ch)) {
    		Zend_Debug::dump(curl_error($ch));
    	} else {
    		curl_close($ch);
    		Zend_Debug::dump($data);
    		Zend_Debug::dump(curl_getinfo($ch));
    	}
    
    
    }
    
    
    public function testaAction() {
    
    	
	  //  include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/class.xmlrpcclient.php' );

		//$client = new xmlrpc_client('http://192.168.1.5:10010/Air.', false);
		
        error_reporting(E_ALL);
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors',1);
		
		include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/xmlrpc.php' );
		
		$client = new xmlrpc_client('http://192.168.1.5:10010/Air', false);
		
		$client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

		$xml  = '<?xml version=1.0 encoding=UTF-8?>
    	<methodCall>
    	<methodName>UpdateBalanceAndDate</methodName><params><param><value><struct><member><name>originNodeType</name><value><string>EXT</string></value></member>
    	<member><name>originHostName</name><value><string>SAIR</string></value></member>
    	<member><name>originTransactionID</name><value><string>1307000605380</string></value></member>
    	<member><name>subscriberNumberNAI</name><value><i4>1</i4></value></member>
    	<member><name>subscriberNumber</name><value><string>24107592315</string></value></member>
    	<member><name>transactionCurrency</name><value><string>CFA</string></value></member>
    	<member><name>dedicatedAccountUpdateInformation</name><value><array><data><value><struct>
    	<member><name>dedicatedAccountID</name><value><int>25</int></value></member>
    	<member><name>adjustmentAmountRelative</name><value><string>120</string></value></member>
    	</struct></value></param></params>
    	</methodCall>';
			
	
	    $result=$client->send($xml, 'POST', 0, 180);

	    Zend_Debug::dump($result);
	   
	    $err = $client->getError();
	    if ($err) {
	    	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	    }
	    
	     
	    echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
	    echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
	    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
	     
			die();
    }
    
    
    
    public function testbAction() {
    
    	 
    	//  include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/class.xmlrpcclient.php' );
    
    	//$client = new xmlrpc_client('http://192.168.1.5:10010/Air.', false);
    
    	error_reporting(E_ALL);
    	ini_set('error_reporting', E_ALL);
    	ini_set('display_errors',1);
    
    	include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/xmlrpc.php' );
    
    	$client = new xmlrpc_client('http://NeXva:NeXv@123@10.87.64.48:10010/Air', false);
    
    	$client->soap_defencoding = 'UTF-8';
    	$client->decode_utf8 = false;
    
    	$xml  = '<?xml version=1.0 encoding=UTF-8?>
    	<methodCall>
    	<methodName>UpdateBalanceAndDate</methodName><params><param><value><struct><member><name>originNodeType</name><value><string>EXT</string></value></member>
    	<member><name>originHostName</name><value><string>SAIR</string></value></member>
    	<member><name>originTransactionID</name><value><string>1307000605380</string></value></member>
    	<member><name>subscriberNumberNAI</name><value><i4>1</i4></value></member>
    	<member><name>subscriberNumber</name><value><string>24107592315</string></value></member>
    	<member><name>transactionCurrency</name><value><string>CFA</string></value></member>
    	<member><name>dedicatedAccountUpdateInformation</name><value><array><data><value><struct>
    	<member><name>dedicatedAccountID</name><value><int>25</int></value></member>
    	<member><name>adjustmentAmountRelative</name><value><string>120</string></value></member>
    	</struct></value></param></params>
    	</methodCall>';
    		
    
    	$result=$client->send($xml, 'POST', 0, 180);
    
    	Zend_Debug::dump($result);
    
    	$err = $client->getError();
    	if ($err) {
    		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    	}
    	 
    
    	echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
    	echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
    	echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
    
    	die();
    }
    
    
    public function orangeMoneyPaymentAction(){
    
    	//session_start();
    	Zend_Session::start();
    	$sessionId = Zend_Session::getId();
    	//echo $sessionId; die();
    	$url = "https://ompay.orange.ci/e-commerce_test_gw/init.php";
    
    	$client = new Zend_Http_Client($url);
    
    	$client->setHeaders(array(
    			'User-Agent: Mozilla/5.0 Firefox/3.6.12',
    			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9',
    			'Accept-Language: en-us,en;q=0.5',
    			'Accept-Encoding: deflate',
    			'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
    			'Content-Type: application/x-www-form-urlencoded',
    			'Content-Length: 109'
    	));
    
    	$client->setParameterPost(array(
    			'merchantid' => '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e',
    			'amount' => 10,
    			'sessionid' => $sessionId,
    			'purchaseref' => 'ARTICLE1'
    	));
    
    	$response = $client->request(Zend_Http_Client::POST);
    	$token = $response->getRawBody();
    	 
    	//$redirectUrl = 'http://joboffersforme.com/request.php';
    	$redirectUrl = 'https://ompay.orange.ci/e-commerce_test_gw/';
    	$data = array(
    			'merchantid' => '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e',
    			'token' => $token,
    			'amount' => 10,
    			'sessionid' => $sessionId,
    			'purchaseref' => 'ARTICLE1'
    	);
    	 
    	$this->redirectPost($redirectUrl, $data);
    	 
    	die();
    	//echo $response->getRawBody().'<br/>';
    	//Zend_Debug::dump($response); die();
    
    	//Redirecting to OrangeMoney
    
    	//$url = "http://joboffersforme.com/request.php";
    
    	/*$url = "https://ompay.orange.ci/e-commerce/";
    
    	$data =array(
    			'merchantid' => '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e',
    			'token' => $token,
    			'amount' => 10,
    			'sessionid' => $sessionId,
    			'purchaseref' => 'ARTICLE1'
    	);
    
    	$query = http_build_query($data);
    
    	$this->_helper->redirector->gotoUrlAndExit($url . '?' . $query);*/
    
    	//$baseUrl = 'https://ompay.orange.ci/e-commerce/';
    
    	/*$ch = curl_init('http://joboffersforme.com/request.php');
    	 curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($_POST));
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$response = curl_exec($ch);
    
    	die();
    	$curl = curl_init('http://joboffersforme.com/request.php');
    	curl_setopt($curl, CURLOPT_POSTFIELDS, "foo");
    	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    	curl_setopt($curl, CURLOPT_POST, true);
    
    	curl_exec($curl);
    
    	echo 123;
    	die();
    
    
    	$ch = curl_init('http://joboffersforme.com/request.php');
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, "id=12345&name=John");
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);  // RETURN THE CONTENTS OF THE CALL
    	$resp = curl_exec($ch);
    	die();
    
    	echo '<script type="text/javascript"> </script>';
    	die();
    	$baseUrl = "http://joboffersforme.com/request.php";
    
    	$this->_redirector = $this->_helper->getHelper('Redirector');
    	$this->_redirector->setCode(307)
    	->setUseAbsoluteUri(true)
    	->gotoUrlAndExit($baseUrl,
    			array(
    					'merchantid' => '0454fde93a8e2f330adeef80d576e86feca2cbb54e549262605af8a4fd0fb91e',
    					'token' => $token,
    					'amount' => 10,
    					'sessionid' => $sessionId,
    					'purchaseref' => 'ARTICLE1'
    			)
    	);
    
    	die();*/
    }
    
    public function testcongoinAction(){
        
        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(-1);
       $aa =  new Nexva_MobileBilling_Type_Huaweinew();
       
       $bb = $aa->chrage('2420662191234', '2', 'XAF');
       
       echo $bb;
    }
    
    
    
    public function testmtnbeninAction(){

    	ini_set('display_errors',1);
    	ini_set('display_startup_errors',1);
    	error_reporting(-1);
    	$aa =  new Nexva_MobileBilling_Type_Huaweibenin();
    	 //97977777 22966517753
    	//$bb = $aa->sendsms('22961119914', 'TEST', $chapId = null);61944027
    //	$bb = $aa->sendsms('22961944027', 'TEST', $chapId = null);
    	
    	$bb = $aa->sendsms('22997977086', 'TEST from neXva', $chapId = null);
    	
    	 
    	echo $bb;
    }
    
    public function testmtnbeninpayAction(){
    
    	ini_set('display_errors',1);
    	ini_set('display_startup_errors',1);
    	error_reporting(-1);
    	$aa =  new Nexva_MobileBilling_Type_Huaweibenin();
    
    //	$buildUrl = $aa->doPayment(110721, 71259, 39528, 22961255483, 'Test App', 0.0000001);
    //	$buildUrl = $aa->doPayment(110721, 71259, 39528, 22967001891, 'Test App', 0.0000001);
    	$buildUrl = $aa->doPayment(110721, 71259, 39528, 22961944027, 'Test App', 0.0000001);
    
    	echo $buildUrl;
    }
    
    
    public function testmtnbeninpaymAction(){
    
    	ini_set('display_errors',1);
    	ini_set('display_startup_errors',1);
    	error_reporting(-1);
    	$aa =  new Nexva_MobileBilling_Type_HuaweibeninMobileMoney();
    //	22962000023 22962000023 22962000023
    
    	$no = $this->getRequest()->getParam('msisdn');
    	$buildUrl = $aa->doPayment(110721, 71259, 39528, $no, 'Test App', 100);
    
    	echo $bb;
    }


    public function cbAction(){
        
     //   'id_ent'    =>  'B4107C95-16AD-7927-A5B6-471C6EF02A8A',
     //   'id_temp'   =>  '7AF7D692-BBEE-5522-6961-4F8775C743CB',

        $url = 'https://www.pagali.cv/pagali/index.php?r=pgPaymentInterface/ecommercePayment';
        $data = array(
            'id_ent'    =>  'E46ECE05-2971-31F3-39BD-819B8D503772',
            'id_temp'   =>  'DA624A65-C6C9-EECE-C2FD-1DED96531A40',
            'order_id'  =>  '12345',
            'currency_code' => 'CVE',
            'return'    =>  'http://api.nexva.com/testone/cb-return',
            'notify'    =>  'http://api.nexva.com/testone/cb-return',
            'total'     =>  '1.50',
            'item_name' =>  'nexva_purchase',
            'quantity'  =>  '2',
            'item_number'   => '234',              //optional parameter
            'amount'    =>  '0.75',
            'total_item'    =>  '1.50'
        );

        ?>
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <script type="text/javascript">
                    function closethisasap() {
                        document.forms["redirectpost"].submit();
                    }
                </script>
            </head>
            <body onload="closethisasap();">
            <form name="redirectpost" method="post" action="<?php echo $url; ?>">
                <?php
                if ( !is_null($data) ) {
                    foreach ($data as $k => $v) {
                        echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
                    }
                }
                ?>
            </form>
            </body>
            </html>
        <?php
        //exit;


//----------------------------------------------------------------------------------------------------------------------------
        /*$chapId = 107760;
        $appId = 30494;
        $buildId = 57430;
        $mobileNo = 123456789;
        $price = 6.00;
        $appName = 'test-app-name';

        //Get payment gateway Id of the CHAP
        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);

        $pgType = $pgDetails->gateway_id;
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        //Call Nexva_MobileBilling_Factory and create relevant instance
        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

        //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
        $pgClass->addMobilePayment($chapId, $appId, $buildId, $mobileNo, $price, $paymentGatewayId);

        //Do the transaction and get the build url
        $buildUrl = $pgClass->doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price);*/
    }

    public function cbReturnAction(){

        //$headers = apache_request_headers();
        //Zend_Debug::dump($headers);

        $params = $this->_getAllParams();
        Zend_Debug::dump($params);
        die('Hooooo');

    }

    public function caboAppsAction(){
        die('jkkkbklb');
    }
    
    
    
    public function sendsmsAction() {
        
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        $time_start = microtime(true);
    
    	$cvs_path='mtnf.csv';
    	$chapId = 21134;
    	$pgUsersModel = new Api_Model_PaymentGatewayUsers();
    	$pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
    	$massage='Sell, Shop, Smile. Shop the best prices and make money selling your products online with Jumia - http://nextapps.mtnonline.com/36383';
    	$pgType = $pgDetails->gateway_id;
    	$pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);

    	
    	$mobailNumbers = @fopen($cvs_path, 'r');
    	$i = 0;
    	$smsLog = new Api_Model_SmsLog();
    	while (($line = fgetcsv($mobailNumbers,'', ',')) !== false) {
    	    
    	    foreach($line  as $mobileNumber ) {
    	        $result = $pgClass->sendsms($mobileNumber, $massage, $chapId);
    	        
    	        if($result==1){
    	        	$smsLog->loggedSMS($massage, $mobileNumber,'SEND');
    	        	 
    	        	Zend_Debug::dump($result);
    	        	Zend_Debug::dump($mobileNumber);
    	        	 
    	        
    	        }else{
    	        	$smsLog->loggedSMS($massage, $mobileNumber,'FAIL');
    	        }
    	        
    
    	        Zend_Debug::dump($mobileNumber);
    	        
    	    }
    	    
    	    $time_end = microtime(true);
    	    
    	    //dividing with 60 will give the execution time in minutes other wise seconds
    	    $execution_time = ($time_end - $time_start)/60;
    	    
    	    //execution time of the script
    	    echo '<b>Total Execution Time:</b> '.$execution_time.' Mins';

    	    die();
  

    		
    		
    		$i++;
    	}
    	
    	die();
 
    }

    
    
 public function testnAction()
    
    {
echo '<link rel="stylesheet" type="text/css" href="/pagali_app/css/templates.css" media="screen, projection" />';
echo '<script type="text/javascript" src="/pagali_app/js/payment_page.js"></script>';
    
    	 // Prepare POST data
    	 $query = array();
    	 $query['id_ent'] = 'B4107C95-16AD-7927-A5B6-471C6EF02A8A';
    	 $query['id_temp'] = '7AF7D692-BBEE-5522-6961-4F8775C743CB';
    	 $query['order_id'] = '12345';
    	 $query['currency_code'] = 'CVE';
    	 $query['return']    =  'http://api.nexva.com/testone/cb';
    	 $query['notify']    =  'http://api.nexva.com/testone/cb';
    	 $query['total']    =  '1.50';
    	 $query['item_name'] =  'nexva_purchase';
    	 $query['quantity'] =  '2';
    	 $query['item_number']   = '3';
    	 $query['amount']    =  '0.75';
    	 $query['total_item']  =  '1.50';
    	 // Prepare query string
    	 $query_string = '';
    	 foreach ($query as $key=>$value) {
    	 	$query_string .= $key.'='.urlencode($value).'&';
    	 }
    	 $query_string = rtrim($query_string, '&');
    	 

    	 // Open connection
    	 $ch = curl_init();
    	 
    	 //set the url, number of POST vars, POST data
    	 curl_setopt($ch,CURLOPT_URL, 'https://www.pagali.cv/pagali_app/index.php?r=pgPaymentInterface/ecommercePayment');
    	 curl_setopt($ch,CURLOPT_POST, count($query));
    	 curl_setopt($ch,CURLOPT_POSTFIELDS, $query_string);
    	 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	 curl_setopt($ch, CURLOPT_HEADER, 0);
    	 
    	 // Execute post
    	 $result = curl_exec($ch);
    	 
    	 Zend_Debug::dump($result);die();
    	 
    	 // Close connection
    	 curl_close($ch);
    	 
    	 echo '<link rel="stylesheet" type="text/css" href="https://www.pagali.cv/pagali_app/css/templates.css" media="screen, projection" />';
    	 echo '<script type="text/javascript" src="https://www.pagali.cv/pagali_app/js/payment_page.js"></script>';
    	 
    	 
    }

    public  function categoryAction(){
        //Validate Heder params
        //$headersParams = $this->validateHeaderParams();

        $userAgent = 'Mozilla/5.0 (Linux; U; Android 4.0.4; en-gb; GT-I9300 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
        $chapId = 81604;
        //$langCode = $headersParams['langCode'];
        //$chapLanguageId = $headersParams['langCode'];

        $grade = 4;
        $langCode = 'en';

        //check if grade has been provided
        if($langCode === null || empty($langCode)){
            //$this->__echoError("1006","Language code is Empty", self::BAD_REQUEST_CODE);
        }

        //check if grade has been provided
        if($grade === null || empty($grade)){

            //$this->__echoError("11000",$this->getTranslatedText($langCode, '11000') ? $this->getTranslatedText($langCode, '11000') : "Grade Id Empty", self::BAD_REQUEST_CODE);
        }


        $languageModel = new Api_Model_Languages();
        $langId = $languageModel->fetchRow($languageModel->select()->where('code = ?',$langCode));
        //Zend_Debug::dump($langId->id);die();

        $ApiModel = new Nexva_Api_QelasyApi();
        $allCategories = $ApiModel->categoryAction($chapId, $langId->id, $grade);

        Zend_Debug::dump($allCategories);die();

        if (count($allCategories) > 0)
        {
            //$this->getResponse()->setHeader('Content-type', 'application/json');
            //Zend_Debug::dump($allCategories);die();
            //echo str_replace('\/','/',json_encode($allCategories));
            //$this->loggerInstance->log('Response ::' . json_encode($allCategories),Zend_Log::INFO);

        }
        else
        {
            echo 'Data not found!';die();
            //$this->__echoError("3000", $this->getTranslatedText($langCode, '3000') ? $this->getTranslatedText($langCode, '3000') : "Data Not found", self::BAD_REQUEST_CODE);
        }
    }

    public function testPgClassAction(){

        $pgUsersModel = new Api_Model_PaymentGatewayUsers();
        $pgDetails = $pgUsersModel->getGatewayDetailsByChap(110721);

        $pgType = $pgDetails->gateway_id;
        $paymentGatewayId = $pgDetails->payment_gateway_id;

        $pgClass = Nexva_MobileBilling_Factory::createFactory($pgType);
        //Zend_Debug::dump($pgClass);die();

        //Airtel Rwanda test Number 250735285444, 250734014536, 250734010864, chap_id = 114306
        //Airtel Nigeria test Number 2347012966965 chap_id = 81449
        //Airtel Malawi test Number 265997201172 chap_id = 163302
        //Airtel Gabon test Number 24104187540 chap_id = 110721

        $buildUrl = $pgClass->doPayment(110721, 71259, 39528, 24104155639, 'Test App', 1);

        //$pgClass->sendsms(265997201172, 'test-message', 163302);

        Zend_Debug::dump($buildUrl);die();

    }
    


}





class Uganda_Soap_Functions
{

    // Define the method as a PHP function
    function hello($name) {

        return 'Hello, ' . $name;


    }

    function processRequest($p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $p10, $p11, $p12)
    {
        $result = '';

        $result = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:b2b="http://b2b.mobilemoney.mtn.zm_v1.0/">
                   <soapenv:Header/> <soapenv:Body>
                   <b2b:processRequestResponse>';

        $result .= '<return><name>'.$p2->name.'</name><value>'.$p2->value.'</value></return><return><name>'.$p10->name.'</name><value>'.$p10->value.'</value></return><return><name></name><value></value></return><return><name>ThirdPartyAcctRef</name><value>'.$p4->value.'</value></return><return><name></name><value></value></return>';

        $result .= '</b2b:processRequestResponse>
                    </soapenv:Body>
                    </soapenv:Envelope>';



        //return array('return'=>array('name'=>'n1','value'=>'v1'),'return'=>array('name'=>'n2','value'=>'v2'));

        $objMain = new stdClass();

        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj3 = new stdClass();
        $obj4 = new stdClass();
        $obj5 = new stdClass();

        $obj1->return->name = $p2->name;
        $obj1->return->value = $p2->value;

        $obj2->return->name = $p10->name;
        $obj2->return->value = $p10->value;

        $obj3->return->name = '';
        $obj3->return->value = '';

        $obj4->return->name = 'ThirdPartyAcctRef';
        $obj4->return->value = $p4->value;

        $obj5->return->name = '';
        $obj5->return->value = '';

        /*$obj->return[1]['name'] = $p10->name;
        $obj->return[1]['value'] = $p10->value;

        $obj->return[2]['name'] = '';
        $obj->return[2]['value'] = '';

        $obj->return[3]['name'] = 'ThirdPartyAcctRef';
        $obj->return[3]['value'] = $p4->value;

        $obj->return[4]['name'] = '';
        $obj->return[4]['value'] = '';*/

        return array($obj1,$obj2,$obj3,$obj4,$obj5);

        //return '<return><name>'.$p2->name.'</name><value>'.$p2->value.'</value></return>';
        //return $result;
    }
    
    
   private function do_call($host, $port, $request) {
    
    	$fp = fsockopen($host, $port, $errno, $errstr);
    	$query = "POST /home/servertest.php HTTP/1.0\nUser_Agent: My Egg Client\nHost: ".$host."\nContent-Type: text/xml\nContent-Length: ".strlen($request)."\n\n".$request."\n";
    
    	if (!fputs($fp, $query, strlen($query))) {
    		$errstr = "Write error";
    		return 0;
    	}
    
    	$contents = '';
    	while (!feof($fp)) {
    		$contents .= fgets($fp);
    	}
    
    	fclose($fp);
    	return $contents;
    }
    
    
    


}