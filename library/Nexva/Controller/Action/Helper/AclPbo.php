<?php

/*
 * This Helper Class is used to add ACL controls to Pbo module
 * add resources, roles and priviliges 
 * 
 */

class Nexva_Controller_Action_Helper_AclPbo {

    public $acl;

    //Instatntiate Zend ACL
    public function __construct() 
    {
        $this->acl = new Zend_Acl();
    }

    //Set User Rolse
    public function setRoles() 
    {
        $this->acl->addRole(new Zend_Acl_Role('superAdmin'));       
        $this->acl->addRole(new Zend_Acl_Role('admin'));
        $this->acl->addRole(new Zend_Acl_Role('speAdmin'));
        $this->acl->addRole(new Zend_Acl_Role('user'));
    }

    //Set Resources - controller, models, etc...
    public function setResources() 
    {
        $this->acl->add(new Zend_Acl_Resource('app'));
        $this->acl->add(new Zend_Acl_Resource('index'));
        $this->acl->add(new Zend_Acl_Resource('user'));
        $this->acl->add(new Zend_Acl_Resource('menu'));
        $this->acl->add(new Zend_Acl_Resource('page'));
        $this->acl->add(new Zend_Acl_Resource('payment-gateway'));
        $this->acl->add(new Zend_Acl_Resource('statistic'));
        $this->acl->add(new Zend_Acl_Resource('setting'));
        $this->acl->add(new Zend_Acl_Resource('ticket'));
        $this->acl->add(new Zend_Acl_Resource('get-serialised-app-names'));
        $this->acl->add(new Zend_Acl_Resource('campaign'));
        $this->acl->add(new Zend_Acl_Resource('category'));
        $this->acl->add(new Zend_Acl_Resource('qelasy'));
    }

    //Set privileges
    public function setPrivilages() 
    {
        $this->acl->allow('superAdmin', 'user', array('login','logout'));
        $this->acl->allow('superAdmin', 'index', 'index');
        $this->acl->allow('superAdmin', 'app', 'index');   
        $this->acl->allow('superAdmin', 'statistic', array('index'));
        $this->acl->allow('superAdmin', 'statistic', array('sales'));
        
        $this->acl->allow('admin', 'user', array('index','login','logout','resend-verification','get-serialised-user-emails','delete-user','excel-report'));
        $this->acl->allow('admin', 'index', 'index');
        $this->acl->allow('admin', 'app', array('index', 'do-feature', 'do-delete' ,'filter-apps', 'add-to-store','do-banner' ,'get-app-details','get-apps-by-device', 'embeded', 'do-flag', 'do-approve', 'excel-report'
                                                    ,'google-play-downloads','add-bulk','get-platform-for-app','selected-platforms-for-featured','selected-platforms-for-banner','get-serialised-app-names',
                                                    'get-serialised-app-names-for-filter','user-apps','translate','addtranslation','removetranslation','appstitude', 'islamic', 'nexva'));
        $this->acl->allow('admin', 'menu', array('index', 'add-menu','do-delete', 'do-publish', 'edit-menu', 'get-pages-by-language-id'));
        $this->acl->allow('admin', 'page', array('index', 'add-page', 'do-delete', 'do-publish', 'edit-page'));
        $this->acl->allow('admin', 'payment-gateway', array('index','set-default'));                
        $this->acl->allow('admin', 'setting', array('index'));
        $this->acl->allow('admin', 'statistic', array('user-wise','user-wise-details','index','device-wise','payout','sales','developers','app-wise','import-statistics','app-wise-statistics','user-wise-statistics','device-wise-details','get-device-names','downloaded-users','data-usage','purchases', 'in-app','inapp-payments','ecarrot-user','ecarrot-paid-user'));
        $this->acl->allow('admin', 'ticket', array('index', 'dashboard', 'details', 'change-status','download-attachment'));
        $this->acl->allow('admin', 'campaign', array('index','sms-campaign','email-campaign','add-sms-campaign','add-email-campaign','list-sms-campaign','list-email-campaign','edit-sms-campaign','edit-email-campaign','send-sms','send-email','suggest','view-stats'));
        $this->acl->allow('admin', 'category', array('manage-category','add-chap-categories','qelasy-grades','edit-qelasy-grade','add-qelasy-grade','delete-qelasy-grade','assign-grade-for-apps','get-qelasy-grades','load-sub-category-div'));
        $this->acl->allow('admin', 'qelasy', array('add-chap-categories','qelasy-grades','edit-qelasy-grade','add-qelasy-grade','delete-qelasy-grade','assign-grade-for-apps','get-qelasy-grades','edit-qelasy-user'));

        $this->acl->allow('speAdmin', 'user', array('index','login','logout'));
        $this->acl->allow('speAdmin', 'index', 'index');
        $this->acl->allow('speAdmin', 'app', array('index', 'filter-apps', 'add-to-store','get-app-details','get-apps-by-device', 'excel-report', 'do-delete', 'embeded','add-bulk','appstitude', 'islamic'));
        $this->acl->allow('speAdmin', 'category', array('manage-category','add-chap-categories','qelasy-grades','edit-qelasy-grade','add-qelasy-grade','delete-qelasy-grade','assign-grade-for-apps','get-qelasy-grades','load-sub-category-div'));

        $this->acl->allow('user', 'user', array('index','login','logout','resend-verification','get-serialised-user-emails','excel-report'));

    }

    //Set ACL to registry - store ACL object in the registry
    public function setAcl() 
    {
        Zend_Registry::set('acl', $this->acl);
    }

}

