
<?php
include"../config.inc.php";
$user = strip_tags(trim($_REQUEST['username']));
 
if(strlen($user) <= 0)
{
  echo json_encode(array('code'  =>  -1, 'result'  =>  'Invalid username, please try again.' ));
  die;
}
 
// Query database to check if the username is available
$query = "SELECT * FROM cccam_user WHERE user_username = '$user' ";
$result = mysql_query($query);
$available = mysql_num_rows($result);
// Execute the above query using your own script and if it return you the
// result (row) we should return negative, else a success message.
 
//$available = true;
 
if(!$available)
{
  echo json_encode(array('code'  =>  1, 'result'  =>  "<img src=\"../img/accept.png\" width=\"16\" height=\"16\" />" ));
  die;
}
else
{
  echo  json_encode(array('code'  =>  0, 'result'  =>  "<img src=\"../img/cross.png\" width=\"16\" height=\"16\" />" ));
  die;
}
die;
?>