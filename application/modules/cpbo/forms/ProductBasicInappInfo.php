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
class Cpbo_Form_ProductBasicInappInfo extends Zend_Form {

  public $buttonDecorators = array(
    'ViewHelper',
    'Errors',
    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2', 'align' => 'right')),
//            array(array('label' => 'HtmlTag'), array('tag' => 'td')),
    array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
  );
  public $elementDecorators = array(
    'ViewHelper',
    array(array('open' => 'HtmlTag'), array('tag' => 'td', 'width' => '80%', 'openOnly' => true)),
    array('Description', array('tag' => 'small', 'class' => 'description', 'escape'=>false)),
    array(array('close' => 'HtmlTag'), array('tag' => 'td', 'width' => '80%', 'closeOnly' => true)),
    array('Label', array('tag' => 'td', 'width' => '20%')),
    array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
  );

  public function init() {

    $this->setMethod('post');
    $this->setName('product_basic_info');

    $validator = new Zend_Validate_Regex('([A-Za-z0-9]+)');
    $validator->setMessage(
        'Your content name can only contain letters, numbers and underscores (_).');

    // Add a product name element
    $this->addElement('text', 'name', array(
      'decorators' => $this->elementDecorators,
      'label' => 'Content Name:',
      'required' => true,
      'size' => 250,
      'filters' => array('StringTrim'),
      'class' => 'txt-input medium validate(required, rangelength(1,250))',
      'validators' => array(
        array('StringLength', false, array(1, 250))
      ),
    ));


    // Add a product price element
    $this->addElement('text', 'price', array(
      'decorators' =>  $this->elementDecorators,
      'label' => 'Suggested Retail Price (SRP) in USD:',
      'required' => true,
      'autocomplete' => 'off',
      'Description' => '<a href="" id="info" rel="facebox"></a> 
       
       
       
       </div>',      
      'filters' => array('StringTrim'),
      'class' => 'txt-input small validate(required, range(.1, 10000))',
    ));

    $this->addElement('textarea', 'brief_description', array(
      'id' => 'brief_description',
      'decorators' => $this->elementDecorators,
      'label' => 'Brief Description (5000 words):',
      'Description' => 'A brief description of your content.',
      'required' => true,
      'rows' => 5,
      'filters' => array('StringTrim'),
      'class' => 'txt-input medium validate(required, rangelength(5,500))',
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

    // Add the submit button
    $this->addElement('submit', 'submit', array(
      'ignore' => true,
      'label' => 'Next',
      'href' => 'tab-screenshots',
      'decorators' => $this->buttonDecorators,
      'class' => 'submit button'
    ));
    
        // Add userID as a hidden
    // Add a user name element
    $this->addElement('hidden', 'id', array('decorators' =>  array( 'ViewHelper')));
    $this->addElement('hidden', 'review', array( 'decorators' =>  array( 'ViewHelper')));
    $this->addElement('hidden', 'payout', array( 'decorators' =>  array( 'ViewHelper')));
    $this->addElement('hidden', 'inapp', array( 'value' => 1 ));
    $this->addElement('hidden', 'status', array( 'value' => 'PENDING_APPROVAL'));


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
