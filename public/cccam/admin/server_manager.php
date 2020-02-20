<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}

if(isset($_GET['action']) && $_GET['action'] == "delete") {
	$server_id = $_GET['server_id'];
	$sql = "DELETE FROM cccam_server_list WHERE server_id = '$server_id'";
	mysql_query($sql) or die (mysql_error());
	
	$sql_client = "DELETE FROM cccam_servers WHERE server_list_id = '$server_id'";
	mysql_query($sql_client) or die (mysql_error());
}

$sql_server = "SELECT * FROM cccam_server_list";
$query_server = mysql_query($sql_server);



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
              <td bgcolor="#003366">Server Manager ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="200" height="20" bgcolor="#5B7CFF" class="TestoContenuto">Total Server::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
              </tr>
          </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">Servers ::</td>
              </tr>
            </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">&nbsp;</td>
              </tr>
            </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="551" height="20" bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
                <td height="20" colspan="3" bgcolor="#5B7CFF" class="TestoContenuto"><div align="center"><a href="add_server_list.php"><img src="../img/add.png" width="16" height="16" border="0" /></a></div></td>
              </tr>
              <?php while($result_server = mysql_fetch_assoc($query_server)) { 
			  $id =  $result_server['server_id'];
			  $host = $result_server['server_host'];
			  $port = $result_server['server_port'];
			  $wemu = $result_server['server_wantemu'];
			  $upho = $result_server['server_uphops'];
			  
			  ?>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $host . " " . $port . " " . $wemu . " " . $upho;?></td>
                <td width="33" height="20" bgcolor="#5B7CFF" class="TestoContenuto"><div align="center"><a href="edit_server_list.php?server_id=<?php echo $id;?>"><img src="../img/application_edit.png" width="16" height="16" border="0" /></a></div></td>
                <td width="33" bgcolor="#5B7CFF" class="TestoContenuto"><div align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?action=delete&server_id=".$id;?>"><img src="../img/cross.png" width="16" height="16" border="0" /></a></div></td>
                <td width="33" bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
              </tr>
              <?php }?>
          </table>
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