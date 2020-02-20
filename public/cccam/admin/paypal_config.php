<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}


$sql_paypal = "SELECT * FROM cccam_paypal";
$query_paypal = mysql_query($sql_paypal);
$result_paypal = mysql_fetch_assoc($query_paypal);

$email = $result_paypal['paypal_email'];
$pay1 = $result_paypal['paypal_cur_1'];
$pay3 = $result_paypal['paypal_cur_3'];
$pay6 = $result_paypal['paypal_cur_6'];
$pay12 = $result_paypal['paypal_cur_12'];
$cur_code = $result_paypal['paypal_cur_code'];
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
              <td bgcolor="#003366">Paypal Config ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
          <form id="form2" name="form2" method="post" action="insert_confirm.php">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Paypal E-mail ::</td>
                <td width="450" bgcolor="#5B7CFF" class="TestoContenuto"><label for="paypal_email"></label>
                  <input name="paypal_email" type="text" class="LoginBox" id="paypal_email" value="<?php echo $email;?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Pay Abo 1 ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><input name="paypal_cur_1" type="text" class="LoginBox" id="paypal_cur_1" value="<?php echo $pay1;?>" size="8" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Pay Abo 2 ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><input name="paypal_cur_3" type="text" class="LoginBox" id="paypal_cur_3" value="<?php echo $pay3;?>" size="8" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Pay Abo 3 ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><input name="paypal_cur_6" type="text" class="LoginBox" id="paypal_cur_6" value="<?php echo $pay6;?>" size="8" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Pay Abo 4</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><input name="paypal_cur_12" type="text" class="LoginBox" id="paypal_cur_12" value="<?php echo $pay12;?>" size="8" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Pay Currency ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><input name="paypal_cur_code" type="text" class="LoginBox" id="paypal_cur_code" value="<?php echo $cur_code;?>" size="6" /></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td><input name="paypal" type="hidden" id="paypal" value="update" /></td>
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