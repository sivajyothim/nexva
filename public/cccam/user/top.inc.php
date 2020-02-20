<table width="900" border="0" align="center" cellpadding="0" cellspacing="0" class="Contorno">
  <tr>
    <td width="300"><span class="TestoDesc"><?php echo "Logged in user : ". $_SESSION['username']; ?></span></td>
    <td width="300">&nbsp;</td>
    <td width="300"><div align="right"><a href="<?php echo $_SERVER['PHP_SELF']."?action=logout";?>" class="TestoDesc">Logout</a></div></td>
  </tr>
</table>
