<?php

/**
 * Check if the user has his mobile no as header enrichment and return the user details
 * @author Rooban
 */

class Nexva_Controller_Action_Helper_IpHeader extends Zend_Controller_Action_Helper_Abstract {

    /*
     * Function checks the user active with header enrichment and create user for the first time viewing the site
     * Returns the user details
     * @param $chapId Chap ID
     */
    //public function checkUser($chapId = 21134) { replaced by below code line 08/05/2014 as we are implementing header enrichment enable/disable option - viraj
    public function checkUser($chapId) {
        


        $msisdn = null;

        $isHeaderEnrichmentActive = false;

      
        //if ($chapId == 21134) { removed this condition 08/05/2014 - viraj
        switch ($chapId) {
            case 21134:
                $msisdn = @$_SERVER['HTTP_MSISDN'];
                break;

            default:
                $themeMeta   = new Model_ThemeMeta();
                $themeMeta->setEntityId($chapId);
                $headers = apache_request_headers();
                $headerIdentifierCode = $themeMeta->WHITELABLE_IP_HDR_IDENTIFIER;
                if($headerIdentifierCode){
                    $msisdn = $headers[$headerIdentifierCode];
                }
                break;
                
             
        }
        
 /*        if($chapId == 283006)
            $msisdn = '381645552117'; */
        
        
        
        if ($chapId == 283006) {
        	 
        /*  	$themeMeta   = new Model_ThemeMeta();
        	$themeMeta->setEntityId(283006);
        	$headers = apache_request_headers();
        	$headerIdentifierCode = $themeMeta->WHITELABLE_IP_HDR_IDENTIFIER;
        	Zend_Debug::dump($chapId, 'aa');
        	Zend_Debug::dump($headerIdentifierCode, 'bb');
        	Zend_Debug::dump($headers[$headerIdentifierCode], 'aa'); 
        	die($msisdn);        
       */
        
        }
      
        //Check the MSISDN is set in header. If not the default MSISDN(0123456789) will be set to current user
        if ($msisdn == null) {

            $deviceSession = new Zend_Session_Namespace('Device');

            if ($deviceSession->mobileNo)
                $msisdn = $deviceSession->mobileNo;
            else {
                $msisdn = '0123456789';
                $deviceSession->mobileNo = $msisdn;
            }
            
            $user = new Api_Model_Users();
            $userInfo = $user->getUserByMobileNo($msisdn);
            return array('user_id' => $userInfo->id, 'mobile_no' => $msisdn, 'password' => '', 'header_enrichment_active' => $isHeaderEnrichmentActive);
            
        } else {

            $isHeaderEnrichmentActive = true;
            
           
            
            ////Only need this for MTN Nigeria. The header enrichment developed lately, but some users were already registered without country code. But the header MSISDN set with country code            $themeMeta = new Model_ThemeMeta();
            $themeMeta->setEntityId($chapId);
            $countryCode = $themeMeta->COUNTRY_CODE_TELECOMMUNICATION;
            if ($chapId == 21134) { //only this need for mtn nigeria
                $ptn = "/^$countryCode/";  
                $str = $msisdn; 
                $rpltxt = "0";  
                $msisdn = preg_replace($ptn, $rpltxt, $str);
            }
            //// end ////
            
            $user = new Api_Model_Users();
            $userInfo = $user->getUserByMobileNo($msisdn);
            
          
            //Check the user is registered with relevant CHAP
            if ($userInfo) {
                //If user is currently logged in the details retriving from auth
                if (@Zend_Auth::getInstance()->getIdentity()->id){
                        $loggedUserInfo =Zend_Auth::getInstance()->getIdentity();
                        return array('user_id' => $loggedUserInfo->id, 'mobile_no' => $loggedUserInfo->mobile_no, 'password' => $userInfo->password, 'header_enrichment_active' => $isHeaderEnrichmentActive);
                } else {
                        return array('user_id' => $userInfo->id, 'mobile_no' => $userInfo->mobile_no, 'password' => $userInfo->password, 'header_enrichment_active' => $isHeaderEnrichmentActive);
                }
            } else {
               
                $activationCode = substr(md5(uniqid(rand(), true)), 5, 8);

                switch ($chapId) {
                    case 21134: //MTN Nigeria
                        $email = $activationCode . 'iphaderuser@mtn.com'; //Don't change this since this is the common for web and app
                        $password = 'mtnpassword';
                        $firstName = 'mtnfisrtname';
                        $lastName = 'mtnlastname';
                        break;

                    case 25022: //Airtel Sri Lanka
                        $email = $activationCode . 'iphaderuser@airtelsl.com';
                        $password = 'airtelpassword'; //Don't change this since this is the common for web and app
                        $firstName = 'airtelfisrtname';
                        $lastName = 'airtellastname';
                        break;
                    
                    case 81449: //Airtel Nigeria
                    case 114306: //Airtel Rwanda
                    case 110721: //Airtel Gabon
                    case 163302: //Airtel Malawi
                    case 274515: //Airtel Niger100791
                    case 100791: //Airtel TZ
                    case 280316: //Airtel DRC
                    case 324405: //Airtel MG
                        $email = $activationCode . 'iphaderuser@airtel.com';
                        $password = 'airtelpassword'; //Don't change this since this is the common for web and app
                        $firstName = 'airtelfisrtname';
                        $lastName = 'airtellastname';
                        break;
                    
                    case 80184: //MTN Uganda
                        $email = $activationCode . 'iphaderuser@mtnuganda.com';
                        $password = 'mtnugandapassword'; //Don't change this since this is the common for web and app
                        $firstName = 'mtnugandafisrtname';
                        $lastName = 'mtnugandalastname';
                        break;
                        
                    case 283006:
                         $email = $activationCode . 'iphaderuser@mts.com';
                         $password = 'mtspassword'; //Don't change this since this is the common for web and app
                         $firstName = 'mtsfisrtname';
                         $lastName = 'mtslastname';
                         break;
                        
                    default: //Default
                        $email = $activationCode . 'iphaderuser@nexva.com';
                        $password = 'nexvapassword'; //Don't change this since this is the common for web and app
                        $firstName = 'nexvafirstname';
                        $lastName = 'nexvalastname';
                        break;
                }

                //If the header contains MSISDN the user will be registered in the forst time with the common password
                $userData = array(
                    'username' => $email,
                    'email' => $email,
                    'password' => $password, //Common password set above for the chaps
                    'type' => "USER",
                    'login_type' => "NEXVA",
                    'chap_id' => $chapId,
                    'mobile_no' => $msisdn,
                    'activation_code' => $activationCode,
                    'status' => 1
                );

                $userId = $user->createUser($userData);

                $userMeta = new Model_UserMeta();
                $userMeta->setEntityId($userId);
                $userMeta->FIRST_NAME = $firstName;
                $userMeta->LAST_NAME = $lastName;
                $userMeta->TELEPHONE = $msisdn;
                $userMeta->UNCLAIMED_ACCOUNT = '1';

                $userInfo = $user->getUserByMobileNo($msisdn);
                
                
                     
              
                return array('user_id' => $userInfo->id, 'mobile_no' => $userInfo->mobile_no, 'password' => $userInfo->password, 'header_enrichment_active' => $isHeaderEnrichmentActive);
            }
        } 
    }

    /*
     * Function login in user with only mobile number
     * @param $chapId Chap ID
     * @param $mobileNo Mobile No
     */
    public function loginUserByHeaderEnrichment($chapId, $mobileNo) {
        //If the chap is AirtelSL get the user logged automatically
        $user = new Partnermobile_Model_Users();
        $userData = $user->getUserByMobileNo($mobileNo);
        
        $userDetails = new Zend_Session_Namespace('userDetails');
        $userDetails->userId = $userData['id'];

        $db = Zend_Registry::get('db');
 
        /*$authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'mobile_no', 'password', "MD5(?) AND status=1 AND type='USER' AND mobile_no != '' AND chap_id = $chapId");
        $authDbAdapter->setIdentity($mobileNo);
        $authDbAdapter->setCredential($password);*/
        
        $authDbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'mobile_no', 'mobile_no', "status=1 AND type='USER' AND mobile_no != '' AND chap_id = $chapId");
        $authDbAdapter->setIdentity($mobileNo);
        $authDbAdapter->setCredential($mobileNo);

        $result = Zend_Auth::getInstance()->authenticate($authDbAdapter);
        
        //if($_SERVER['REMOTE_ADDR'] == '113.59.209.48'){ print_r($result); }
        
        //If validated successfully
        if ($result->isValid()) {
            Zend_Auth::getInstance()->getStorage()->write($authDbAdapter->getResultRowObject(null, 'password'));
        }
    }

}
