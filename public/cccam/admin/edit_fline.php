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

$fline_id = $_GET['fline_id'];

$sql_uphops = "SELECT * FROM list_uphops";
$query_uphops = mysql_query($sql_uphops);

$sql_emuse = "SELECT * FROM list_emusemm";
$query_emuse = mysql_query($sql_emuse);

$sql_emm = "SELECT * FROM list_emusemm";
$query_emm = mysql_query($sql_emm);

$sql_reshare = "SELECT * FROM list_reshare";
$query_reshare = mysql_query($sql_reshare);

$sql_clrows = "SELECT * FROM cccam_channelinfo GROUP BY chan_caid";
$query_clrows = mysql_query($sql_clrows);
$num_rows = mysql_num_rows($query_clrows);

$sql_chan = "SELECT * FROM cccam_channelinfo";
$query_chan = mysql_query($sql_chan);

$sql_time_from = "SELECT * FROM list_time";
$query_time_from = mysql_query($sql_time_from);

$sql_time_to = "SELECT * FROM list_time";
$query_time_to = mysql_query($sql_time_to);


$sql_server = "SELECT * FROM cccam_server_list";
$query_server = mysql_query($sql_server);

$sql_fline = "SELECT * FROM cccam_fline WHERE fline_id = '$fline_id'";
$query_fline = mysql_query($sql_fline);
$result_fline = mysql_fetch_array($query_fline);



$value = $result_fline['fline_chanlimit'];
$count = count(explode(",", $value));
for($i = 0 ; $i <= $count ; $i++) {
$exp = explode(", ", $value);
											
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
              <td bgcolor="#003366">Edit F-Line ::</td>
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
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Username ::</td>
                <td bgcolor="#5B7CFF"><label for="user_name"></label>
                  <input name="fline_username" type="text" disabled="disabled" class="LoginBox" id="textfield2" value="<?php echo $result_fline['fline_username'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Password ::</td>
                <td bgcolor="#5B7CFF"><input name="fline_password" type="text" disabled="disabled" class="LoginBox" id="textfield13" value="<?php echo $result_fline['fline_password'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">UpHops ::</td>
                <td bgcolor="#5B7CFF"><label for="fline_uphops"></label>
                  <select name="fline_uphops" class="LoginBox" id="fline_uphops">
                    <?php while($result_uphops = mysql_fetch_array($query_uphops)) { ?>
                    <option value="<?php echo $result_uphops['uphops_value'];?>" <?php if($result_uphops['uphops_value'] == $result_fline['fline_uphops']) { echo " selected=\"selected\"";}?>><?php echo $result_uphops['uphops_value'];?></option>
                    <?php } ?>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Share Emus ::</td>
                <td bgcolor="#5B7CFF"><select name="fline_shareemus" class="LoginBox" id="fline_shareemus">
                  <?php while($result_emuse = mysql_fetch_array($query_emuse)) { ?>
                  <option value="<?php echo $result_emuse['muem_value'];?>" <?php if($result_emuse['muem_value'] == $result_fline['fline_shareemus']) { echo " selected=\"selected\"";}?>><?php echo $result_emuse['muem_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Allow Emm ::</td>
                <td bgcolor="#5B7CFF"><select name="fline_allowemm" class="LoginBox" id="fline_allowemm">
                   <?php while($result_emm = mysql_fetch_array($query_emm)) { ?>
                  <option value="<?php echo $result_emm['muem_value'];?>" <?php if($result_emm['muem_value'] == $result_fline['fline_allowemm']) { echo " selected=\"selected\"";}?>><?php echo $result_emm['muem_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Reshare Level ::</td>
                <td valign="top" bgcolor="#5B7CFF"><select name="fline_reshare" class="LoginBox" id="fline_reshare">
                  <?php while($result_reshare = mysql_fetch_array($query_reshare)) { ?>
                  <option value="<?php echo $result_reshare['reshare_value'];?>" <?php if($result_reshare['reshare_value'] == $result_fline['fline_reshare']) { echo " selected=\"selected\"";}?>><?php echo $result_reshare['reshare_value'];?></option>
                  <?php } ?>
                </select></td>
                </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Card Limit ::</td>
                <td valign="top" bgcolor="#5B7CFF"><label for="fline_cardlimit"></label>
                  <textarea name="fline_cardlimit" cols="45" rows="5" class="LoginBox" id="fline_cardlimit"><?php echo $result_fline['fline_cardlimit'];?></textarea>
                  <a href="add_cardlimit.php?fline_id=<?php echo $_GET['fline_id'];?>"><img src="../img/add.png" alt="Add Card Limit" width="16" height="16" border="0" /></a></td>
                </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Chan Limit ::</td>
                <td bgcolor="#5B7CFF">
                <select name="fline_chanlimit[]" size="10" multiple="multiple" class="LoginBox" id="fline_chanlimit">
                  <?php while($result_chan = mysql_fetch_array($query_chan)) { 
				  $value = $result_chan['chan_caid'].":".$result_chan['chan_ident'].":".$result_chan['chan_chaid'];
				  ?>
                  <option value="<?php echo $value;  ?>" <?php if(in_array($value, $exp)) { echo " selected=\"selected\" "; }  ?>><?php echo $result_chan['chan_caid'].":".$result_chan['chan_ident'].":".$result_chan['chan_chaid']." -- ".$result_chan['chan_provider']." - ".$result_chan['chan_channel_name']; ?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Time Limit ::</td>
                <td bgcolor="#5B7CFF"><label for="fline_timelimit"></label>
                  <textarea name="fline_timelimit" cols="45" rows="5" class="LoginBox" id="fline_timelimit"><?php echo $result_fline['fline_timelimit'];?></textarea></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ip/Hostname ::</td>
                <td bgcolor="#5B7CFF"><input name="fline_hostlimit" type="text" class="LoginBox" id="textfield14" value="<?php echo $result_fline['fline_hostlimit'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Update Active ::</td>
                <td bgcolor="#5B7CFF"><select name="ftp_active" class="LoginBox" onclick="ChangeFTP();" id="ftp_active">
                  <option value="1" <?php if($result_fline['ftp_active'] == "1") { echo "selected=\"selected\"";} ?> >Yes</option>
                  <option value="0" <?php if($result_fline['ftp_active'] == "0") { echo "selected=\"selected\"";} ?> >No</option>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Ip/Hostname ::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_ip" type="text" class="LoginBox" id="ftp_ip" value="<?php echo $result_fline['ftp_ip'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Port ::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_port" type="text" class="LoginBox" id="textfield3" value="<?php echo $result_fline['ftp_port'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Username ::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_user" type="text" class="LoginBox" id="textfield4" value="<?php echo $result_fline['ftp_user'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Password ::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_pass" type="text" class="LoginBox" id="textfield5" value="<?php echo $result_fline['ftp_pass'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Local Directory ::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_local" type="text" class="LoginBox" id="textfield6" value="<?php echo $result_fline['ftp_local'];?>" size="50" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ftp Remote Directory ::</td>
                <td bgcolor="#5B7CFF"><input name="ftp_remote" type="text" class="LoginBox" id="ftp_remote" value="<?php echo $result_fline['ftp_remote'];?>" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Allow On Server ::</td>
                <td bgcolor="#5B7CFF"><select name="server_id[]" size="2" multiple="multiple" class="LoginBox" id="server_id[]">
                  <?php while($result_server = mysql_fetch_assoc($query_server)) { 
					$sql_server_select = "SELECT * FROM cccam_servers WHERE fline_id = '$fline_id'";
					$query_server_select = mysql_query($sql_server_select);
					while($result_server_select = mysql_fetch_assoc($query_server_select)) {
					$server_list_id[] = $result_server_select['server_list_id'];
					}
					$id = $result_server['server_id'];
					$host = $result_server['server_host'];
					$port = $result_server['server_port'];
					
					?>
                  <option value="<?php echo $id;?>" <?php if(in_array($id, $server_list_id)) { echo "selected=\"selected\" " ;}?> ><?php echo $host.":".$port; ?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
                <td bgcolor="#5B7CFF"><input name="fline_id" type="hidden" id="fline_id" value="<?php echo $_GET['fline_id'];?>" />
                  <input name="update" type="hidden" id="update" value="fline" /></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
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