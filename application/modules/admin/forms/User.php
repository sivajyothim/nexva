<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */


class Admin_Form_User extends Zend_Form {

    public function init() {

//        parent::init();
//        $this->addPrefixPath('Admin_Form', 'Admin/Form')->addElementPrefixPath('Admin', 'Valiate');

        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setName('user_create');

        // Add userID as a hidden
                // Add a user name element
        $this->addElement('hidden', 'id', array(
        ));

        // validator on user name to not contain any special charectors
        $validator = new Zend_Validate_Regex('([A-Za-z0-9]+)');
        $validator->setMessage(
                'Your username can only contain letters, numbers and underscores (_).');

        // Add a user name element
        $this->addElement('text', 'username', array(
                'label'      => 'Username:',
                'required'   => true,
                'filters'    => array('StringTrim'),
                'class' => 'txt-input medium validate(required, rangelength(5,50))',
                'validators' => array(
                        array('StringLength', false, array(5, 50))
                ),
        ));

        // Password Validation
        $passwordConfirmation = new Admin_Form_Validate_Password();

        // Add a password element
        $this->addElement('password', 'password', array(
                'label'      => 'Password:',
                
                'filters'    => array('StringTrim'),
                'class' => "validate('',rangelength(5,50)) txt-input medium",
                'validators' => array(
                        $passwordConfirmation,
                        array('Alnum'),
                        array('StringLength', false, array(5, 50)),
                ),
        ));

        // Add a re-password element
        $this->addElement('password', 'password_confirm', array(
                'label'      => 'Confirm password:',
                'required'   => false,
                'filters'    => array('StringTrim'),
                'class' => "validate('', rangelength(5,50), match(#password)) txt-input medium",
                'validators' => array(
                        $passwordConfirmation,
                        array('Alnum'),
                        array('StringLength', false, array(6, 100)),
                ),
        ));

        // Add an email element
        $this->addElement('text', 'email', array(
                'label'      => 'Email address:',
                'required'   => true,
                'filters'    => array('StringTrim'),
                'validators' => array(
                        'EmailAddress',
                ),
                'class' => 'validate(email) txt-input medium'
        ));

//        // Add a first name element
//        $this->addElement('text', 'fname', array(
//                'label'      => 'Full Name:',
//                'required'   => true,
//                'filters'    => array('StringTrim'),
//                'class' => 'txt-input medium'
//        ));


        
        $this->addElement('select', 'type', array(
                'label'      => 'User type:',
                'required'   => true,
                'filters'    => array('StringTrim'),
                'class' => 'validate(required) select-input small',
                 'multioptions' => array('CP'=>'Content Provider', 'ADMIN'=>'Administrator', 'USER'=>'User', 'CHAP' => 'CHAP', 'RESELLER' => 'Reseller')
        ));
        
        $payouts = new Admin_Model_Payout();
        $user  =  new Model_User();  
        
        $front = Zend_Controller_Front::getInstance();

        $userDetails = $user->getUserDetailsById( $front->getRequest()->getParam('id'));

        
        if($userDetails->type =='CP')
           {
        
        $this->addElement('select', 'payout', array(
                'label'      => 'Payout Scheme:',
                'required'   => true,
                'filters'    => array('StringTrim'),
                'class' => 'validate(required) select-input small',
                'multioptions' => $payouts->getPayoutRoyalties()
        ));
        
        }
        
        // Royalti payouts 

        // Add an status element
        $this->addElement('checkbox', 'status', array(
                'label' => 'Active:',
                'value' => true
        ));

        // Add a notify element
//        $this->addElement('checkbox', 'notify', array(
//                'label'      => 'Notify to user:',
//        ));

        // Add the submit button
        $this->addElement('submit', 'submit', array(
                'ignore'   => true,
                'label'    => 'Create User',
                'class' => 'txt-input small'
        ));

        // And finally add some CSRF protection
//        $this->addElement('hash', 'csrf', array(
//                'ignore' => true,
//        ));
    }
}
