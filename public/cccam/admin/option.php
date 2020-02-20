<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}



if(isset($_POST['en']) && $_POST['en'] == "1") {
	$sql_update = "UPDATE cccam_option SET opt_enable_auto_cccamcfg = '1'";
	mysql_query($sql_update)  or die (mysql_error());
	exec("sudo ../script/enable_auto_update");
}
if(isset($_POST['en']) && $_POST['en'] == "0") {
	$sql_update = "UPDATE cccam_option SET opt_enable_auto_cccamcfg = '0'";
	mysql_query($sql_update) or die (mysql_error());
	exec("sudo ../script/disable_auto_update");
}
	
$sql_opt = "SELECT * FROM cccam_option";
$query_opt = mysql_query($sql_opt);
$result_opt = mysql_fetch_assoc($query_opt);

$selected = $result_opt['opt_enable_auto_cccamcfg'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Admin Panel - <?php echo basename($_SERVER['REQUEST_URI']);?></title>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
a:link {
	color: #FFF;
}
a:visited {
	color: #FFF;
}
a:hover {
	color: #900;
}
a:active {
	color: #FFF;
}
body {
	background-color: #5B7CFF;
}
</style>
</head>

<body>
<?php include"top.inc.php";?><br />
<table width="900" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="200" valign="top"><?php include"menu.inc.php";?></td>
    <td width="10">&nbsp;</td>
    <td valign="top"><table width="690" border="0" cellpadding="0" cellspacing="0" class="Contorno">
      <tr>
        <td valign="top" bgcolor="#84C1DF"><table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">Option ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
          <form id="form2" name="form2" method="post" action="">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="397" height="20" bgcolor="#5B7CFF" class="TestoContenuto">Enable auto load cccam.cfg , and auto disable fline ::</td>
                <td width="250" height="20" bgcolor="#5B7CFF" class="TestoContenuto"><p>
                  <label>
                    <input type="radio" name="en" value="1" id="en_0" <?php if($selected == "1") { echo " checked=\"checked\" " ;} ?>  />
                    Enable</label>
                  <br />
                  <label>
                    <input name="en" type="radio" id="en_1" value="0" <?php if($selected == "0") { echo " checked=\"checked\" " ;} ?> />
                    Disable</label>
                </p></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Enable Auto Sid Update Sky Italia ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Not Implemented</td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Enable Auto Update Oscam ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Not Implemented</td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Enable Client Update ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Not Implemented</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td><input name="button2" type="submit" class="LoginBox" id="button2" value="Update" /></td>
              </tr>
            </table>
          </form>
<br /></td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        </tr>
      </table></td>
  </tr>
</table>
<br /><?php include"bottom.inc.php";?>
</body>
</html>