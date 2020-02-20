<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}

$sql = "SELECT COUNT(user_name) FROM cccam_user";
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


$sql_list_user = "SELECT *, COUNT(cccam_fline.user_id) as sum FROM cccam_user, cccam_quote, cccam_fline WHERE cccam_user.user_id = cccam_quote.user_id AND cccam_user.user_id = cccam_fline.user_id GROUP BY cccam_user.user_username ORDER BY cccam_quote.quote_from DESC LIMIT $primo, $per_page";
$query_list_user = mysql_query($sql_list_user);

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
              <td bgcolor="#003366">Edit User Quote ::</td>
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
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
            <?php while($result_list_user = mysql_fetch_array($query_list_user)) {
				
				$al = $result_list_user['quote_to'];
				$tipo = "G";
				$current=date("d-m-Y");

				$rim = datediff($tipo, $current, $al);
				?>
            <tr>
                <td width="150" height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo "<div title='Total Fline " .$result_list_user['sum']."'><a href='edit_user_quote.php?user_id=".$result_list_user['user_id']."'>". $result_list_user['user_username']."</a> (".$result_list_user['sum'].") </div>";?></td>
                <td width="200" height="20" bgcolor="#5B7CFF" class="TestoContenuto"><?php echo $result_list_user['user_email'];?></td>
                <td width="150" height="20" bgcolor="#5B7CFF" ><span class="TestoContenuto">
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
                <td width="33" bgcolor="#5B7CFF" ><div align="center"><a href="edit_user_quote.php?user_id=<?php echo $result_list_user['user_id'];?>"><span class="TestoContenuto"><img src="../img/application_edit.png" alt="" width="16" height="16" border="0" /></span></a></div></td>
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
<br />
<?php include"bottom.inc.php";?>
</body>
</html>