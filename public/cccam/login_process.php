<?php
// Start session
session_start();

// Include Config for database connection
include"config.inc.php";

// Retrive user e password from post
$username = mysql_real_escape_string($_POST['username']);
$password = mysql_real_escape_string($_POST['password']);

// Query user e pass to database
$sql = "SELECT * FROM cccam_user WHERE user_username = '$username' AND user_password = '$password'";
$query = mysql_query($sql);
$result = mysql_fetch_assoc($query);

// Level value from user and user id
$user_level = $result['user_level'];
$user_id = $result['user_id'];

// If num rown is > 0
if(mysql_num_rows($query) > 0)
{
			// If user level = admin
 	 		if($user_level == "admin") 
			 {
				 	// set session username
				 	$_SESSION['username'] = htmlspecialchars($username);
					$_SESSION['userid'] = $user_id;
					// Redirect
					header("Location: ./admin/list_user.php");
					exit();
  
 			 } else {
				 	// set session username
				 	$_SESSION['username'] = htmlspecialchars($username);
					$_SESSION['userid'] = $user_id;
					// Redirect
					header("Location: ./user/manager.php");
					exit();
			 }
} else {
	// Redirect
	header("Location: index.php?action=loginerror");
	exit();
}
 



?>
