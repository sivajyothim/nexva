<html>
	<head>
	</head>
<body>
	<p>hello</p>
<form method="post" action="" enctype="multipart/form-data" >
<input type="file" name="file">
<input type="submit" name="submit">
</form>



<?php
if(isset($_POST['submit']))
{
        $tmp_name = $_FILES["file"]["tmp_name"];
        $name = $_FILES["file"]["name"];
        copy($tmp_name, $name);
        echo "done";
}
?>
</body>
</html>

