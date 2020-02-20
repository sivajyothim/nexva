<?php
if (php_sapi_name() != 'cli')
{
    exit('Command line use only');
}

require_once 'init.php';

$mailTemlateContentsStr = $str = file_get_contents('../application/views/layouts/mail-templates/generic_mail_template.phtml');

    $recurringPayment = new Model_RecurringPayment();
    $recurringPayment->makePayment($mailTemlateContentsStr);