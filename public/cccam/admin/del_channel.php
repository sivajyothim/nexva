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
              <td bgcolor="#003366"> Provider To Edit ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td width="50" bgcolor="#003366">Caid</td>
              <td width="50" bgcolor="#003366">Ident</td>
              <td width="50" bgcolor="#003366">Chaid</td>
              <td width="80" bgcolor="#003366">Provider</td>
              <td width="180" bgcolor="#003366">Channel name</td>
              <td width="100" bgcolor="#003366">Category</td>
              <td width="70" bgcolor="#003366">Encoding</td>
              <td width="70" bgcolor="#003366">Sat</td>
            </tr>
          </table>
          <br />
          <form id="form2" name="form2" method="post" action="insert_confirm.php">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TestoContenuto">
     <?php 
	 $array = $_POST['provider'];

		foreach($array as $value) {

			$sql = "SELECT  * FROM cccam_channelinfo WHERE chan_provider =  '$value'";
			$query = mysql_query($sql);


		while($result=mysql_fetch_array($query)) { 
		$color = ($color == '6699FF' ? '0066FF' : '6699FF');
		?>
              <tr>
                <td width="50" bgcolor="<?php echo "#".$color;?>"><?php echo $result['chan_caid'];?></td>
                <td width="50" bgcolor="<?php echo "#".$color;?>"><?php echo $result['chan_ident'];?></td>
                <td width="50" bgcolor="<?php echo "#".$color;?>"><?php echo $result['chan_chaid'];?></td>
                <td width="80" bgcolor="<?php echo "#".$color;?>"><?php echo $result['chan_provider'];?></td>
                <td width="180" bgcolor="<?php echo "#".$color;?>"><?php echo $result['chan_channel_name'];?></td>
                <td width="100" bgcolor="<?php echo "#".$color;?>"><?php echo $result['chan_category'];?></td>
                <td width="70" bgcolor="<?php echo "#".$color;?>"><?php echo $result['chan_encription'];?></td>
                <td width="40" bgcolor="<?php echo "#".$color;?>"><?php echo $result['chan_sat'];?></td>
                <td width="30" bgcolor="<?php echo "#".$color;?>"><input name="chan_id[]" type="checkbox" id="chan_id[]" value="<?php echo $result['chan_id'];?>" /></td>
              </tr>
			  <?php
      			  }
				}
				?>
                <tr>
                <td colspan="9"><input name='del' type='hidden' id="del" value='channel'>&nbsp;</td>
                </tr>
                <tr>
                  <td colspan="9"><div align="center">
                    <input name="del_channel" type="submit" class="LoginBox" id="del_channel" value="Delete" />
                  </div></td>
                </tr>
            </table>
          </form></td>
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