<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}

$sql_user_detail = "SELECT * FROM cccam_user WHERE cccam_user.user_id = " . $_SESSION['userid'];
$query_user_detail = mysql_query($sql_user_detail);
$result_user_detail = mysql_fetch_assoc($query_user_detail);

$sql_fline = "SELECT * FROM cccam_fline WHERE cccam_fline.user_id = " . $_SESSION['userid'];
$query_fline = mysql_query($sql_fline);

$sql_quote = "SELECT * FROM cccam_quote WHERE cccam_quote.user_id = " . $_SESSION['userid'];
$query_quote = mysql_query($sql_quote);
$result_quote = mysql_fetch_assoc($query_quote);

$sql_pair = "SELECT * FROM cccam_cline WHERE user_id = " . $_SESSION['userid'];
$query_pair = mysql_query($sql_pair);


$al = $result_quote['quote_to'];
$tipo = "G";
$current=date("d-m-Y");
$rim = datediff($tipo, $current, $al);

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
<?php include"../user/top.inc.php";?><br />
<table width="900" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="200" valign="top"><?php include"../user/menu.inc.php";?></td>
    <td width="10">&nbsp;</td>
    <td valign="top"><table width="690" border="0" cellpadding="0" cellspacing="0" class="Contorno">
      <tr>
        <td valign="top" bgcolor="#84C1DF"><table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>&nbsp;</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">User Detail::</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
            </tr>
          </table>
<table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="250" height="20" bgcolor="#5B7CFF" class="TestoContenuto">Name ::</td>
                <td width="350" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_name'];?></td>
                
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Surname ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_surname'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Street ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_street'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Number ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_number'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Zip Code ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_zip_code'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">City ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_city'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Phone ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_phone'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Email ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_email'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Level ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_level'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Username ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_username'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Password ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_password'];?></td>
              </tr>
              <tr>
                <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Note ::</td>
                <td bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_user_detail['user_note'];?></td>
              </tr>
            </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">F-Line Detail::</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
            <tr>
              <td width="550" bgcolor="#5B7CFF">&nbsp;</td>
            </tr>
            <?php while($result_fline = mysql_fetch_assoc($query_fline)) {
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . " }";
					$chanlimit = NULL;
					$timelimit = NULL;
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = NULL;
					$timelimit = NULL;
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = NULL;
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { }";
					$timelimit = " { }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { }";
					$timelimit = " { }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = NULL;
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] != NULL && 
					   $result_fline['fline_timelimit'] == NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { " . $result_fline['fline_chanlimit'] . " }";
					$timelimit = " { }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
					if($result_fline['fline_cardlimit'] == NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . " }";
					$chanlimit = " { }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] == NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = NULL;
					   }
					if($result_fline['fline_cardlimit'] != NULL && 
					   $result_fline['fline_chanlimit'] == NULL && 
					   $result_fline['fline_timelimit'] != NULL && 
					   $result_fline['fline_hostlimit'] != NULL) {
					$reshare = " { " . $result_fline['fline_reshare'] . ", " . $result_fline['fline_cardlimit'] . " }";
					$chanlimit = " { }";
					$timelimit = " { " . $result_fline['fline_timelimit'] . " }";
					$hostlimit = " " . $result_fline['fline_hostlimit'];
					   }
 				  ?>
            <tr>
              <td width="550" height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php if($result_fline['fline_active'] == "1") { } else { echo "<span class=\"FDisable\">"; }?>
                <?php echo "F: ".$result_fline['fline_username']." ".$result_fline['fline_password']." ".$result_fline['fline_uphops']." ".$result_fline['fline_shareemus']." ".$result_fline['fline_allowemm'] ." " . $reshare . $chanlimit . $timelimit . $hostlimit; ?></td>
            </tr>
            <?php } ?>
            <tr>
              <td height="20" colspan="4" bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">C-Line Pair::</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
            <tr></tr>
            <tr>
              <td width="550" bgcolor="#5B7CFF">&nbsp;</td>
            </tr>
            <?php
			$num = mysql_num_rows($query_pair);
			if($num >= 1) {
			 while($result_cline = mysql_fetch_assoc($query_pair)){
				 $hops_val = $result_cline['cline_caid_id_hops'];
			 $wemu_val = $result_cline['cline_wantemus'];
			 $limit_val = $result_cline['cline_cardlimit'];
			 if($wemu_val == "yes") {
				 $wemu = "yes";
				 $hops = NULL;
				 
			 }
			 if($wemu_val == "no") {
				 $wemu = "no";
				 $hops = " { " . $hops_val . " }";
				
			 }
			if($wemu_val == "no" && $limit_val != NULL) {
				 $wemu = "no";
				 $hops = " { " . $hops_val.", " . $limit_val . " }";
				 
			 } 
			
			
			?>
            <tr>
              <td width="550" bgcolor="#5B7CFF" class="TestoContenuto"><?php if($result_cline['cline_active'] == "1") { } else { echo "<span class=\"FDisable\">"; }?>
                <?php echo "C: " . $result_cline['cline_hostname'] . " " . $result_cline['cline_port'] . " " . $result_cline['cline_username'] . " " . $result_cline['cline_password'] . " " . $wemu . " " . $hops ;  ?></td>
            </tr>
            <?php
			}} else {
				
				
			?>
            <tr>
              <td width="550" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo "No Pair!";   ?></td>
              </tr>
            <?php
			
			}?>
            <tr>
              <td bgcolor="#5B7CFF">&nbsp;</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">Quote Detail::</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
            </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
            <tr>
              <td width="250" height="20" bgcolor="#5B7CFF" class="TestoContenuto">Quote ::</td>
              <td width="350" bgcolor="#5B7CFF" class="TestoContenuto"><?php if($result_quote['quote_value'] == "-1") { echo "Unlimited"; } else { echo $result_quote['quote_value']; } ;?></td>
            </tr>
            <tr>
              <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">From ::</td>
              <td bgcolor="#5B7CFF" class="TestoContenuto"><?php if($result_quote['quote_from'] == "-1") { echo "Unlimited"; }  else { echo $result_quote['quote_from']; };?></td>
            </tr>
            <tr>
              <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">To ::</td>
              <td bgcolor="#5B7CFF" class="TestoContenuto"><?php if($result_quote['quote_to'] == "-1") { echo "Unlimited"; }  else { echo $result_quote['quote_to']; };?></td>
            </tr>
            <tr>
              <td height="20" bgcolor="#5B7CFF" class="TestoContenuto">Expire ::</td>
              <td bgcolor="#5B7CFF" class="TestoContenuto"><?php 
				if($al == "-1")
				{	
				echo "<div class='Illimitato'>Unlimited</div>";
				} else 
				{ if($rim <= "0") 
				{ echo "<div class='Scaduto'>Expired</div>"; 
				} else 
				{ echo $rim . " Day/s"; 
				}
				};
				?></td>
            </tr>
          </table>          <br /></td>
        </tr>
        <tr>
        <td><?php
		?></td>
        </tr>
      </table></td>
  </tr>
</table>
<br /><?php include"../user/bottom.inc.php";?>
</body>
</html>