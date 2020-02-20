<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}

$fline_id = $_GET['fline_id'];

$sql_edit = "SELECT * FROM cccam_fline WHERE fline_id = '$fline_id' ";
$query_edit = mysql_query($sql_edit);
$result_edit = mysql_fetch_array($query_edit);



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
              <td bgcolor="#003366">Edit Ftp Client::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
          <form id="form" NAME="form" method="post" action="insert_confirm.php">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Ftp Update Active::</td>
                <td bgcolor="#5B7CFF"><select name="ftp_active" class="LoginBox" onclick="ChangeFTP();" id="ftp_active">
                  <option value="1" <?php if($result_edit['ftp_active'] == "1") { echo "selected=\"selected\"";} ?> >Yes</option>
                  <option value="0" <?php if($result_edit['ftp_active'] == "0") { echo "selected=\"selected\"";} ?> >No</option>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Ip/Hostname::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_ip" type="text" class="LoginBox" id="ftp_ip" value="<?php echo $result_edit['ftp_ip'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Port::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_port" type="text" class="LoginBox" id="textfield3" value="<?php echo $result_edit['ftp_port'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Username::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_user" type="text" class="LoginBox" id="textfield4" value="<?php echo $result_edit['ftp_user'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Password::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_pass" type="text" class="LoginBox" id="textfield5" value="<?php echo $result_edit['ftp_pass'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Local Directory::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_local" type="text" class="LoginBox" id="textfield6" value="<?php echo $result_edit['ftp_local'];?>" size="50" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Remote Directory::</td>
                <td bgcolor="#5B7CFF"><select name="ftp_remote" class="LoginBox" id="ftp_remote">
                  <option value="etc">/etc</option>
                  <option value="varetc">/var/etc</option>
                </select></td>
              </tr>
              <tr>
                <td class="TestoContenuto">&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="TestoContenuto">&nbsp;</td>
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
<br />
<?php include"bottom.inc.php";?>
</body>
</html>
<?php mysql_close($query);?>