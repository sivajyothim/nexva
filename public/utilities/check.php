<?php
include_once("dbconfig.php");

set_time_limit(0);

$conn   =   mysql_pconnect(HOST, USERNAME, PASSWORD);
mysql_select_db(NEXVA_DB);

$query  =   "select * from devices WHERE wurfl_device_id = '' OR wurfl_actual_root_device = ''";
$rs     =   mysql_query($query,$conn);

while($row = mysql_fetch_assoc($rs)){

    $devices[$row['id']]=$row['useragent'];
}

//echo "<pre>"; print_r($devices); die();


// Include the Tera-WURFL file
require_once('../vendors/Tera-WURFL/TeraWurfl.php');

// instantiate the Tera-WURFL object


// Get the capabilities of the current client.


$wurflObj = new TeraWurfl();
foreach($devices as $key=>$value){
    $wurflObj->GetDeviceCapabilitiesFromAgent($value);
    

// see what this device's preferred markup language is
//echo "Markup: ".$wurflObj->getDeviceCapability("preferred_markup");
echo "<br />===============================<br />";

$capabilit = $wurflObj->capabilities;
echo $capabilit['id']."<br />";
echo $capabilit['tera_wurfl']['actual_root_device']."<br />";


$queryUpdate= sprintf("update devices set wurfl_device_id ='%s' , wurfl_actual_root_device ='%s' where id = %s",$capabilit['id'],$capabilit['tera_wurfl']['actual_root_device'],$key);
echo $queryUpdate."<br />";
mysql_query($queryUpdate,$conn);
echo "<br />===============================<br />";
// see the display resolution
//$id =   $wurflObj->capabilities['id'];
//$actual_device_root =   $wurflObj->capabilities['actual_root_device'];
//$width = $wurflObj->getDeviceCapability("resolution_width");
//$height = $wurflObj->getDeviceCapability("resolution_height");

echo "<br /><br /><br />";
flush();
//unset($wurflObj);
//sleep(1);
//mysql_close();
}
// see if this client is on a wireless device (or if they can't be identified)

?>
