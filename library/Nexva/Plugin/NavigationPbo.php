<?php

/**
 * This Plugin is used to generate role base Menu on the PBO module 
 */
class Nexva_Plugin_NavigationPbo extends Zend_Controller_Plugin_Abstract 
{

    //check if particular resource is allowed for the user
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        //Get module name       
        $module = $request->getModuleName();
        
        //Generate menu only for PBO Module
        if($module == 'pbo')
        {
            //get view object
            $view = Zend_Layout::getMvcInstance()->getView();          

            //Get the XML navigation file for this particular module
            $config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/pboNavigation.xml','nav');
            
            //Set it for Zend navigation control
            $navigation = new Zend_Navigation($config);

            //Access the ACL object in the registry
            $acl = Zend_Registry::get('acl');

            //get the user type
            $usersNs = new Zend_Session_NameSpace('members');
            $roleName = $usersNs->userType;

            //send the navigation to the Layout, through ACL 
            $view->navigation($navigation)->setAcl($acl)
                                          ->setRole($roleName);
        }        
           
    }

}