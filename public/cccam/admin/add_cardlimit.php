<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}


$sql_clrows = "SELECT * FROM cccam_channelinfo GROUP BY chan_ident";
$query_clrows = mysql_query($sql_clrows);
$num_rows = mysql_num_rows($query_clrows);

$sql_cardhoplimit = "SELECT * FROM list_uphops";
$query_cardhoplimit = mysql_query($sql_cardhoplimit);


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
              <td bgcolor="#003366">Add Card Limit ::</td>
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
                <td bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
                <td bgcolor="#5B7CFF"></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Card Limit ::</td>
                <td bgcolor="#5B7CFF"><select name="fline_cardlimit" class="LoginBox" id="fline_cardlimit">
                  <option value="">--Select--</option>
                  <?php while($result_cardlimit = mysql_fetch_array($query_clrows)) { ?>
                  <option value="<?php echo $result_cardlimit['chan_caid'] . ":" . $result_cardlimit['chan_ident'];?>"><?php echo $result_cardlimit['chan_caid'] . ":" . $result_cardlimit['chan_ident'] . " - " . $result_cardlimit['chan_provider'];?></option>
                  <?php  } ?>
                </select>
                  <select name="fline_cardlimitHops" class="LoginBox" id="fline_cardlimitHops">
                    <option value="">-</option>
                    <?php while($result_cardhoplimit = mysql_fetch_array($query_cardhoplimit)) { ?>
                    <option value="<?php echo ":".$result_cardhoplimit['uphops_value'];?>"><?php echo $result_cardhoplimit['uphops_value'];?></option>
                    <?php } ?>
                  </select></td>
              </tr>
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
                <td bgcolor="#5B7CFF"><input name="add" type="hidden" id="add" value="cardlimit" />
                  <input name="fline_id" type="hidden" id="fline_id" value="<?php echo $_GET['fline_id'];?>" /></td>
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