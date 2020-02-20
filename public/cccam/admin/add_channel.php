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
<script type="text/javascript" language="JavaScript">
function nameempty()
{
        if ( document.ins_single.chan_caid.value == '' || document.ins_single.chan_ident.value == '' || document.ins_single.chan_chaid.value == '' || document.ins_single.chan_provider.value == '' || document.ins_single.chan_channel_name.value == '' || document.ins_single.chan_category.value == '' || document.ins_single.chan_encription.value == '' || document.ins_single.chan_sat.value == '' )
        {
                alert('Please enter all field!!!')
                return false;
        }
}
</script>

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
              <td bgcolor="#003366">Add Single Channel ::</td>
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
          <form id="ins_single" name="ins_single" method="post" action="insert_confirm.php" onSubmit="return nameempty();">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1" class="TestoContenuto">
              <tr>
                <td width="300" bgcolor="#5B7CFF">Caid ::</td>
                <td width="350" bgcolor="#5B7CFF"><input name="chan_caid" type="text" class="BoxEdit" id="chan_caid" size="4"  /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF">Ident ::</td>
                <td bgcolor="#5B7CFF"><input name="chan_ident" type="text" class="BoxEdit" id="chan_ident" size="6" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF">Chan Id ::</td>
                <td bgcolor="#5B7CFF"><input name="chan_chaid" type="text" class="BoxEdit" id="chan_chaid"size="6" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF">Provider ::</td>
                <td bgcolor="#5B7CFF"><input name="chan_provider" type="text" class="BoxEdit" id="chan_provider" size="12" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF">Channel Name ::</td>
                <td bgcolor="#5B7CFF"><input name="chan_channel_name" type="text" class="BoxEdit" id="chan_channel_name" size="27" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF">Category ::</td>
                <td bgcolor="#5B7CFF"><input name="chan_category" type="text" class="BoxEdit" id="chan_category" size="14" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF">Encoding ::</td>
                <td bgcolor="#5B7CFF"><input name="chan_encription" type="text" class="BoxEdit" id="chan_encription" size="8" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF">Sat ::</td>
                <td bgcolor="#5B7CFF"><input name="chan_sat" type="text" class="BoxEdit" id="chan_sat" size="6"/></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td colspan="2"><input name='ins_si' type='hidden' id="ins_si" value='single' />
                  &nbsp;</td>
              </tr>
              <tr>
                <td colspan="2" bgcolor="#003366"><div align="left">
                  <input name="edit_channel" type="submit" class="LoginBox" id="edit_channel" value="Insert" />
                </div></td>
              </tr>
            </table>
          </form>
          <br />
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">Add Multiple Channel ::</td>
            </tr>
          </table>
          <br /></td>
        </tr>
        <tr>
        <td><form action="insert_confirm.php" method="post" enctype="multipart/form-data" name="ins_multiple" id="ins_multiple">
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TestoContenuto">
            <tr>
              <td bgcolor="#5B7CFF"><label for="uploaded_file"></label>
                <input name="uploaded_file" type="file" class="LoginBox" id="uploaded_file" />
                <span class="Testo">
                <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
                </span></td>
            </tr>
            <tr>
              <td width="650"><input name='ins_mu' type='hidden' id="ins_mu" value='multiple' />&nbsp;</td>
            </tr>
            <tr>
              <td bgcolor="#003366"><div align="left">
                <input name="edit_channel2" type="submit" class="LoginBox" id="edit_channel2" value="Insert" />
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