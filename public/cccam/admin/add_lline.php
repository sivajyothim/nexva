<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}
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
              <td bgcolor="#003366">Add  L-Line ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
          <form id="form" NAME="form" method="post" onSubmit="return nameempty();" action="insert_confirm.php">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Hostname ::</td>
                <td bgcolor="#5B7CFF"><label for="user_name"></label>
                  <input name="lline_hostname" type="text" class="LoginBox" id="textfield2" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Port ::</td>
                <td bgcolor="#5B7CFF"><input name="lline_port" type="text" class="LoginBox" id="textfield13" size="10" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Username ::</td>
                <td bgcolor="#5B7CFF"><input name="lline_username" type="text" class="LoginBox" id="lline_username" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Password ::</td>
                <td bgcolor="#5B7CFF"><input name="lline_password" type="text" class="LoginBox" id="textfield3" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Caid ::</td>
                <td bgcolor="#5B7CFF"><input name="lline_caid" type="text" class="LoginBox" id="textfield4" size="15" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ident ::</td>
                <td bgcolor="#5B7CFF"><input name="lline_ident" type="text" class="LoginBox" id="textfield5" size="15" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Hops ::</td>
                <td bgcolor="#5B7CFF"><input name="lline_hops" type="text" class="LoginBox" id="textfield6" size="10" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
                <td bgcolor="#5B7CFF"><input name="add_lline" type="hidden" id="add_lline" value="ok" /></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td><input name="button2" type="submit" class="LoginBox" id="button2" value="Add" /></td>
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
<?php mysql_close($query);?>