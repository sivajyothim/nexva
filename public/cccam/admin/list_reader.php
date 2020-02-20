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
	$reader_id = $_GET['reader_id'];
	$sql = "UPDATE cccam_reader SET reader_active = '0' WHERE reader_id = '$reader_id'";
	mysql_query($sql);
}
if(isset($_GET['action']) && $_GET['action'] == "enable") {
	$reader_id = $_GET['reader_id'];
	$sql = "UPDATE cccam_reader SET reader_active = '1' WHERE reader_id = '$reader_id'";
	mysql_query($sql);
}

if(isset($_GET['action']) && $_GET['action'] == "delete") {
	$reader_id = $_GET['reader_id'];
	$sql = "DELETE FROM cccam_reader WHERE reader_id = '$reader_id'";
	mysql_query($sql);
}

$sql = "SELECT COUNT(reader_id) FROM cccam_reader";
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
$sql_reader = "SELECT * FROM cccam_reader ORDER BY reader_active DESC LIMIT $primo, $per_page  ";
$query_reader = mysql_query($sql_reader);




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
              <td bgcolor="#003366">List Reader ::</td>
              </tr>
          </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td width="550" bgcolor="#003366"><a href="add_fline.php"></a></td>
              <td width="50" bgcolor="#003366"><div align="center">Edit</div></td>
              <td width="50" bgcolor="#003366"><div align="center">E/D</div></td>
              </tr>
          </table>
          <br />
          
         <table width="650" border="0" align="center" cellpadding="0" cellspacing="1" class="TestoContenuto">
		
         <tr>
              <td bgcolor="#5B7CFF">&nbsp;</td>
              <td colspan="3" bgcolor="#5B7CFF"><div align="center"><a href="add_reader.php"><img src="../img/add.png" alt="" width="16" height="16" border="0" /></a></div></td>
              </tr>
         <?php while($result_reader = mysql_fetch_assoc($query_reader)) {
		
			 
			 ?>
         
         
            
            <tr>
              <td height="20" bgcolor="#5B7CFF"><?php if($result_reader['reader_active'] == "1") { } else { echo "<span class=\"FDisable\">"; }?>                <?php echo $result_reader['reader_value_name'] . " " . $result_reader['reader_value'] . " " . $result_reader['reader_misc'] ;?></td>
              <td width="33" height="20" bgcolor="#5B7CFF"><?php if($result_reader['reader_misc'] == "") { } else { ?><div align="center"><a href="edit_reader.php?reader_id=<?php  echo $result_reader['reader_id'];?>"><img src="../img/application_edit.png" alt="" width="16" height="16" border="0" /></a></div><?php }?></td>
              <td width="33" bgcolor="#5B7CFF"> <div align="center">
                <?php if($result_reader['reader_active'] == "1") { echo "<a href=\"".$_SERVER['PHP_SELF']."?action=disable&reader_id=".$result_reader['reader_id']."&page=".$_GET['page']."\"><img src=\"../img/unlock.png\" title=\"Disable\" border=\"0\" width=\"16\" height=\"16\" /></a>"; } else { echo "<a href=\"".$_SERVER['PHP_SELF']."?action=enable&reader_id=".$result_reader['reader_id']."&page=".$_GET['page']."\"><img src=\"../img/lock.png\" title=\"Enable\" border=\"0\" width=\"16\" height=\"16\" /></a>"; } ?>
              </div></td>
              <td width="33" bgcolor="#5B7CFF"><div align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?action=delete&reader_id=".$result_reader['reader_id']."&page=".$_GET['page'];?>"><img src="../img/cross.png" width="16" height="16" border="0" /></a></div></td>
            </tr>
           <?php }?>
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
