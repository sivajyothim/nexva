<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}
$search = $_GET['user_search'];
$sql = "SELECT * FROM cccam_user WHERE user_name LIKE '%".$search."%' OR user_email LIKE '%".$search."%' OR user_zip_code LIKE '%".$search."%' OR user_username LIKE '%".$search."%'";
$query = mysql_query($sql);
$res_count = mysql_num_rows($query);



// numero totale di records
$tot_records = $res_count;

// risultati per pagina(secondo parametro di LIMIT)
$per_page = 20;

// numero totale di pagine
$tot_pages = ceil($tot_records / $per_page);

// pagina corrente
$current_page = (!$_GET['page']) ? 1 : (int)$_GET['page'];

// primo parametro di LIMIT
$primo = ($current_page - 1) * $per_page;


$sql_list_user = "SELECT *, COUNT(cccam_fline.user_id) as sum FROM cccam_user, cccam_quote, cccam_fline WHERE cccam_user.user_name LIKE '%".$search."%' OR user_email LIKE '%".$search."%' OR user_zip_code LIKE '%".$search."%' OR user_username LIKE '%".$search."%' AND cccam_user.user_id = cccam_quote.user_id AND cccam_user.user_id = cccam_fline.user_id GROUP BY cccam_user.user_username ORDER BY cccam_quote.quote_from DESC LIMIT $primo, $per_page";
$query_list_user = mysql_query($sql_list_user);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script language="Javascript">
function toggleDiv(id,flagit) {
if (flagit=="1"){
if (document.layers) document.layers[''+id+''].visibility = "show"
else if (document.all) document.all[''+id+''].style.visibility = "visible"
else if (document.getElementById) document.getElementById(''+id+'').style.visibility = "visible"
}
else
if (flagit=="0"){
if (document.layers) document.layers[''+id+''].visibility = "hide"
else if (document.all) document.all[''+id+''].style.visibility = "hidden"
else if (document.getElementById) document.getElementById(''+id+'').style.visibility = "hidden"
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
              <td bgcolor="#003366">List User ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td width="150" bgcolor="#003366">Username</td>
              <td width="200" bgcolor="#003366">E-Mail</td>
              <td width="150" bgcolor="#003366">Expire</td>
              <td width="100" bgcolor="#003366">Level</td>
              <td width="33" bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
          <form id="form" name="form" method="post" action="del_channel.php">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
            <?php while($result_list_user = mysql_fetch_array($query_list_user)) {
			
				$al = $result_list_user['quote_to'];
				$tipo = "G";
				$current=date("d-m-Y");

				$rim = datediff($tipo, $current, $al);
				?>
              <tr>
                <td width="150" height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo "<div title='Total Fline " .$result_list_user['sum']."'><a href='list_user_detail.php?user_id=".$result_list_user['user_id']."'>". $result_list_user['user_username']."</a> (".$result_list_user['sum'].") </div>";?></td>
                <td width="200" height="20" bgcolor="#5B7CFF"  class="TestoContenuto"><?php echo $result_list_user['user_email'];?></td>
                <td width="150" height="20" bgcolor="#5B7CFF"><span class="TestoContenuto">
				<?php 
				if($al == "-1")
				{	
				echo "<div class='Illimitato'>Illimitato</div>";
				} else 
				{ if($rim <= "0") 
				{ echo "<div class='Scaduto'>Scaduto</div>"; 
				} else 
				{ echo $rim . " Giorni/o"; 
				}
				};
				?></span></td>
                <td width="100" height="20" bgcolor="#5B7CFF" ><span class="TestoContenuto"><?php echo $result_list_user['user_level'];?></span></td>
                <td width="33"bgcolor="#5B7CFF" ><div align="center"><a href="edit_user.php?user_id=<?php echo $result_list_user['user_id'];?>"><img src="../img/application_edit.png" width="16" height="16" border="0" align="middle" /></a></div></td>
              </tr>
              <?php } ?>
            </table>
        </form>
          <br /></td>
        </tr>
        <tr>
        <td><div class="Contorno" align="center">
          <?php $paginazione = "";
for($i = 1; $i <= $tot_pages; $i++) {
if($i == $current_page) {
$paginazione .= "<span class='Paginazione'>".$i . " " . "</span>";
} else {
$paginazione .= "<span class='Paginazione'><a href=\"?page=$i&user_search=$search\" title=\"Vai alla pagina $i\">$i</a></span> ";
}
}
$paginazione .= " ";
echo $paginazione;
?>
          <br /><?php echo "<span class='Paginazione'>Tot record : ". $res_count . "</span>";?>
        </div></td>
        </tr>
      </table></td>
  </tr>
</table>
<br /><?php include"bottom.inc.php";?>
</body>
</html>