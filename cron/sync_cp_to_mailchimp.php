<?php

    include_once("../application/BootstrapCli.php");

    $cp = new Cpbo_Model_User();
    $mailchimp = new Nexva_MailChimp_MailChimp();

    $rows = $cp->getAll(true);

    $counter = 0;
    
    foreach( $rows as $row ) {
        if( $row->email != "" ) {
            //@todo: find out if this email is already subscribed. if so skip.

            $meta = new Cpbo_Model_UserMeta();
            $meta->setEntityId($row->id);
            
            $success = $mailchimp->subscribeToList($row->email,
                    Zend_Registry::get('config')->mailchimp->lists->cp->id, $meta->FIRST_NAME, $meta->LAST_NAME);
                        
            if( $success ) {
                echo "Added '{$meta->FIRST_NAME} {$meta->LAST_NAME}' ({$row->email}) to list \n";
                $counter++;
            }
            else
                echo "Unable to add {$row->email} to list \n";            
        }
        
    }
   
    echo "\nDone. Added: $counter people to list. \n";


?>