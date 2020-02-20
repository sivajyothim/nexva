<?php
//include_once( 'smppclient.class.php' );
//include_once( 'gsmencoder.class.php' );
//include_once( 'sockettransport.class.php' );
//
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);
//// Construct transport and client
////$transport = new SocketTransport(array('pmg-acg-sms01.ref1.lightsurf.net'),8008);
//
//$transport = new SocketTransport(array('pmg-acg-sms01.ref1.lightsurf.net'),'8008');
//
//
//
//$transport->setRecvTimeout(10000);
//$smpp = new SmppClient($transport);
//
//
//// Activate binary hex-output of server interaction
//$smpp->debug = true;
//$transport->debug = true;
//
//// Open the connection
//// $transport->open();
//$transport->open();
//
//$smpp->bindTransmitter("1432","n3x41A99");
//
//// Optional connection specific overrides
//SmppClient::$sms_null_terminate_octetstrings = false;
//SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
//SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;
//
//// Prepare message
//$message = 'Hello world';
////  $encodedMessage = GsmEncoder::utf8_to_gsm0338($message);
//$encodedMessage = GsmEncoder::utf8_to_gsm0338($message);
//
//
//$from = new SmppAddress('13369570515', SMPP::TON_INTERNATIONAL,SMPP::NPI_E164);
//$to = new SmppAddress('13369570515', SMPP::TON_INTERNATIONAL,SMPP::NPI_E164);
//
//
//
//// Send
////  $smpp->sendSMS($from,$to,$encodedMessage,$tags);
//
//var_dump($smpp->sendSMS($from,$to,$encodedMessage));
//
//// Close connection
//$smpp->close();
//die('dd');
//
//
//


include_once( 'smppclient.class.php' );
include_once( 'gsmencoder.class.php' );
include_once( 'sockettransport.class.php' );

ini_set('display_errors', 'On');
error_reporting(E_ALL);
// Construct transport and client

$transport = new SocketTransport(array('10.80.101.50'),8411);


$transport->setRecvTimeout(10000);
$smpp = new SmppClient($transport);




// Activate binary hex-output of server interaction
$smpp->debug = true;
$transport->debug = true;

// Open the connection
// $transport->open();
$yy = $transport->open();

print_r($yy);die();
$x = $smpp->bindTransmitter("mtnappssm45","nosxvfn4");

// Optional connection specific overrides
SmppClient::$sms_null_terminate_octetstrings = false;
SmppClient::$csms_method = SmppClient::CSMS_PAYLOAD;
SmppClient::$sms_registered_delivery_flag = SMPP::REG_DELIVERY_SMSC_BOTH;

// Prepare message
//$message = 'Hello world';
//  $encodedMessage = GsmEncoder::utf8_to_gsm0338($message);
//$encodedMessage = GsmEncoder::utf8_to_gsm0338($message);


//$from = new SmppAddress('13369570515', SMPP::TON_INTERNATIONAL,SMPP::NPI_E164);
//$to = new SmppAddress('13369570515', SMPP::TON_INTERNATIONAL,SMPP::NPI_E164);

// Prepare message
        $message = 'MTN Congo app-store - from neXva';
        $encodedMessage = GsmEncoder::utf8_to_gsm0338($message);
        $from = new SmppAddress('068661314',SMPP::TON_INTERNATIONAL,SMPP::NPI_E164);
        $to = new SmppAddress('068661314',SMPP::TON_INTERNATIONAL,SMPP::NPI_E164);

// Send
//  $smpp->sendSMS($from,$to,$encodedMessage,$tags);

var_dump($smpp->sendSMS($from,$to,$encodedMessage));

// Close connection
$smpp->close();
die('dd');