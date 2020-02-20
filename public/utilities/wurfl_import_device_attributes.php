<?php

/**
 * This script will get all device table entried and then update the device attributes table with attributes
 * 
 * README
 * ------
 * - truncate table beofre start the script (no harm)
 * - You must only run this only once
 *
 */
error_reporting(E_ALL);
set_time_limit(0);

include_once("../../application/BootstrapCli.php");

include_once( '../vendors/Tera-WURFL/TeraWurfl.php' );
$wurfl = new TeraWurfl();

$log = "device_attrib_importer_log.txt";
$log_append = fopen($log, 'a') or die("can't open file");
// get the devices table entries
$model = new Model_Device();
$modelAttrib = new Model_DeviceAttributes();
$devies = $model->fetchAll();

foreach ($devies as $device) {
  import_device_attributes_to_nexva($device);
}

//echo "<br><b>Inserted: $insert_count devices.</b>";

function import_device_attributes_to_nexva($device) {
  global $wurfl, $log_append, $modelAttrib;
  $devices = array();
  $deviceName = $device->brand . ' ' . $device->model . "\n";
  if (!empty($device->useragent)) {
    $match_found = $wurfl->GetDeviceCapabilitiesFromAgent($device->useragent);
    if ($match_found) {
      $deviceId = $device->id;
      if ($deviceId != "" && $wurfl->capabilities['product_info']['brand_name'] != "" && $wurfl->capabilities['product_info']['model_name'] != "") {
        // array keys are attribute values
        $devices[1] = empty($wurfl->capabilities['product_info']['device_os']) ? 'Java' : $wurfl->capabilities['product_info']['device_os'];
        $devices[2] = ($wurfl->capabilities['product_info']['pointing_method']);
        $devices[3] = ($wurfl->capabilities['product_info']['device_os_version']);
        $devices[4] = ($wurfl->capabilities['sound_format']['mp3']);
        $devices[5] = ($wurfl->capabilities['j2me']['j2me_midp_1_0']);
        $devices[6] = ($wurfl->capabilities['j2me']['j2me_midp_2_0']);
        $devices[7] = ($wurfl->capabilities['display']['resolution_width']);
        $devices[8] = ($wurfl->capabilities['display']['resolution_height']);
        // insert into device_attributes table
        foreach ($devices as $attribId => $value) {

          $data = array(
            'device_attribute_definition_id' => $attribId,
            'device_id' => $deviceId,
            'value' => $value
          );
          // cehck the values are exists
          $row = $modelAttrib->fetchRow(
                      $modelAttrib->select()
                      ->where('device_attribute_definition_id = ?', $attribId)
                      ->where('device_id = ?', $deviceId)
          );
          if (!empty($row->id)) {
            // if the values are different
            if ($value != $row->value) {
              // do update
              $update = $modelAttrib->update($data, 'id = ' . $row->id);
              fwrite($log_append, "updating device ($deviceId and attribute $row->id) $deviceName");
              echo "Updating $deviceName";
              if (empty($update))
                fwrite($log_append, "Error : updating device ($deviceId and attribute $row->id) $deviceName");
//              return;
            }
          }
          else {
            // insert new ids
            $insert = $modelAttrib->insert($data);
            fwrite($log_append, "inserting device ($deviceId) $deviceName");
            echo "Inserting $deviceName";
            if (empty($insert))
              fwrite($log_append, "Error : inserting device ($deviceId) $deviceName");
//            return;
          }
        }
      }
    }
  }
//  fwrite($log_append, $platform_os);
}

$file = $log;
$file_size = filesize($file);
$handle = fopen($file, "r");
$content = fread($handle, $file_size);
$content = chunk_split(base64_encode($content));
$uid = md5(uniqid(time()));
$name = basename($file);
$header = '';
$header = "From: neXva Mailer Agent <do-not-reply@nexva.com>\r\n";
$header .= "Reply-To: heshan@nexva.com\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"\r\n\r\n";
$header .= "This is a multi-part message in MIME format.\r\n";
$header .= "--" . $uid . "\r\n";
$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$header .= "Device Attributes Importer\n\r\n";
$header .= "--" . $uid . "\r\n";
$header .= "Content-Type: application/octet-stream; name=\"" . $log . "\"\r\n"; // use different content types here
$header .= "Content-Transfer-Encoding: base64\r\n";
$header .= "Content-Disposition: attachment; filename=\"" . $log . "\"\r\n\r\n";
$header .= $content . "\r\n\r\n";
$header .= "--" . $uid . "--";

@mail('heshanmw@gmail.com', 'Device Attributes Importer', "", $header);
//@mail('jahufar@nexva.com', 'Device Attributes Importer', "", $header);
fclose($log_append);
?>