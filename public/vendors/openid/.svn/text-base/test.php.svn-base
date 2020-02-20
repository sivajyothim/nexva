<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require 'SimpleOpenID.class.php';

$openid = new SimpleOpenID;
// $_REQUEST['openid'] is the input field the user submitted
$openid->SetIdentity($_REQUEST['openid']);

// ApprovedURL is the url we want to call back to
$openid->SetApprovedURL('http://nexva.com/vendors/openid/test2.php');
$openid->SetTrustRoot('http://nexva.com/');

// I'm also requesting their email address for the creation of their new profile
$openid->SetOptionalFields('email');

$openid->SetOpenIDServer($server);
$openid->SetIdentity($identity);

$server = $openid->GetOpenIDServer();

$identity = $openid->GetIdentity();

$openid->Redirect();

// User::GetOpenIDServer checks the database table 'user_openids' for the 
// user's openid and the associated identity, which saves having to run
// a separate HTTP request if it's not available (see else case).


// send the user to their OpenID provider for authentication
$openid->Redirect();

?>