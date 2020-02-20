<?php

error_reporting(E_ALL);
set_time_limit(0);
include_once("../application/BootstrapCli.php");

// check lock file exists
if (isLocked ()) {
  echo 'lock file is exists';
  // @TODO : check if cron is running for hour or more
//  return TRUE;
} else {
  processQueue();
}

// process the queue
function processQueue() {
  // Register shutdown callback
  register_shutdown_function('releaseLock');
  // create lock file
  grantLock();
// Get job queue
  $config = Zend_Registry::get('config');
  $options = array(
    'name' => 's3_file_sync',
    'driverOptions' => array(
      'host' => $config->resources->multidb->default->host,
      'username' => $config->resources->multidb->default->username,
      'password' => $config->resources->multidb->default->password,
      'dbname' => $config->resources->multidb->default->dbname,
      'type' => 'mysqli'
    )
  );
  
  
  $tries = $config->aws->s3->tries;
  $queue = new Zend_Queue('Db', $options);

  
  $jobs = $queue->count();
  
 
// Get up to 5 messages from a queue
  $messages = $queue->receive($jobs);
  $attempted = 0;
// create the profuctBuildFile model
  $prodBuildFildModel = new Model_ProductBuildFile();
  foreach ($messages as $i => $message) {
//    echo $message->body, "\n";
    $file = unserialize($message->body);
    $file['attempted'] = 0;
    $attempted = isset($file['attempted']) ? $file['attempted'] : 0;
//    echo "\n";
    if ($attempted >= $tries) {
      // @TODO : send a mail
      $config = Zend_Registry::get("config");
      $mailer = new Nexva_Util_Mailer_Mailer();
      $mailer->setSubject('neXva - File sync faile with S3');
      $mailer->addTo(explode(',', $config->nexva->application->dev->contact));
      $mailer->setLayout("generic_mail_template")
          ->setMailVar("data", $file)
          ->sendHTMLMail('s3_sync_failed.phtml');
    }
    // if upload failed
    Zend_Debug::dump($jobs);
    Zend_Debug::dump($i++);
 //   Zend_Debug::dump($file);
  //  Zend_Debug::dump($prodBuildFildModel->pushToS3($file));
    
    
    if(file_exists($file['destination'])){
    
    if (!$prodBuildFildModel->pushToS3($file)) {
      $file['attempted']++;
      if ($attempted < $tries)
        $queue->send(serialize($file));
    }
    
    }
    // We have processed the message; now we remove it from the queue.
    $queue->deleteMessage($message);
    echo $i++;
  }

// release the lock
  releaseLock();
}

// create lock
function grantLock() {
  $ourFileHandle = fopen('lock/.lockfile', 'w') or die("can't open file");
  fclose($ourFileHandle);
}

// check lock exists

function isLocked() {
  if (file_exists('lock/.lockfile'))
    return TRUE;
  return FALSE;
}

// release lock
function releaseLock() {
  if (file_exists('lock/.lockfile'))
    unlink('lock/.lockfile');
}

?>
