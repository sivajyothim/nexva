<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}



$sql_tot = "SELECT SUM(quote_value) as tot FROM cccam_quote WHERE quote_value != '-1'";
$query_tot = mysql_query($sql_tot);
$result_tot = mysql_fetch_assoc($query_tot);
	
	


$tot_users = "SELECT COUNT(user_id) as tot FROM cccam_user";
$query_tot_users = mysql_query($tot_users);
$result_tot_users = mysql_fetch_assoc($query_tot_users);
$tot_users = $result_tot_users['tot'];

$tot_fline = "SELECT COUNT(fline_id) as tot FROM cccam_fline";
$query_tot_fline = mysql_query($tot_fline);
$result_tot_fline = mysql_fetch_assoc($query_tot_fline);
$tot_fline = $result_tot_fline['tot'];

$tot_cline = "SELECT COUNT(cline_id) as tot FROM cccam_cline";
$query_tot_cline = mysql_query($tot_cline);
$result_tot_cline = mysql_fetch_assoc($query_tot_cline);
$tot_cline = $result_tot_cline['tot'];

$tot_nline = "SELECT COUNT(nline_id) as tot FROM cccam_nline";
$query_tot_nline = mysql_query($tot_nline);
$result_tot_nline = mysql_fetch_assoc($query_tot_nline);
$tot_nline = $result_tot_nline['tot'];

$tot_lline = "SELECT COUNT(lline_id) as tot FROM cccam_lline";
$query_tot_lline = mysql_query($tot_lline);
$result_tot_lline = mysql_fetch_assoc($query_tot_lline);
$tot_lline = $result_tot_lline['tot'];

$tot_rline = "SELECT COUNT(rline_id) as tot FROM cccam_rline";
$query_tot_rline = mysql_query($tot_rline);
$result_tot_rline = mysql_fetch_assoc($query_tot_rline);
$tot_rline = $result_tot_rline['tot'];
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
              <td bgcolor="#003366">Statistic ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Total Users ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $tot_users;?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Total F-Line ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $tot_fline;?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Total C-Line ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $tot_cline;?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Total N-Line ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $tot_nline;?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Total L-Line ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $tot_lline;?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Total R-Line ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $tot_rline;?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Total Quote ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_tot['tot'];?></td>
              </tr>
              <tr>
                <td width="200" height="20" bgcolor="#5B7CFF" class="TestoContenuto">Total Expire User ::</td>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
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