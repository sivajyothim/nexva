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
class Admin_Form_ProductBasicInfo extends Zend_Form {

    public $buttonDecorators = array(
      'ViewHelper',
      'Errors',
      array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2')),
//            array(array('label' => 'HtmlTag'), array('tag' => 'td')),
      array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    );
    public $elementDecorators = array(
      'ViewHelper',
      array(array('open' => 'HtmlTag'), array('tag' => 'td', 'width' => '60%', 'openOnly' => true)),
//            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'width' => '60%', 'openOnly' => true)),
//            'Description',
      array('Description', array('tag' => 'small', 'class' => 'description')),
      array(array('close' => 'HtmlTag'), array('tag' => 'td', 'width' => '60%', 'closeOnly' => true)),
      array('Label', array('tag' => 'td', 'width' => '40%')),
      array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    );

    public function init() {

//        parent::init();
//        $this->addPrefixPath('Admin_Form', 'Admin/Form')->addElementPrefixPath('Admin', 'Valiate');
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setName('product_basic_info');

        // Add userID as a hidden
        // Add a user name element
        $this->addElement('hidden', 'id', array());
        $this->addElement('hidden', 'review', array());
        $this->addElement('hidden', 'subcategory', array());
/*
        $this->addElement('select', 'user_id', array(
          'label' => 'Content Provider :',
          'required' => true,
          'decorators' => $this->elementDecorators,
          'filters' => array('StringTrim'),
          'class' => 'validate(required) select-input small',
          'multioptions' => array()
//          'multioptions' => array('DEMO' => 'Demo', 'FREEWARE' => 'Freeware', 'COMMERCIAL' => 'Commercial', 'SHAREWARE' => 'Shareware Registration')
        ));
//
*/
        
        // validator on user name to not contain any special charectors
        $validator = new Zend_Validate_Regex('([A-Za-z0-9]+)');
        $validator->setMessage(
            'Your content name can only contain letters, numbers and underscores (_).');

        // Add a product name element
        $this->addElement('text', 'name', array(
          'decorators' => $this->elementDecorators,
          'label' => 'Content Name:',
//                'Description' => '1212 ',
          'required' => true,
          'size' => 10,
          'filters' => array('StringTrim'),
          'class' => 'txt-input medium validate(required, rangelength(1,250))',
          'validators' => array(
            array('StringLength', false, array(1, 250))
          ),
        ));

        $this->addElement('select', 'category_parent', array(
          'label' => 'Category:',
//                'Description' => 'adafsdfasdfa ',
          'required' => true,
          'decorators' => $this->elementDecorators,
          'class' => 'validate(required) select-input',
          'multioptions' => array()
        ));

        // Add a product version element
        $this->addElement('text', 'product_version', array(
          'decorators' => $this->elementDecorators,
          'label' => 'Content Version:',
          'filters' => array('StringTrim'),
          'class' => 'txt-input small',
        ));

        // Add a product price element
        $this->addElement('text', 'price', array(
          'decorators' => $this->elementDecorators,
          'label' => 'Suggested Retail Price (SRP) in USD:',
          'required' => true,
          'Description' => 'If your content is for free then enter 0 as the amount.',
          'filters' => array('StringTrim'),
          'class' => 'txt-input small validate(required, number)',
        ));

        // Add a product type element
//        $this->addElement('select', 'apptype', array(
//                'decorators' => $this->elementDecorators,
//                'label'      => 'Network Aware Application :',
//                'required'   => true,
//                'filters'    => array('StringTrim'),
//                'class' => 'select-input small',
//                'Description' => 'An application that is network aware is one that communicates with other devices or services using a wireless
//                    network. This wireless network can be either a carrier\'s network or a local area network. Examples of such application
//                    include stock tracking applications, multiplayer games, and applications that retrieve weather updates, traffic updates,
//                    movie times, or sports scores.',
//                'multioptions' => array('Yes', 'No')
//        ));
        // Add a product keyword element
        $this->addElement('text', 'keywords', array(
          'decorators' => $this->elementDecorators,
          'label' => 'Keywords :',
          'required' => true,
          'Description' => 'This is used for internal search purposes, Please keep your keyworks relevant to your content. Separate each keyword by comma (,). Not case sensitive.',
          'filters' => array('StringTrim'),
          'class' => 'txt-input large validate(required)',
        ));

        // Add a product brief description element
        $this->addElement('textarea', 'brief_description', array(
          'id' => 'brief_description',
          'decorators' => $this->elementDecorators,
          'label' => 'Brief Description (500 words):',
          'Description' => 'A brief description of your content.',
          'required' => true,
          'rows' => 5,
          'filters' => array('StringTrim'),
          'class' => 'txt-input medium validate(required, rangelength(5,500))',
        ));

        // Add a product description element
        $this->addElement('textarea', 'full_description', array(
          'id' => 'full_description',
          'decorators' => $this->elementDecorators,
          'label' => 'Detail Description :',
          'required' => true,
          'Description' => 'More detailed description of your content.',
          'rows' => 10,
          'filters' => array('StringTrim'),
          'class' => 'txt-input medium validate(required)',
        ));

        // Add a email element
        $this->addElement('text', 'notify_email', array(
          'decorators' => $this->elementDecorators,
          'label' => 'Notification of Purchase email address :',
          'required' => true,
          'Description' => 'We will email you an alert, when your content is purchased.',
          'filters' => array('StringTrim'),
          'validators' => array(
            'EmailAddress',
          ),
          'class' => 'validate(email) txt-input small'
        ));

        // Add a notify element
        $this->addElement('checkbox', 'desktop_product', array(
          'label' => 'Is this a desktop content?:',
          'decorators' => $this->elementDecorators,
          'class' => 'txt-input small'
        ));
        
        // Add show on nexpager element 
        $this->addElement('checkbox', 'show_in_nexpager', array(
          'label' => 'Show in neXpager?:',
          'decorators' => $this->elementDecorators,
          'class' => 'txt-input small'
        ));
        

        // Add a notify element
        $this->addElement('checkbox', 'is_featured', array(
          'label' => 'Is this a featured product?:',
          'decorators' => $this->elementDecorators,
          'class' => 'txt-input small'
        ));

        $this->addElement('checkbox', 'is_suggested', array(
          'label' => 'Do you want to suggest this product?:',
          'decorators' => $this->elementDecorators,
          'class' => 'txt-input small'
        ));

        //Add google app id
        $this->addElement('text','google_id',array(
            'decorators' => $this->elementDecorators,
            'label' => 'Google Play App id :',
            'filters' => array('StringTrim'),
            'class' => 'txt-input large'
        ));

        //Add apple app id
        $this->addElement('text','apple_id',array(
            'decorators' => $this->elementDecorators,
            'label' => 'iTunes App Store App id :',
            'filters' => array('StringTrim'),
            'class' => 'txt-input large'
        ));

        // Add show on nexpager element 
        $this->addElement('checkbox', 'inapp_payment', array(
          'label' => 'InApp Payment:',
          'decorators' => $this->elementDecorators,
          'class' => 'txt-input small'
        ));
        
        
        $this->addElement('select', 'neXpayer_enabled', array(
          'label' => 'Nexplayer Enable:',
          'required' => true,
          'decorators' => $this->elementDecorators,
          'class' => 'validate(required) select-input',
          'multioptions' => array('No'=>'No','Yes'=>'Yes')
        ));
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
          'ignore' => true,
          'label' => 'Save and Continue',
          'href' => 'tab-screenshots',
          'decorators' => $this->buttonDecorators,
          'class' => 'txt-input small submit button'
        ));

        /*$this->addElement('text', 'keywords', array(
            'decorators' => $this->elementDecorators,
            'label' => 'Keywords :',
            'required' => true,
            'Description' => 'This is used for internal search purposes, Please keep your keyworks relevant to your content. Separate each keyword by comma (,). Not case sensitive.',
            'filters' => array('StringTrim'),
            'class' => 'txt-input large validate(required)',
        ));*/

    }

    public function loadDefaultDecorators() {
        $this->setDecorators(array(
          'FormElements',
          array('HtmlTag', array('tag' => 'table')),
          'Form',
        ));
    }

}

?>
