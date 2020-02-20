<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}


$sql = "SELECT COUNT(chan_id) FROM cccam_channelinfo";
$query = mysql_query($sql);
$res_count = mysql_fetch_row($query);



// numero totale di records
$tot_records = $res_count[0];

// risultati per pagina(secondo parametro di LIMIT)
$per_page = 50;

// numero totale di pagine
$tot_pages = ceil($tot_records / $per_page);

// pagina corrente
$current_page = (!$_GET['page']) ? 1 : (int)$_GET['page'];

// primo parametro di LIMIT
$primo = ($current_page - 1) * $per_page;


// esecuzione seconda query con LIMIT
$sql_limit = "SELECT * FROM cccam_channelinfo ORDER BY chan_provider ASC LIMIT $primo, $per_page ";
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
        <td valign="top" bgcolor="#84C1DF"><table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="Contorno">
          <tr>
            <td><form id="form2" name="form2" method="get" action="list_channel_search.php?page=1">
              <label for="chan_list"></label>
              <input name="chan_list" type="text" class="LoginBox" id="chan_list" />
              <input name="button2" type="submit" class="LoginBox" id="button2" value="Search" />
            </form></td>
          </tr>
        </table>
          <br />
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
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td width="50" bgcolor="#003366">&nbsp;</td>
              <td width="50" bgcolor="#003366">&nbsp;</td>
              <td width="50" bgcolor="#003366">&nbsp;</td>
              <td width="80" bgcolor="#003366">&nbsp;</td>
              <td width="180" bgcolor="#003366">&nbsp;</td>
              <td width="100" bgcolor="#003366">&nbsp;</td>
              <td width="70" bgcolor="#003366">&nbsp;</td>
              <td width="70" bgcolor="#003366">&nbsp;</td>
            </tr>
          </table>
          <br />
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TestoContenuto"><?php while($result=mysql_fetch_array($query_limit)) { 
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
              <td width="70" bgcolor="<?php echo "#".$color;?>"><?php echo $result['chan_sat'];?></td>
            </tr><?php } ?>
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