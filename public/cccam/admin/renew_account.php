<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}

$sql = "SELECT * FROM cccam_accrenew ORDER BY ren_id DESC ";
$query = mysql_query($sql);

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
              <td bgcolor="#003366">Renew Account List ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td width="25%" bgcolor="#003366" class="TestoContenuto">User</td>
              <td width="25%" bgcolor="#003366" class="TestoContenuto">Pay Date</td>
              <td width="25%" bgcolor="#003366" class="TestoContenuto">Quote</td>
              <td width="25%" bgcolor="#003366" class="TestoContenuto">Valid To</td>
            </tr>
          </table>
          <form id="form2" name="form2" method="post" action="">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <?php while($result = mysql_fetch_assoc($query)) { ?>
              <tr>
                <td width="25%" height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result['user_id'];?></td>
                <td width="25%" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result['ren_date'];?></td>
                <td width="25%" height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result['quote_value'];?></td>
                <td width="25%" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result['quote_to'];?></td>
              <?php } ?>
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