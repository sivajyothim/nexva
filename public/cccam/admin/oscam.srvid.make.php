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
                <td colspan="2" bgcolor="#FFFFFF" class="CCcamTMP"><?php
				$provider =  $_GET['provider'];
				$sql = "SELECT * FROM cccam_channelinfo WHERE chan_provider = '$provider' AND chan_category != '' GROUP BY chan_category";
				$query = mysql_query($sql);
				
				while($result = mysql_fetch_assoc($query)) {
				
				$categorie[] = $result['chan_category'];
				$provider = $result['chan_provider'];
				}
				
				foreach($categorie as $cat) {
					$category = "<br># ". $cat ." <br><br>";
					file_put_contents("oscam.srvid", $category , FILE_APPEND);
	
					$sql = "SELECT * FROM cccam_channelinfo WHERE chan_provider = '$provider' AND chan_category = '$cat'";
					$query = mysql_query($sql);
	
	
						while($result_sky = mysql_fetch_assoc($query)) {
	
							$chan_caid = $result_sky['chan_caid'];
							$chan_chaid = $result_sky['chan_chaid'];
							$chan_provider = $result_sky['chan_provider'];
							$chan_channel_name = $result_sky['chan_channel_name'];
	
	$os_srvid_line = $chan_caid . "|" . $chan_chaid . "|" . $chan_provider . "|" . $chan_channel_name . "|" . "TV" . "<br>";
	file_put_contents("oscam.srvid", $os_srvid_line, FILE_APPEND);
	}}
				$file = file_get_contents("oscam.srvid");
				echo $file;
				exec ("rm oscam.srvid");
				?>
                
                </td>
              </tr>
          </table>          <br /></td>
        </tr>
        <tr>
        <td><?php echo $link;?></td>
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