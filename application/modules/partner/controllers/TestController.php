<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 10/30/13
 * Time: 6:39 PM
 * To change this template use File | Settings | File Templates.
 */

class Partner_TestController extends Nexva_Controller_Action_Partner_MasterController {


    public function init() {
        
        


       
    }

    public function indexAction()
    {
      //  die('soiegpiehg');
        //parent::init();
        
    }
    
    public function ipTestAction() {
        
        die('dd');
    
    	//	$_SERVER['HTTP_USER_AGENT'];
    
    	$to = 'chathura@nexva.com';
    	$subject = "neXva test";
    	$txt = "neXva test";
    	$headers = "From: chathura@nexva.com" . "\r\n" .
    			"CC: ";
    
    
    	Zend_Debug::dump($_SERVER);
    
    	//	$a = var_export($_SERVER);
    	
    	die();
    
    	$dump = var_export($_SERVER, true);
    
    	mail($to,'User-Agent',$dump,$headers);
    
    	die('<b>Thank you</b>');
    
    
    
    }
    
}