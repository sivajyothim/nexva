<?php

/**
 * Log to a file
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     
 * @version     $Id$
 */
class Nexva_Notifier_Services_LogFile implements Nexva_Notifier_Interface_Observer {

  public function notify(Nexva_Notifier_Interface_Subject $subject) {
    $masg = $subject->getMsg();
    if (!isset($logger)) {
      $logger = new Zend_Log();
      // @TODO : get site name from the config and append date to it
      $path_to_log_file = APPLICATION_PATH . '/logs/web.log';
      $writer = new Zend_Log_Writer_Stream($path_to_log_file);
      $logger->addWriter($writer);
    }
    $logger->log($masg, $subject->getSeverity());
    $logger = null;
  }

}

?>
