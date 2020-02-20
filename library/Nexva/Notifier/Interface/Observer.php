<?php

/**
 * Observer Interface
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

interface Nexva_Notifier_Interface_Observer {
  public function notify(Nexva_Notifier_Interface_Subject $subject);
}

?>
