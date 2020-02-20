<?php 
$openid = new SimpleOpenID;
$identity = $_GET['openid_identity'];

$openid->SetIdentity($identity);
$ok = $openid->ValidateWithServer();

if ($ok) {
  /**
   * Tasks:
   * If user doesn't exist (tested by LoadByOpenID) - create an account
   * If they're new - send them to an activate page with appropriate captcha logic
   * If they existed already, redirect to their home page
   */

  // tries to load a user profile using their openid identity,
  // standard stuff, that would normally be by username.
  if (!$User->LoadUserByOpenID($identity)) {
    // create a new user
    $User->CreateUserFromOpenID($identity, $_GET['openid_sreg_email']);

    // ask the user, as a once off, to prove they're human.
    Utility::Redirect('/activate');
  } else {
    // redirect the user to their home page
    Utility::Redirect('/' . $User->username);
  }
} else if ($openid->IsError() == true) {
  // There was a problem logging in.  This is captured in $error (do a var_dump for details)
  $error = $openid->GetError();

  $msg = "OpenID auth problem\nCode: {$error['code']}\nDescription: {$error['description']}\nOpenID: {$identity}\n";

  // error message handling is done further along in the code, but ensure the user
  // can pass on as much information as possible to replicate the bug.
} else { 
  // General error, not due to comms
  $Error = 'Authorisation failed, please check the credentials entered and double check the use of caplocks.';
}

?>
