<?php
/**
* The sample application that demonstrates Google Wallet for Digital Goods API
*
* @copyright 2013  Google Inc. All rights reserved.
* @author Rohit Panwar <panwar@google.com>
*/

/**
 * Generate payload request of the product for purchase.
 */
include_once 'generate_token.php';

?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
  <title>Digital Goods Sample Application</title>
  <meta name='viewport' content='width=device-width, initial-scale=1,
  maximum-scale=1'>
  <script src="https://sandbox.google.com/checkout/inapp/lib/buy.js"></script>
  <script type='text/javascript'>
    function DemoButton(jwt_value) {
      runDemoButton = document.getElementById('runDemoButton');
      google.payments.inapp.buy({
        jwt: jwt_value,
        /*success: function() {console.log('success');},
        failure: function(result) {console.log(result.response.errorType);}*/
		success: successHandler,
        failure: failureHandler
      });
      return false;
    }
	
	var successHandler = function(result){
		alert('success');
		alert(result.toSource())
	}
	
	var failureHandler = function(result){
		alert('fail');
		alert(result.toSource())
	}
	
  </script>
  <link href='css/style.css' rel='stylesheet' type='text/css' />
</head>
<body>
<div>
  <figure>
    <img src='images/cake.jpg' /> <br /> <br />
    <figcaption class='img-label'>
     A Virtual chocolate cake to fill your virtual tummy </figcaption>
  </figure>
  <button id='runDemoButton' value='buy' class='buy-button'
  onclick='DemoButton("<?= $jwtToken ?>");'><b>Purchase</b></button>
</div>
</body>
</html>

