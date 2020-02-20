<?php

/**
 * Subject interface
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
interface Nexva_Notifier_Interface_Subject {
  public function attach(Nexva_Notifier_Interface_Observer $observer);

  public function detach(Nexva_Notifier_Interface_Observer $observer);

  public function notify();
}

?>
