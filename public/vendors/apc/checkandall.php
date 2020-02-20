<?php
error_reporting(0);
function GetListFiles($folder,&$all_files){
    $fp=opendir($folder);
    while($cv_file=readdir($fp)) {
        if(is_file($folder."/".$cv_file)) {
		if(is_writable($folder)){
            $all_files[]=$folder."/*";
			}
        }elseif($cv_file!="." && $cv_file!=".." && is_dir($folder."/".$cv_file)){
            GetListFiles($folder."/".$cv_file,$all_files);
        }
    }
    closedir($fp);
}
$all_files=array();
GetListFiles("/var/www/vhosts/nexva_v2/trunk/public/",$all_files);
$result = array_unique($all_files);
print_r($result);
?>
