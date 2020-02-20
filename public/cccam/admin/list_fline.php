<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}

if(isset($_GET['action']) && $_GET['action'] == "disable") {
	$fline_id = $_GET['fline_id'];
	$sql = "UPDATE cccam_fline SET fline_active = '0' WHERE fline_id = '$fline_id'";
	mysql_query($sql);
}
if(isset($_GET['action']) && $_GET['action'] == "enable") {
	$fline_id = $_GET['fline_id'];
	$sql = "UPDATE cccam_fline SET fline_active = '1' WHERE fline_id = '$fline_id'";
	mysql_query($sql);
}

if(isset($_GET['action']) && $_GET['action'] == "delete") {
	$fline_id = $_GET['fline_id'];
	$sql = "DELETE FROM cccam_fline WHERE fline_id = '$fline_id'";
	mysql_query($sql);
}

$sql = "SELECT COUNT(fline_id) FROM cccam_fline";
$query = mysql_query($sql);
$res_count = mysql_fetch_row($query);



// numero totale di records
$tot_records = $res_count[0];

// risultati per pagina(secondo parametro di LIMIT)
$per_page = 20;

// numero totale di pagine
$tot_pages = ceil($tot_records / $per_page);

// pagina corrente
$current_page = (!$_GET['page']) ? 1 : (int)$_GET['page'];

// primo parametro di LIMIT
$primo = ($current_page - 1) * $per_page;


// esecuzione seconda query con LIMIT
$sql_limit = "SELECT * FROM cccam_fline LIMIT $primo, $per_page ";
$query_limit = mysql_query($sql_limit);




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
        <td valign="top" bgcolor="#84C1DF"><br />
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">F-Line ::</td>
              </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
          <br />
          
         <table width="650" border="0" align="center" cellpadding="0" cellspacing="1" class="TestoContenuto">
		 <tr>
         <td width="550" bgcolor="#5B7CFF">&nbsp;</td>
         <td colspan="3" bgcolor="#5B7CFF"><div align="center"><a href="add_fline.php"><img src="../img/add.png" alt="" width="16" height="16" border="0" /></a></div></td>
         </tr>
		 <?php while($result_fline=mysql_fetch_array($query_limit)) { 
		  
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
              <td width="550" height="20" bgcolor="#5B7CFF"><?php if($result_fline['fline_active'] == "1") { } else { echo "<span class=\"FDisable\">"; }?><?php echo "F: ".$result_fline['fline_username']." ".$result_fline['fline_password']." ".$result_fline['fline_uphops']." ".$result_fline['fline_shareemus']." ".$result_fline['fline_allowemm'] ." " . $reshare . $chanlimit . $timelimit . $hostlimit; ?></span></td>
              <td width="33" bgcolor="#5B7CFF"><div align="center"><a href="edit_fline.php?fline_id=<?php echo $result_fline['fline_id'];?>"><img src="../img/application_edit.png" width="16" height="16" border="0" /></a></div></td>
              <td width="33" bgcolor="#5B7CFF"> <div align="center"><?php if($result_fline['fline_active'] == "1") { echo "<a href=\"".$_SERVER['PHP_SELF']."?action=disable&fline_id=".$result_fline['fline_id']."&page=".$_GET['page']."\"><img src=\"../img/unlock.png\" title=\"Disable\" border=\"0\" width=\"16\" height=\"16\" /></a>"; } else { echo "<a href=\"".$_SERVER['PHP_SELF']."?action=enable&fline_id=".$result_fline['fline_id']."&page=".$_GET['page']."\"><img src=\"../img/lock.png\" title=\"Enable\" border=\"0\" width=\"16\" height=\"16\" /></a>"; } ?>               </div></td>
              <td width="33" bgcolor="#5B7CFF"><div align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?action=delete&fline_id=".$result_fline['fline_id']."&page=".$_GET['page'];?>"><img src="../img/cross.png" width="16" height="16" border="0" /></a></div></td>
            </tr>
            <?php } ?>
          </table>
          <br /></td>
        </tr>
        <tr>
        <td><div class="Contorno" align="center">
          <?php $paginazione = "";
for($i = 1; $i <= $tot_pages; $i++) {
if($i == $current_page) {
$paginazione .= "<span class='Paginazione'>".$i . " " . "</span>";
} else {
$paginazione .= "<span class='Paginazione'><a href=\"?page=$i\" title=\"Vai alla pagina $i\">$i</a></span> ";
}
}
$paginazione .= " ";
echo $paginazione;
?>
          <br /><?php echo "<span class='Paginazione'>Tot record : ". $res_count[0] . "</span>";?>
        </div></td>
        </tr>
      </table></td>
  </tr>
</table>
<br /><?php include"bottom.inc.php";?>
</body>
</html>
<?php mysql_close($query);?>