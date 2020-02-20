<?php

/**
 * Send email notification
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package
 * @version     $Id$
 */
class Nexva_Notifier_Services_Email implements Nexva_Notifier_Interface_Observer {

  protected $_recipient = array();

  public function notify(Nexva_Notifier_Interface_Subject $subject) {
    $subject->getMsg();
  }

}

?>
