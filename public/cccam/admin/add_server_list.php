<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}


$sql_wemu = "SELECT * FROM list_wantemus";
$query_wemu = mysql_query($sql_wemu);

$sql_upho = "SELECT * FROM list_reshare";
$query_upho = mysql_query($sql_upho);




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
              <td bgcolor="#003366">Add Server ::</td>
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
                <td width="200" height="20" bgcolor="#5B7CFF" class="TestoContenuto">Server Ip/Hostname ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><label for="server_host"></label>
                  <input name="server_host" type="text" class="LoginBox" id="server_host" /></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Server Port ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><label for="server_port"></label>
                  <input name="server_port" type="text" class="LoginBox" id="server_port" /></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Server Wantemu ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><label for="server_wantemu"></label>
                  <select name="server_wantemu" class="LoginBox" id="server_wantemu">
                    <?php while($result_wemu = mysql_fetch_assoc($query_wemu)) { ?>
                    <option value="<?php echo $result_wemu['wantemus_value'];?>"><?php echo $result_wemu['wantemus_value'];?></option>
                    <?php }?>
                  </select></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Server UpHops ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><label for="server_uphops"></label>
                  <select name="server_uphops" class="LoginBox" id="server_uphops">
                  <?php while($result_upho = mysql_fetch_assoc($query_upho)) {
					  $uphops = "{ " . $result_upho['reshare_value'] . " }";
					   ?>
                    <option value="<?php echo $uphops;;?>" ><?php echo $uphops;?></option>
                    <?php }?>
                  </select></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Add Server to All Fline</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><input name="all" type="checkbox" id="all" value="0" />
                  <label for="all"></label>                  
                  <label for="radio"> ( if checked all fline have this server )</label></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><input name="button2" type="submit" class="LoginBox" id="button2" value="Add Server" />
                  <input name="add_server" type="hidden" id="add_server" value="ok" /></td>
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