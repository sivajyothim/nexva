<?php
/**
 * Simple MailChimp wrapper class
 *
 * @author jahufar
 */

include_once( "MCAPI.class.php" );

class Nexva_MailChimp_MailChimp {

    /**
     * Adds a subscriber to a MailChimp mailinglist.
     *
     * @param string $email
     * @param string $listId
     * @param array mergeVars
     * @param boolean $doubleOptIn
     * @return boolean
     */
    public function subscribeToList($email, $listId, $mergeVars = array(), $doubleOptIn = false) {

        $config = Zend_Registry::get('config');

        $api = new MCAPI($config->mailchimp->api->key);

        //$mergeVars = array('FNAME'=>$firstName, 'LNAME'=>$lastName);
        $retVal = $api->listSubscribe( $listId, $email, $mergeVars, "html", $doubleOptIn, true);

        if ($api->errorCode)
            return false;
        else 
            return true;        
    }

}
?>
