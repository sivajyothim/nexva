<?php

error_reporting(E_ALL);
set_time_limit(0);

include_once("../application/BootstrapCli.php");
include_once( '../public/vendors/Tera-WURFL/TeraWurfl.php' );

$importer = new deviceImporter();
$importer->importDevices();

//$importer->processNotificationQueue();

class deviceImporter {

  protected $_dbWurfl;
  protected $_wurfl;
  protected $_number = 0;

//put your code here
  public function __construct() {
    $this->_dbWurfl = Zend_Registry::get('db_wurfl');
    $this->_wurfl = new TeraWurfl();
  }

  public function importDevices() {
// get the list of tables
    $dbWurfl = $this->_dbWurfl;
    $tables = $dbWurfl->fetchAll("show tables");
    foreach ($tables as $table) {
      if (strrpos($table->Tables_in_tera_wurfl_v2, "_")) { // lazy table validator, this wil only take tables wich has _
//        echo $table->Tables_in_tera_wurfl . "\n";
        $this->loadDevicesFromTable($table->Tables_in_tera_wurfl_v2);
      }
    }
    echo 'total count' . $this->_number;
  }

  private function loadDevicesFromTable($tableName) {
    if (empty($tableName))
      return;

    $deviceModel = new Model_Device();
    $dbWurfl = $this->_dbWurfl;
    $devices = $dbWurfl->fetchAll("SELECT * FROM $tableName");
    foreach ($devices as $device) {
//      echo $device->deviceID . "\n";
      $wurfl = $this->_wurfl;
      $wurflData = $wurfl->GetDeviceCapabilitiesFromAgent($device->user_agent);
// get only wireless devices
      if ($wurfl->capabilities['product_info']['is_wireless_device'] &&
          !empty($wurfl->capabilities['product_info']['brand_name']) &&
          !empty($wurfl->capabilities['product_info']['model_name'])) {
        $model = '';
        $model = !empty($wurfl->capabilities['product_info']['marketing_name']) ? $wurfl->capabilities['product_info']['model_name'] . " (" . $wurfl->capabilities['product_info']['marketing_name'] . ")" : $wurfl->capabilities['product_info']['model_name'];
        $values = array(
          'useragent' => $device->user_agent,
          'model' => $model,
          'brand' => $wurfl->capabilities['product_info']['brand_name'],
          'wurfl_device_id' => $device->deviceID,
          'wurfl_actual_root_device' => $wurfl->capabilities['tera_wurfl']['actual_root_device']
        );
        $deviceFound = $deviceModel->deviceExists('wurfl_device_id', $values);
        if (empty($deviceFound)) {
          $deviceFound = $deviceModel->deviceExists('model', $values);
        }
        if (empty($deviceFound)) {
          $deviceFound = $deviceModel->deviceExists('useragent', $values);
        }
//        echo $deviceFound . "\n";
        $attributes = array();
        $attributes[1] = empty($wurfl->capabilities['product_info']['device_os']) ? 'Java' : $wurfl->capabilities['product_info']['device_os'];
        $attributes[2] = ($wurfl->capabilities['product_info']['pointing_method']);
        $attributes[3] = ($wurfl->capabilities['product_info']['device_os_version']);
        $attributes[4] = ($wurfl->capabilities['sound_format']['mp3']);
        $attributes[5] = ($wurfl->capabilities['j2me']['j2me_midp_1_0']);
        $attributes[6] = ($wurfl->capabilities['j2me']['j2me_midp_2_0']);
        $attributes[7] = ($wurfl->capabilities['display']['resolution_width']);
        $attributes[8] = ($wurfl->capabilities['display']['resolution_height']);

//        echo $deviceFound . "\n";
//        print_r($deviceFound[id]);
        if ($deviceFound) {
          $device = $deviceFound->toArray();
          unset($device['id']);
          $diff = array_diff($values, $device);

          if (count($diff) > 0) {
            $deviceId = $deviceFound['id'];
//            print_r($device);
//            print_r($values);
//            print_r($diff);
            $deviceModel->update($values, 'id = ' . $deviceId);
//            echo $deviceId;
//            exit;
          }
        } else {
// insert new device to device table
// insert attributes
//          print_r($values);
          $deviceId = $deviceModel->insert($values);
          $deviceModel->updateDeviceAttributes($attributes, $deviceId);
          $deviceModel->deviceUpdateNotify($deviceId);
// insert device update notificaiton to the CP's

          echo $this->_number++ . "\n";
        }
      }
    }
  }

  /**
   * Process the notification queue
   */
  public function processNotificationQueue() {
    $modelProduct = new Model_Product();
    $modelDeviceUpdate = new Model_DeviceUpdate();
    $userModel = new Model_User();
    $deviceModel = new Model_Device();
    $userMeta = new Model_UserMeta();
    $devices = new stdClass();
    $users = new stdClass();
    $list = $this->loadList();
// get the list of CP Id's
    foreach ($list as $device) {
//get the users list
      $products = $modelProduct->getAllAppsByDeviceId($device->device_id);
      if (!is_array($products))
        continue;
      foreach ($products as $product) {
        $uid = $product['uid'];
        $product['device_id'] = $device->device_id;
        $productId = $product['id'];
        $users->$uid->products[$productId] = $product;
        $devices->$product['id']->device->name[] = $deviceModel->getDeviceNameById($device->device_id);
      }
      // add device detials to device array
//      $deviceId = $device->device_id;
//      $devices->$deviceId->device->name[] = $deviceModel->getDeviceNameById($device->device_id);
//      $modelDeviceUpdate->delete('id = ' . $device->id);
    }
//
//    print_r($users);
//    exit;
// Get the list of products for users and sending out email to CP's
// only send to relevent parties
    foreach ($users as $uid => $productsList) {
      $user = $userModel->getUserDetailsById($uid);
//sending out an email
      $userMeta->setEntityId($uid);
      $mailer = new Nexva_Util_Mailer_Mailer();
      $mailer->setSubject("neXva - We found new devices for your Apps");
      if (APPLICATION_ENV == 'production') {
        $mailer->addTo($user->email, $user->username);
      } else {
        $mailer->addTo('heshan@nexva.com', $user->username)
            ->addCC('jahufar@nexva.com', 'Jahufar');
      }
      $mailer->setLayout("generic_mail_template")//change if needed. remember, stylesheets cannot be linked in HTML email, so it has to be embedded. see the layout for more.
          ->setMailVar("email", $user->email)
          ->setMailVar("name", $userMeta->FIRST_NAME . ' ' . $userMeta->LAST_NAME)
          ->setMailVar("devices", $devices)
          ->setMailVar("products", $productsList->products)
          ->sendHTMLMail('device_update_notification.phtml'); //change this. mail templates are in /views/scripts/mail-templates
    }
  }

  private function loadList() {
    $model = new Model_DeviceUpdate();
    return $model->fetchAll();
  }

}

?>
