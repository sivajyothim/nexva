<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}

// List Menu UpHops


$sql_ulevel = "SELECT * FROM list_ulevel";
$query_ulevel = mysql_query($sql_ulevel);


$user_id = $_GET['user_id'];

$sql_edit = "SELECT * FROM cccam_user WHERE user_id = '$user_id' ";
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
              <td bgcolor="#003366">Edit User ::</td>
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
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Name ::</td>
                <td bgcolor="#5B7CFF"><label for="user_name"></label>
                  <input name="user_name" type="text" class="LoginBox" id="user_name" value="<?php echo $result_edit['user_name'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Surname ::</td>
                <td bgcolor="#5B7CFF"><input name="user_surname" type="text" class="LoginBox" id="user_surname" value="<?php echo $result_edit['user_surname'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Street ::</td>
                <td bgcolor="#5B7CFF"><input name="user_street" type="text" class="LoginBox" id="user_street" value="<?php echo $result_edit['user_street'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Number ::</td>
                <td bgcolor="#5B7CFF"><input name="user_number" type="text" class="LoginBox" id="user_number" value="<?php echo $result_edit['user_number'];?>" size="5" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Zip Code ::</td>
                <td bgcolor="#5B7CFF"><input name="user_zip_code" type="text" class="LoginBox" id="user_zip_code" value="<?php echo $result_edit['user_zip_code'];?>" size="8" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">City ::</td>
                <td bgcolor="#5B7CFF"><input name="user_city" type="text" class="LoginBox" id="user_city" value="<?php echo $result_edit['user_city'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Phone ::</td>
                <td bgcolor="#5B7CFF"><input name="user_phone" type="text" class="LoginBox" id="user_phone" value="<?php echo $result_edit['user_phone'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Email ::</td>
                <td bgcolor="#5B7CFF"><input name="user_email" type="text" class="LoginBox" id="user_email" value="<?php echo $result_edit['user_email'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">User Level ::</td>
                <td bgcolor="#5B7CFF"><select name="user_level" class="LoginBox" id="user_level">
                <?php while($result_ulevel = mysql_fetch_assoc($query_ulevel)) { ?>
                  <option value="<?php echo $result_ulevel['ulev_value'];?>" <?php if($result_ulevel['ulev_value'] == $result_edit['user_level']) { echo " selected=\"selected\""; }?>><?php echo $result_ulevel['ulev_value'];?></option>
                 
                  
                <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Username ::</td>
                <td bgcolor="#5B7CFF"><input name="user_username" type="text" disabled="disabled" class="LoginBox" id="user_username" value="<?php echo $result_edit['user_username'];?>" size="15" /></td>
                </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Password ::</td>
                <td bgcolor="#5B7CFF"><input name="user_password" type="text" disabled="disabled" class="LoginBox" id="user_password" onchange="this.form.fline_password.value=this.value;" value="<?php echo $result_edit['user_password'];?>" size="30" /></td>
              </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Note ::</td>
                <td bgcolor="#5B7CFF"><label for="user_note"></label>
                  <textarea name="user_note" cols="45" rows="5" class="LoginBox" id="user_note"><?php echo $result_edit['user_note'];?></textarea></td>
              </tr>
              <tr>
                <td class="TestoContenuto">&nbsp;</td>
                <td><input name="edit" type="hidden" id="edit" value="user" />
                  <input name="user_id" type="hidden" id="user_id" value="<?php echo $_GET['user_id'];?>" /></td>
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