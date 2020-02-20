<table width="900" border="0" align="center" cellpadding="0" cellspacing="0" class="Contorno">
  <tr>
    <td width="300"><span class="TestoDesc"><?php echo "Logged in user : ". $_SESSION['username']; ?></span></td>
    <td width="300"><form name="form1" method="get" action="list_user_search.php">
      <label for="text"></label>
      <textrea name="text" id="text" cols="45" rows="5"></textarea>
      <label for="user_search"></label>
      <input class="LoginBox" type="text" name="user_search" id="user_search">
      <input class="LoginBox" type="submit" name="button" id="button" value="Search User">
    </form></td>
    <td width="300"><div align="right"><a href="<?php echo $_SERVER['PHP_SELF']."?action=logout";?>" class="TestoDesc">Logout</a></div></td>
  </tr>
</table>
