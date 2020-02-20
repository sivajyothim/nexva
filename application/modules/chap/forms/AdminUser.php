<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     CP
 * @version     $Id$
 */

/**
 * Description of ProductBasicInfo
 *
 * @author Administrator
 */
class Chap_Form_AdminUser extends Zend_Form {

    public $buttonDecorators = array(
      'ViewHelper',
      'Errors',
      array(array('row' => 'HtmlTag'), array('tag' => 'p'))
    );
    
    public $elementDecorators = array(
      'ViewHelper',
      array('Description', array('tag' => 'small', 'class' => 'description')),
      array(array('close' => 'HtmlTag'), array('tag' => 'td', 'width' => '60%', 'closeOnly' => true)),
      array('Label', array('tag' => 'label')),
      array(array('row' => 'HtmlTag'), array('tag' => 'p'))
    );

    public function init() {
        //parent::init();
        $this->setMethod('post');
        $this->setName('admin_user');

        // Add userID as a hidden
        $this->addElement('hidden', 'formId', array());
        $this->getElement('formId')->setValue('admin');

        // validator on user name to not contain any special charectors
        $validator = new Zend_Validate_Regex('([A-Za-z0-9]+)');
        $validator->setMessage('Your content name can only contain letters, numbers and underscores (_).');

        // Add a admin name element
        $this->addElement('text', 'admin_contact_name', array(
          'decorators' => $this->elementDecorators,
          'label' => 'Admin contact name:',
          'Description' => '',
          'required' => true,
          'size' => 10,
          'filters' => array('StringTrim'),
          'class' => 'txt-input medium validate(required)',
          'validators' => array(
            array('StringLength', false, array(1, 250))
          ),
        ));
        
        // Add a admin email element
        $this->addElement('text', 'admin_contact_email', array(
          'decorators' => $this->elementDecorators,
          'label' => 'Admin contact email:',
          'Description' => '',
          'required' => true,
          'size' => 10,
          'filters' => array('StringTrim'),
          'class' => 'txt-input medium validate(email)',
          'validators' => array(
            array('StringLength', false, array(1, 250))
          ),
        ));
        
        // Add a admin phone element
        $this->addElement('text', 'admin_contact_phone', array(
          'decorators' => $this->elementDecorators,
          'label' => 'Admin contact phone:',
          'Description' => '',
          'required' => true,
          'size' => 10,
          'filters' => array('StringTrim'),
          'class' => 'txt-input medium',
          'validators' => array(
            array('StringLength', false, array(1, 250))
          ),
        ));

       

        // Add the submit button
        $this->addElement('submit', 'submit', array(
          'ignore' => true,
          'label' => 'Save',
          'href' => 'tab-screenshots',
          'decorators' => $this->buttonDecorators,
          'class' => 'txt-input small submit button',
          'style' => 'width : 80px'
        ));


        // And finally add some CSRF protection
//        $this->addElement('hash', 'csrf_protection', array(
//                'ignore' => true,
//        ));
    }

    public function loadDefaultDecorators() {
        $this->setDecorators(array(
          'FormElements',
          array('HtmlTag', array('tag' => 'fieldset')),
          'Form',
        ));
    }

}

?>
