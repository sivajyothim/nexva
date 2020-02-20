<?php

/**
 * This Plugin is used to check if a resource (action of a controller) is allowed for the user
 * in the PBO module 
 */
class Nexva_Plugin_AclPbo extends Zend_Controller_Plugin_Abstract 
{

    //check if particular resource is allowed for the user
    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
         //get action name
         $privilageName = $request->getActionName();        
         //get controller name 
         $resourceName = $request->getControllerName();
         $module = $request->getModuleName();
         
        if($module == 'pbo')
        {
            $acl = Zend_Registry::get('acl');

            //get the user type
            $usersNs = new Zend_Session_NameSpace('members');
            $roleName = $usersNs->userType;
            
            //check if resource is allowed, if not redirect
            if(!$acl->isAllowed($roleName, $resourceName, $privilageName)) 
            {          
                $request->setModuleName('pbo')
                        ->setControllerName('user')
                        ->setActionName('login');
            }
        }        
           
    }

}
