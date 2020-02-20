<?php
include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

$connection     =   mysql_connect("localhost", "nexvadb", "aluthgama")or die(mysql_error());




$insert_count = 0;
// get the non demo product files by priduct id
$sql_v1_files = "select iPFId,tDeviceId,tbl_product_files.iPId,tbl_product_files.vJarfile,tbl_product_files.vJadfile,vSISFile,vOTAFile,vCodeFile1,vCodeFile2,vCodeFile3,vCodeFile4,vCodeFile5,vCodeFile6,vCodeFile7,vCodeFile8,vCodeFile9,vCodeFile10,vWallpaper,vScrennServer,vVideo,vRingtone,vApkFile,vAudioBook,vUrl,vNAme from nexvadb.tbl_product_files join nexvadb.tbl_product_details
            on   tbl_product_files.iPId = tbl_product_details.iPId
            where cMode = 'D' order by iPId";

$rs_v1_files = mysql_query($sql_v1_files,$connection) or die("Error in query: " .mysql_error() . $sql_v1_files);

while($row_v1_files = mysql_fetch_array($rs_v1_files)) {

    import_to_v2($row_v1_files);
}

// importing to product_build, build_files, build_devices tables
function import_to_v2($row) {
    // variables
    $pro_name   = $row['vNAme'];
    $product_id = $row['iPId'];
    $device_str = $row['tDeviceId'];
    $build_name = 'Default Build 1';
    // check product id exists
//    $sql_prod = 'SELECT id FROM products WHERE id = ' . $product_id;
//    if(mysql_num_rows( mysql_query($sql_prod) ) == 0 )
//        return;
    // selete the inserted entries
    $q_select_demo  =   "select * from nexva_v2_production.products where name like '".mysql_real_escape_string($pro_name)."' and product_type = 'DEMO'";
    //echo $q_select_demo."<br />";
    $connection2     =   mysql_connect("localhost", "nexvadb", "aluthgama")or die(mysql_error());
    $rs_select_demo=mysql_query($q_select_demo,$connection2)or print(mysql_error());
  
    $rows_demo_products =    mysql_fetch_assoc($rs_select_demo)or print(mysql_error());
    var_dump($rows_demo_products);
    $sql_select = 'SELECT id FROM nexva_v2_production.product_builds WHERE product_id = ' . $rows_demo_products['id'];
    echo $sql_select;
    echo "<br /><br />-------<br />";
    //die();
    //$sql_select = 'SELECT id FROM product_builds WHERE product_id = ' . $product_id;
    if( ($numb_of_rows = mysql_num_rows(mysql_query($sql_select) )) > 0 )
        $build_name = 'Default Build ' . ($numb_of_rows + 1);
    // insert to product_builds
    echo $sql =  "INSERT IGNORE INTO nexva_v2_production.product_builds(product_id, name, device_selection_type, status) VALUES (".$rows_demo_products['id'].", '$build_name', 'CUSTOM', 1)";
    echo "<br />";
    $connection3     =   mysql_connect("localhost", "nexvadb", "aluthgama")or die(mysql_error());
    mysql_query($sql,$connection3);
    if(mysql_errno() != 0) {
        echo 'Warning : my sql error occured ' . mysql_error();
        return;
    }
    $build_id = mysql_insert_id();
    //inserting to build_devices
    $devices = explode(',', $device_str);
    foreach($devices as $key=>$value) {
//        $sql_prod = 'SELECT id FROM devices WHERE id = ' . $value;
        if(empty ($value))
            continue;
        echo $sql_device =  "INSERT IGNORE INTO nexva_v2_production.build_devices(device_id, build_id) VALUES ($value, $build_id)";
        echo '<br/>';
        $connection4     =   mysql_connect("localhost", "nexvadb", "aluthgama")or die(mysql_error());
        mysql_query($sql_device,$connection4);
        if(mysql_errno() != 0) {
            echo 'Warning : my sql error occured ' . mysql_error();
            continue;
        }
    }

    //inserting to build_files
    $files = array();
    $columns = array('vJarfile', 'vJadFile', 'vSISFile', 'vOTAFile', 'vCodeFile1', 'vCodeFile2',
            'vCodeFile3', 'vCodeFile4', 'vCodeFile5', 'vCodeFile6', 'vCodeFile7', 'vCodeFile8', 'vCodeFile9',
            'vCodeFile10', 'vWallpaper', 'vScrennServer', 'vVideo', 'vRingtone', 'vApkFile', 'vAudioBook', 'vUrl');

    foreach ($columns as $column) {
        if(isset ($row[$column]) &&  !empty ($row[$column]))
            $files[] = $row[$column];

    }

    foreach($files as $key=>$filename) {
//        $filename = str_replace("'", '', $filename);
        echo $sql_file =  'INSERT IGNORE INTO nexva_v2_production.build_files(filename, build_id) VALUES ("' . $filename . '", ' . $build_id . ')';
        //echo '$sql_file<br/>';
        $connection5     =   mysql_connect("localhost", "nexvadb", "aluthgama")or die(mysql_error());
        mysql_query($sql_file,$connection5) or die("Error inserting device: ". mysql_error(). " ". $sql_file);
    }

    flush();
}

?>