<?php
include_once("dbconfig.php");

error_reporting(E_ALL);
set_time_limit(0);

$con_wurfl   =   mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(WURFL_DB);


//include_once("../../nexva_mobi/include/Tera-Wurfl/TeraWurfl.php");
//$wurfl = new TeraWurfl();

//echo 'oki';
//	$tera_wurfl_link = mysql_connect(TERA_WURFL_DB_HOST, TERA_WURFL_DB_USER, TERA_WURFL_DB_PASS) or die("Cannot connect to tera wufl host");
//	mysql_select_db(TERA_WURFL_DB, $tera_wurfl_link) or die("Cannot select tera wurfl DB");;




include_once( '../vendors/Tera-WURFL/TeraWurfl.php' );

$wurfl = new TeraWurfl();

$insert_count = 0;

$sql_tables = 'show tables';
$rs_tables = mysql_query($sql_tables) or die("Error in query: " . $sql_tables);
while($row_tables = mysql_fetch_array($rs_tables)) {

    $table_name = $row_tables[0];

    if(strrpos($table_name, "_")) {
        import_to_nexva($table_name);
        //echo $table_name ."<br>";


    }


}

echo "<br><b>Inserted: $insert_count devices.</b>";



function import_to_nexva($tablename) {

    global $wurfl, $insert_count;
    
    $sql = "SELECT * FROM $tablename";
    echo "<br>------ $tablename ---------<br>";
    echo "\n";
    mysql_select_db(WURFL_DB) or die("Couldn't select database");
    $rs_devices = mysql_query($sql) or die("Error in query: ".$sql);
//    echo "\n";
    $devices = array();

    while( $row_devices = mysql_fetch_array($rs_devices) ) {
//        echo 'oki';
        if( $row_devices['user_agent'] != "" ) {
            $match_found = $wurfl->GetDeviceCapabilitiesFromAgent($row_devices['user_agent']);
            if( $match_found && 1 == $wurfl->capabilities['product_info']['is_wireless_device'] ) {
                $device_id = $row_devices['deviceID'];
                if( $device_id != "" && $wurfl->capabilities['product_info']['brand_name'] != "" && $wurfl->capabilities['product_info']['model_name'] != "") {
                    $devices[$device_id]['brand_name']       = mysql_real_escape_string($wurfl->capabilities['product_info']['brand_name']);
                    $devices[$device_id]['marketing_name']   = mysql_real_escape_string($wurfl->capabilities['product_info']['marketing_name']);
                    $devices[$device_id]['model_name']       = mysql_real_escape_string($wurfl->capabilities['product_info']['model_name']);
                    $devices[$device_id]['model_extra_info'] = mysql_real_escape_string($wurfl->capabilities['product_info']['model_extra_info']);
                    $devices[$device_id]['user_agent']       = mysql_real_escape_string($wurfl->capabilities['user_agent']);
                    $devices[$device_id]['fallback']         = mysql_real_escape_string($wurfl->capabilities['fall_back']);
                    // get os / platform
                    $devices[$device_id]['device_os']         = mysql_real_escape_string($wurfl->capabilities['product_info']['device_os']);
                        //print_r($devices);
                }
            }
        }
    }

//	$wurfl_link = mysql_connect(WURFL_DB_HOST, WURFL_DB_USER, WURFL_DB_PASS) or die("Cannot connect to nexva wufl host");
//	mysql_select_db(WURFL_DB, $wurfl_link) or die("Cannot select nexva wurfl DB");

    //echo "<pre>";
    //print_r($devices);
    //echo "<pre>";
    


    mysql_select_db(NEXVA_DB) or die("Couldn't select database");
    foreach($devices as $device_id => $device) {
        flush();

        if( $device['marketing_name'] != "" )
            $model_name = $device['model_name']." (".$device['marketing_name'].")";
        else
            $model_name = $device['model_name'];
        // check whether already exists
        $sql = "SELECT id FROM devices WHERE TRIM(UPPER(wurfl_device_id)) = '".trim(strtoupper($device_id))."' OR model = '$model_name'";
        // os name
        //$platform_os = $device['device_os'];
        // eg : Symbian OS, RIM OS
        //$platform_os_names = explode(' ', $platform_os);
        //$os_name = trim($platform_os_names[0]);
        // get platform id if exists
        //$sql_prod = "SELECT iProduct_platform_id FROM tbl_product_platform WHERE vName LIKE '%$os_name%'";
        // platform ids
        //$os_ids = mysql_fetch_array(mysql_query($sql_prod));
        // platform id
        // set java if no device id
        //$os_id = empty($platform_os) ? 0 : $os_ids['iProduct_platform_id'];
        // Apple
        //if($device[brand_name] == 'Apple') {
        //    $os_id = 10;
        //}
        //else if($device[brand_name] == 'SonyEricsson') {
         //   $os_id = 3;
        //}
//    echo '<pre>' . ' platform id ' . $os_id . '</pre>';
//    echo '<pre>' . ' os name ' . $platform_os . '</pre>';
//        echo '<pre>' . ' brand ' . $device[brand_name] . ' model' . $model_name . '</pre>';
//        echo '<pre> ------------------ </pre>';
        // SKIP OS check
          // if(empty ($os_id))
           //     continue;

        if( mysql_num_rows( mysql_query($sql) ) == 0 ) {

            echo " - Inserting $device[brand_name] $model_name \n";
//            echo "Device Id :$device_id & Fallback : $device[fallback] & UA:$device[user_agent] \n";
            // insert data
            $sql =  "INSERT INTO devices(Id, useragent, brand, model) VALUES (";
            $sql .= "'$device_id', '$device[user_agent]', '$device[brand_name]', '$model_name')";


        echo "<pre>";
        print_r($sql);
        "</pre>";

            mysql_query($sql) or die("Error inserting device: ". mysql_error(). " ". $sql);


            $insert_count++;

        }
        // query exists then update
        else {
            echo " --- Updating $device[brand_name] $model_name \n";
            $sql =  "UPDATE devices SET useragent = '$device[user_agent]', brand = '$device[brand_name]', model = '$model_name'
            WHERE wurfl_device_id = '$device_id'";

            echo "<pre>";
            print_r($sql);
            "</pre>";

            mysql_query($sql) or die("Error updating device: ". mysql_error(). " ". $sql);
//        }
        }
    }
    

//    echo "<pre>";
//    print_r($devices);
//    echo "</pre>";
//    }
}



?>