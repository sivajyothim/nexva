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

$cline_id = $_GET['cline_id'];

$sql_user = "SELECT * FROM cccam_user";
$query_user = mysql_query($sql_user);

$sql_wantemus = "SELECT * FROM list_wantemus";
$query_wantemus = mysql_query($sql_wantemus);

$sql_reshare = "SELECT * FROM list_reshare";
$query_reshare = mysql_query($sql_reshare);

$sql_cline = "SELECT * FROM cccam_cline WHERE cline_id = '$cline_id'";
$query_cline = mysql_query($sql_cline);
$result_cline = mysql_fetch_assoc($query_cline);

$sql_uphops = "SELECT * FROM list_uphops";
$query_uphops = mysql_query($sql_uphops);

$sql_clrows = "SELECT * FROM cccam_channelinfo GROUP BY chan_caid";
$query_clrows = mysql_query($sql_clrows);
$num_rows = mysql_num_rows($query_clrows);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript" language="javascript">
function Change()
{
	
if (document.form.cline_wantemus.value == "yes")
  { 
  document.getElementById("cline_reshare").style.color="red";
  document.form.cline_reshare.disabled = true;
  document.form.cline_reshare.style.backgroundColor="#CCC";
  document.form.cline_reshare.value = "";
  
  for (i = 0 ; i <= <?php echo $num_rows;?> ; i++) {
  
  document.form.cline_cardlimit[i].style.color="red";
  document.form.cline_cardlimit[i].disabled = true;
  document.form.cline_cardlimit[i].style.backgroundColor="#CCC";
  document.form.cline_cardlimit[i].value = "";
  
  document.form.cline_cardlimitHops[i].style.color="red";
  document.form.cline_cardlimitHops[i].disabled = true;
  document.form.cline_cardlimitHops[i].style.backgroundColor="#CCC";
  document.form.cline_cardlimitHops[i].value = "";
  
  }
  }
else
  {
  document.getElementById("cline_reshare").style.color="#000";
  document.form.cline_reshare.disabled = false;
  document.form.cline_reshare.style.backgroundColor="#FFC";
  document.form.cline_reshare.value = "<?php echo $result_cline['cline_caid_id_hops'];?>";
  
  for (i = 0 ; i <= <?php echo $num_rows;?> ; i++) {
  
  document.form.cline_cardlimit[i].style.color="#000";
  document.form.cline_cardlimit[i].disabled = false; 
  document.form.cline_cardlimit[i].style.backgroundColor="#FFC";
  document.form.cline_cardlimit[i].value = "";
  
  document.form.cline_cardlimitHops[i].style.color="#000";
  document.form.cline_cardlimitHops[i].disabled = false;
  document.form.cline_cardlimitHops[i].style.backgroundColor="#FFC";
  document.form.cline_cardlimitHops[i].value = "";
  }
  
  }
}
</script>
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

<body onload="Change()";>
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
              <td bgcolor="#003366">Add C-Line ::</td>
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
                <td bgcolor="#5B7CFF" class="TestoContenuto">Associates ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="user_id"></label>
                  <select name="user_id" class="LoginBox" id="user_id">
                  	<option value="0">--No Associates--</option>
                    <?php while($result_user = mysql_fetch_assoc($query_user)) { ?>
                    <option value="<?php echo $result_user['user_id'];?>"><?php echo $result_user['user_username'];?></option>
                    <?php } ?>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Hostname ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="cline_hostname" type="text" class="LoginBox" id="textfield2" /></td>
                </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Port ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="cline_port" type="text" class="LoginBox" id="cline_port" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Username ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="cline_username" type="text" class="LoginBox" id="textfield3" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Password ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="cline_password" type="text" class="LoginBox" id="textfield4" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Want Emus ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="cline_wantemus"></label>
                  <select name="cline_wantemus" class="LoginBox" onchange="Change();" id="cline_wantemus">
                  <option value="">-- Select --</option>
                    <?php while($result_wantemus = mysql_fetch_assoc($query_wantemus)) { ?>
                    <option value="<?php echo $result_wantemus['wantemus_value'];?>"><?php echo $result_wantemus['wantemus_value'];?></option>
                    <?php } ?>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Hops ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><select name="cline_reshare" class="LoginBox" id="cline_reshare">
                  <option value="">-- Select --</option>
                  <?php while($result_reshare = mysql_fetch_assoc($query_reshare)) { ?>
                  <option value="<?php echo $result_reshare['reshare_value'];?>"><?php echo $result_reshare['reshare_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Card Limit ::</td>
                <td width="201" valign="top" bgcolor="#5B7CFF">
				
				<?php for($i = 0 ; $i <= $num_rows-1 ; $i++) { 
				$sql_cardlimit = "SELECT * FROM cccam_channelinfo GROUP BY chan_caid";
				$query_cardlimit = mysql_query($sql_cardlimit);
				?>
                <select name="cline_cardlimit[]" class="LoginBox" id="cline_cardlimit">
                <option value="">--Select--</option>
                 <?php while($result_cardlimit = mysql_fetch_array($query_cardlimit)) { ?>
                
                  <option value="<?php echo $result_cardlimit['chan_caid'] . ":" . $result_cardlimit['chan_ident'];?>"><?php echo $result_cardlimit['chan_caid'] . ":" . $result_cardlimit['chan_ident'] . " - " . $result_cardlimit['chan_provider'];?></option>
                  <?php  } ?>
                </select><br />
				<?php } ?>
                </td>
                <td width="245" valign="top" bgcolor="#5B7CFF">
				<?php for($i = 0 ; $i <= $num_rows-1 ; $i++) { 
				$sql_cardhoplimit = "SELECT * FROM list_uphops";
				$query_cardhoplimit = mysql_query($sql_cardhoplimit);
				?>
                  <select name="cline_cardlimitHops[]" class="LoginBox" id="cline_cardlimitHops">
                    <option value="">-</option>
                    <?php while($result_cardhoplimit = mysql_fetch_array($query_cardhoplimit)) { ?>
                    <option value="<?php echo ":".$result_cardhoplimit['uphops_value'];?>"><?php echo $result_cardhoplimit['uphops_value'];?></option>
                    <?php } ?>
                  </select> <br />
                  <?php } ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td colspan="2"><input name="add_cline" type="hidden" id="add_cline" value="ok" /></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td colspan="2"><input name="button2" type="submit" class="LoginBox" id="button2" value="Add" /></td>
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