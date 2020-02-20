<?php
session_start();
include"../config.inc.php";
$sql_value = "SELECT * FROM cccam_paypal";
$query_value = mysql_query($sql_value);
$result_query = mysql_fetch_assoc($query_value);

$email = $result_query['paypal_email'];
$currency = $result_query['paypal_cur_1'];
$currency_code = $result_query['paypal_cur_code'];

/*  PHP Paypal IPN Integration Class Demonstration File
 *  4.16.2005 - Micah Carrick, email@micahcarrick.com
 *
 *  This file demonstrates the usage of paypal.class.php, a class designed  
 *  to aid in the interfacing between your website, paypal, and the instant
 *  payment notification (IPN) interface.  This single file serves as 4 
 *  virtual pages depending on the "action" varialble passed in the URL. It's
 *  the processing page which processes form data being submitted to paypal, it
 *  is the page paypal returns a user to upon success, it's the page paypal
 *  returns a user to upon canceling an order, and finally, it's the page that
 *  handles the IPN request from Paypal.
 *
 *  I tried to comment this file, aswell as the acutall class file, as well as
 *  I possibly could.  Please email me with questions, comments, and suggestions.
 *  See the header of paypal.class.php for additional resources and information.
*/

// Setup class
require_once('paypal.class.php');  // include the class file
$p = new paypal_class;             // initiate an instance of the class
//$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // testing paypal url
$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';     // paypal url
            
// setup a variable for this script (ie: 'http://www.micahcarrick.com/paypal.php')
$this_script = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

// if there is not action variable, set the default action of 'process'
if (empty($_GET['action'])) $_GET['action'] = 'process';  

switch ($_GET['action']) {
    
   case 'process':      // Process and order...

      // There should be no output at this point.  To process the POST data,
      // the submit_paypal_post() function will output all the HTML tags which
      // contains a FORM which is submited instantaneously using the BODY onload
      // attribute.  In other words, don't echo or printf anything when you're
      // going to be calling the submit_paypal_post() function.
 
      // This is where you would have your form validation  and all that jazz.
      // You would take your POST vars and load them into the class like below,
      // only using the POST values instead of constant string expressions.
 
      // For example, after ensureing all the POST variables from your custom
      // order form are valid, you might have:
      //
      // $p->add_field('first_name', $_POST['first_name']);
      // $p->add_field('last_name', $_POST['last_name']);
      
      $p->add_field('business', $email);
      $p->add_field('return', $this_script.'?action=success');
      $p->add_field('cancel_return', $this_script.'?action=cancel');
      $p->add_field('notify_url', $this_script.'?action=ipn');
      $p->add_field('item_name', 'Quota Socio : '.$_SESSION['username'].' , 1 mese ');
      $p->add_field('amount', $currency);
	  $p->add_field('currency_code', $currency_code);

      $p->submit_paypal_post(); // submit the fields to paypal
      //$p->dump_fields();      // for debugging, output a table of all the fields
      break;
      
   case 'success':      // Order was successful...
   
      // This is where you would probably want to thank the user for their order
      // or what have you.  The order information at this point is in POST 
      // variables.  However, you don't want to "process" the order until you
      // get validation from the IPN.  That's where you would have the code to
      // email an admin, update the database with payment status, activate a
      // membership, etc.  
 
      echo "Success ! Thank you for your order.";
      foreach ($_POST as $key => $value) {
		   echo "$key: $value<br>"; 
		   }
	  
	  // Actual Date
	  $sql = "SELECT * FROM cccam_quote WHERE user_id = " . $_SESSION['userid'];
	  $query = mysql_query($sql);
	  $result = mysql_fetch_assoc($query);
	  
	  // Date from db to update
	  
	  $old_date = $result['quote_to'];
	  
	  // add 1 month
	  
	  $new_date = strtotime(date("d-m-Y", strtotime($old_date)) . " +1 month");
	  $value = date("d-m-Y",$new_date);
	  
	  // Update the database with new date and quote
	  
	  $new_quote = "25"; // Change if need
	  $today = date("d-m-Y");
	  
	  $sql_update = "UPDATE cccam_quote SET quote_value = '$new_quote', quote_to = '$value', quote_from = '$today' WHERE user_id = " . $_SESSION['userid'];
	  mysql_query($sql_update);
	  
	  // Add into table cccam_accrenew value
	  
	  $sql_ren = "INSERT INTO cccam_accrenew ( ren_id, ren_date, user_id, quote_value, quote_to ) VALUES ( NULL, '$today', '".$_SESSION['username']."' , '$new_quote', '$value')";
	  mysql_query($sql_ren);
	  
	  // Check if fline is disable and enable
	  
	  $sql = "SELECT * FROM cccam_fline WHERE user_id = " . $_SESSION['userid'];
	  $query = mysql_query($sql);
	  $result = mysql_fetch_assoc($query);
	  
	  	if($result['fline_active'] == "0") {
			$sql_update = "UPDATE cccam_fline SET fline_active = '1' WHERE user_id = " . $_SESSION['userid'];
	 		 mysql_query($sql_update);
		}
      
      // You could also simply re-direct them to another page, or your own 
      // order status page which presents the user with the status of their
      // order based on a database (which can be modified with the IPN code 
      // below).
      
      break;
      
   case 'cancel':       // Order was canceled...

      // The order was canceled before being completed.
 
      echo "Canceled ! The order was canceled.";
      
      break;
      
   case 'ipn':          // Paypal is calling page for IPN validation...
   
      // It's important to remember that paypal calling this script.  There
      // is no output here.  This is where you validate the IPN data and if it's
      // valid, update your database to signify that the user has payed.  If
      // you try and use an echo or printf function here it's not going to do you
      // a bit of good.  This is on the "backend".  That is why, by default, the
      // class logs all IPN data to a text file.
      
      if ($p->validate_ipn()) {
          
         // Payment has been recieved and IPN is verified.  This is where you
         // update your database to activate or process the order, or setup
         // the database with the user's order details, email an administrator,
         // etc.  You can access a slew of information via the ipn_data() array.
  
         // Check the paypal documentation for specifics on what information
         // is available in the IPN POST variables.  Basically, all the POST vars
         // which paypal sends, which we send back for validation, are now stored
         // in the ipn_data() array.
  
         // For this example, we'll just email ourselves ALL the data.
         $subject = 'Instant Payment Notification - Recieved Payment';
         $to = 'sbarboff@live.it';    //  your email
         $body =  "An instant payment notification was successfully recieved\n";
         $body .= "from ".$p->ipn_data['payer_email']." on ".date('m/d/Y');
         $body .= " at ".date('g:i A')."\n\nDetails:\n";
         
         foreach ($p->ipn_data as $key => $value) { $body .= "\n$key: $value"; }
         mail($to, $subject, $body);
      }
      break;
 }     

?>