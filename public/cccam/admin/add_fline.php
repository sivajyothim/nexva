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

$sql_user = "SELECT * FROM cccam_user";
$query_user = mysql_query($sql_user);

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

$sql_server_list = "SELECT * FROM cccam_server_list";
$query_server = mysql_query($sql_server_list);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript" language="javascript">
function choice(arg)
//return random index number in valid range of arg array
{
        return Math.floor(Math.random()*arg.length);
}

function randstr(arg)
//return random argument of arg array
{
var str = '';
var seed = choice(arg);
        str = arg[seed];
return str;
}

function initialize()
//use actual time to initialize random function as javascript doesn't provide an initialization function itself
//to get more random, use getMilliseconds() function
//don't use getTime() as it produces numbers larger than 1000 billions, eheh
{
        var count=new Date().getSeconds();
        for (c=0; c<count; c++)
                Math.random();
}

function mkpass()
{
        //use of initialize() can decrease speed of script. On really slow systems, disable it.
        initialize();

        //password length
        var pass_len=8;

        var cons_lo = ['b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z'];
        var cons_up = ['B','C','D','F','G','H','J','K','L','M','N','P','Q','R','S','T','V','W','X','Y','Z'];
        var hard_cons_lo = ['b','c','d','f','g','h','k','m','p','s','t','v','z'];
        var hard_cons_up = ['B','C','D','F','G','H','K','M','P','S','T','V','Z'];
var link_cons_lo = ['h','l','r'];
var link_cons_up = ['H','L','R'];
var vowels_lo = ['a','e','i','o','u'];
var vowels_up = ['A','E','I','U']; //O (letter o) and 0 (number zero) get confused
        var digits = ['1','2','3','4','5','6','7','8','9'];

        //change at will how many times digits appears in names array. Order doesn't matter
        var names = [cons_lo, cons_up, digits, hard_cons_lo, hard_cons_up, digits, link_cons_lo, link_cons_up, digits, vowels_lo, vowels_up, digits];

var newpass= '';
        for(i=0; i<pass_len; i++)
         newpass = newpass + randstr(names[choice(names)]);

return newpass;
}

function pullAjax(){
    var a;
    try{
      a=new XMLHttpRequest()
    }
    catch(b)
    {
      try
      {
        a=new ActiveXObject("Msxml2.XMLHTTP")
      }catch(b)
      {
        try
        {
          a=new ActiveXObject("Microsoft.XMLHTTP")
        }
        catch(b)
        {
          alert("Your browser broke!");return false
        }
      }
    }
    return a;
  }
 
  function validate()
  {
    site_root = '';
    var x = document.getElementById('fline_username');
    var msg = document.getElementById('msg');
    user = x.value;
 
    code = '';
    message = '';
    obj=pullAjax();
    obj.onreadystatechange=function()
    {
      if(obj.readyState==4)
      {
        eval("result = "+obj.responseText);
        code = result['code'];
        message = result['result'];
 
        if(code <=0)
        {
          x.style.border = "1px solid red";
          msg.style.color = "red";
		 
		  msg.style.fontStyle = "bold";
        }
        else
        {
          x.style.border = "1px solid #FFF";
          msg.style.color = "#00CC00";
		
        }
        msg.innerHTML = message;
		
      }
    }
    obj.open("GET",site_root+"validate.php?username="+user,true);
    obj.send(null);
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
          <form id="form" NAME="form" method="post" onSubmit="return nameempty();" action="insert_confirm.php">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">Fline ::</td>
              </tr>
        </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">&nbsp;</td>
              </tr>
            </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">User ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="user_id"></label>
                  <select name="user_id" class="LoginBox" id="user_id">
                   <?php while($result_user = mysql_fetch_assoc($query_user)) { ?>
                    <option value="<?php echo $result_user['user_id'];?>"><?php echo $result_user['user_username'];?></option>
                   <?php } ?>
                  </select></td>
              </tr>
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Username ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="user_name"></label>
                  <input name="fline_username" type="text" onkeyup="validate();" class="LoginBox" id="fline_username" /><div id="msg"></div></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Password ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="fline_password" type="text" class="LoginBox" id="textfield13" />
                  <input type="button" class="LoginBox" onclick="document.form.fline_password.value=mkpass();" value="Generate" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">UpHops ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="fline_uphops"></label>
                  <select name="fline_uphops" class="LoginBox" id="fline_uphops">
                    <?php while($result_uphops = mysql_fetch_array($query_uphops)) { ?>
                    <option value="<?php echo $result_uphops['uphops_value'];?>" <?php if($result_uphops['uphops_value'] == "1") { echo " selected=\"selected\"";}?>><?php echo $result_uphops['uphops_value'];?></option>
                    <?php } ?>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Share Emus ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><select name="fline_shareemus" class="LoginBox" id="fline_shareemus">
                  <?php while($result_emuse = mysql_fetch_array($query_emuse)) { ?>
                  <option value="<?php echo $result_emuse['muem_value'];?>"><?php echo $result_emuse['muem_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Allow Emm ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><select name="fline_allowemm" class="LoginBox" id="fline_allowemm">
                   <?php while($result_emm = mysql_fetch_array($query_emm)) { ?>
                  <option value="<?php echo $result_emm['muem_value'];?>"><?php echo $result_emm['muem_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Reshare Level ::</td>
                <td colspan="2" valign="top" bgcolor="#5B7CFF"><select name="fline_reshare" class="LoginBox" id="fline_reshare">
                  <?php while($result_reshare = mysql_fetch_array($query_reshare)) { ?>
                  <option value="<?php echo $result_reshare['reshare_value'];?>"><?php echo $result_reshare['reshare_value'];?></option>
                  <?php } ?>
                </select></td>
                </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Card Limit ::</td>
                <td width="201" valign="top" bgcolor="#5B7CFF">
				
				<?php for($i = 0 ; $i <= $num_rows-1 ; $i++) { 
				$sql_cardlimit = "SELECT * FROM cccam_channelinfo GROUP BY chan_caid";
				$query_cardlimit = mysql_query($sql_cardlimit);
				?>
                <select name="fline_cardlimit[]" class="LoginBox" id="fline_cardlimit">
                <option value="">--Select--</option>
                 <?php while($result_cardlimit = mysql_fetch_array($query_cardlimit)) { ?>
                
                  <option value="<?php echo $result_cardlimit['chan_caid'] . ":" . $result_cardlimit['chan_ident'];?>"><?php echo $result_cardlimit['chan_caid'] . ":" . $result_cardlimit['chan_ident'] . " - " . $result_cardlimit['chan_provider'];?></option>
                  <?php  } ?>
                </select><br />
				<?php } ?>
                </td>
                <td width="245" valign="top" bgcolor="#5B7CFF">
				<?php for($i = 0 ; $i <= $num_rows-1 ; $i++) { 
				$sql_cardhoplimit = "SELECT * FROM list_uphops";
				$query_cardhoplimit = mysql_query($sql_cardhoplimit);
				?>
                  <select name="fline_cardlimitHops[]" class="LoginBox" id="fline_cardlimitHops">
                    <option value="">-</option>
                    <?php while($result_cardhoplimit = mysql_fetch_array($query_cardhoplimit)) { ?>
                    <option value="<?php echo ":".$result_cardhoplimit['uphops_value'];?>"><?php echo $result_cardhoplimit['uphops_value'];?></option>
                    <?php } ?>
                  </select> <br />
                  <?php } ?></td>
              </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Chan Limit ::</td>
                <td colspan="2" bgcolor="#5B7CFF">
                <select name="fline_chanlimit[]" size="10" multiple="multiple" class="LoginBox" id="fline_chanlimit">
                  <?php while($result_chan = mysql_fetch_array($query_chan)) { ?>
                  <option value="<?php echo $result_chan['chan_caid'].":".$result_chan['chan_ident'].":".$result_chan['chan_chaid']; ?>"><?php echo $result_chan['chan_caid'].":".$result_chan['chan_ident'].":".$result_chan['chan_chaid']." -- ".$result_chan['chan_provider']." - ".$result_chan['chan_channel_name']; ?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Time Limit ::</td>
                <td colspan="2" bgcolor="#5B7CFF">
                <select name="fline_timeFrom" class="LoginBox" id="fline_timeFrom">
                  <option value="">No Time Limit</option>
                  <?php while($result_time_from = mysql_fetch_assoc($query_time_from)) { ?>
                  <option value="<?php echo $result_time_from['time_value'];?>"><?php echo $result_time_from['time_value'];?></option>
                  <?php } ?>
                </select>
                <select name="fline_timeTo" class="LoginBox" id="fline_timeTo">
                  <option value="">No Time Limit</option>
                  <?php while($result_time_to = mysql_fetch_assoc($query_time_to)) { ?>
                  <option value="<?php echo $result_time_to['time_value'];?>"><?php echo $result_time_to['time_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ip/Hostname ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="fline_hostlimit" type="text" class="LoginBox" id="textfield14" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Active ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><select name="ftp_active" class="LoginBox" onclick="ChangeFTP();" id="ftp_active">
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Ip/Host ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="ftp_ip" type="text" class="LoginBox" id="ftp_ip" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Port ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="ftp_port" type="text" class="LoginBox" id="textfield3" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP User ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="ftp_user" type="text" class="LoginBox" id="textfield4" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Pass ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="ftp_pass" type="text" class="LoginBox" id="textfield5" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Remote File ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><select name="ftp_remote" class="LoginBox" id="ftp_remote">
                  <option value="etc">/etc</option>
                  <option value="varetc">/var/etc</option>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Local File ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="ftp_local" type="text" class="LoginBox" id="textfield6" value="/var/www/cccam-cms-new/admin/client/" size="50" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Allow On Servers ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><select name="server_id[]" size="2" multiple="multiple" class="LoginBox" id="server_id[]">
                  <?php while($result_server = mysql_fetch_assoc($query_server)) { 
					$id = $result_server['server_id'];
					$host = $result_server['server_host'];
					$port = $result_server['server_port'];
					
					?>
                  <option value="<?php echo $id;?>"><?php echo $host.":".$port; ?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
                <td colspan="2" bgcolor="#5B7CFF">&nbsp;</td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">&nbsp;</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="add" type="hidden" id="add" value="newfline" /></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td colspan="2"><input name="button2" type="submit" class="LoginBox" id="button2" value="Insert" /></td>
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