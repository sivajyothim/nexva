<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}

$sql_select = "SELECT * FROM cccam_channelinfo GROUP BY chan_provider";
$query_select = mysql_query($sql_select);


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
              <td bgcolor="#003366">Oscam.srvid ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
<table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="250" bgcolor="#5B7CFF" >&nbsp;</td>
                <td bgcolor="#5B7CFF" >&nbsp;</td>
              </tr>
              <?php while($result_select = mysql_fetch_assoc($query_select)) { ?>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto" ><?php echo $result_select['chan_provider'];?></td>
                <td height="20" bgcolor="#5B7CFF" ><a href="oscam.srvid.make.php?provider=<?php echo $result_select['chan_provider'];?>"><img src="../img/buildings.png" title="Make" width="16" height="16" border="0" /></a></td>
              </tr>
              <?php } ?>
              <tr>
                <td bgcolor="#5B7CFF" >&nbsp;</td>
                <td bgcolor="#5B7CFF" >&nbsp;</td>
              </tr>
              <tr>
                <td bgcolor="#84C1DF" >&nbsp;</td>
                <td bgcolor="#84C1DF" >&nbsp;</td>
              </tr>
              <tr>
                <td bgcolor="#84C1DF" >&nbsp;</td>
                <td bgcolor="#84C1DF" >&nbsp;</td>
              </tr>
          </table>          <br /></td>
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