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
  
  document.getElementById("cline_cardlimit").style.color="red";
  document.form.cline_cardlimit.disabled = true;
  document.form.cline_cardlimit.style.backgroundColor="#CCC";
  document.form.cline_cardlimit.value = "";
  
  }
else
  {
  document.getElementById("cline_reshare").style.color="#000";
  document.form.cline_reshare.disabled = false;
  document.form.cline_reshare.style.backgroundColor="#FFC";
  document.form.cline_reshare.value = "<?php echo $result_cline['cline_caid_id_hops'];?>";
  
  document.getElementById("cline_cardlimit").style.color="#000";
  document.form.cline_cardlimit.disabled = false; 
  document.form.cline_cardlimit.style.backgroundColor="#FFC";
  document.form.cline_cardlimit.value = "<?php echo $result_cline['cline_cardlimit'];?>";
  
  
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
              <td bgcolor="#003366">Edit C-Line ::</td>
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
                <td bgcolor="#5B7CFF"><select name="user_id" class="LoginBox" id="user_id">
                  <option value="0">--No Associates--</option>
                  <?php while($result_user = mysql_fetch_assoc($query_user)) { ?>
                  <option value="<?php echo $result_user['user_id'];?>" <?php if($result_user['user_id'] == $result_cline['user_id']) { echo " selected=\"selected\" ";} ?>><?php echo $result_user['user_username'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Hostname ::</td>
                <td bgcolor="#5B7CFF"><input name="cline_hostname" type="text" class="LoginBox" id="textfield2" value="<?php echo $result_cline['cline_hostname'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Port ::</td>
                <td bgcolor="#5B7CFF"><input name="cline_port" type="text" class="LoginBox" id="cline_port" value="<?php echo $result_cline['cline_port'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Username ::</td>
                <td bgcolor="#5B7CFF"><input name="cline_username" type="text" class="LoginBox" id="textfield3" value="<?php echo $result_cline['cline_username'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Password ::</td>
                <td bgcolor="#5B7CFF"><input name="cline_password" type="text" class="LoginBox" id="textfield4" value="<?php echo $result_cline['cline_password'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Want Emus ::</td>
                <td bgcolor="#5B7CFF"><label for="cline_wantemus"></label>
                  <select name="cline_wantemus" class="LoginBox" onchange="Change();" id="cline_wantemus">
                  <option value="">-- Select --</option>
                    <?php while($result_wantemus = mysql_fetch_assoc($query_wantemus)) { ?>
                    <option value="<?php echo $result_wantemus['wantemus_value'];?>" <?php if($result_cline['cline_wantemus'] == $result_wantemus['wantemus_value']) { echo " selected=\"selected\" "; } ?>><?php echo $result_wantemus['wantemus_value'];?></option>
                    <?php } ?>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Hops ::</td>
                <td bgcolor="#5B7CFF"><select name="cline_reshare" class="LoginBox" id="cline_reshare">
                  <option value="">-- Select --</option>
                  <?php while($result_reshare = mysql_fetch_assoc($query_reshare)) { ?>
                  <option value="<?php echo $result_reshare['reshare_value'];?>" <?php if($result_reshare['reshare_value'] == $result_cline['cline_caid_id_hops']) { echo " selected=\"selected\"";}?>><?php echo $result_reshare['reshare_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Card Limit ::</td>
                <td bgcolor="#5B7CFF"><label for="cline_cardlimit"></label>
                  <textarea name="cline_cardlimit" cols="45" rows="5" class="LoginBox" id="cline_cardlimit"></textarea>
             <a href="add_cardlimit_cline.php?cline_id=<?php echo $_GET['cline_id'];?>"> <img src="../img/add.png" alt="Add Card Limit" width="16" height="16" border="0" /></a></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td><input name="edit_cline" type="hidden" id="edit_cline" value="ok" />
                  <input name="cline_id" type="hidden" id="cline_id" value="<?php echo $_GET['cline_id'];?>" /></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
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
<br /><?php include"bottom.inc.php";?>
</body>
</html>
<?php mysql_close($query);?>