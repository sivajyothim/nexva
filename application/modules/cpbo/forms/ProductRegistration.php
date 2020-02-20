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
class Cpbo_Form_ProductRegistration extends Zend_Form {
    
	public $buttonDecorators = array(
            'ViewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2',  'align' => 'right')),
//            array(array('label' => 'HtmlTag'), array('tag' => 'td')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    );

    public $elementDecorators = array(
            'ViewHelper',
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'width' => '80%')),
            array('Description', array('tag'  => 'p', 'class' => 'description')),
            array('Label', array('tag' => 'td', 'width' => '20%')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    );
    
    public $elementDecoratorsCommercial = array( 
            'ViewHelper',
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'width' => '80%')),
            array('Description', array('tag'  => 'p', 'class' => 'description')),
            array('Label', array('tag' => 'td', 'width' => '20%')),
    //        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'commercial'))
    );

     public $keyElement =     array(

            'ViewHelper',
            // This opens the wrapping td tag but doesn't close it, we'll close it on
            // the year field decorator later
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'width' => '80%', 'openOnly' => true)),
            // Using this to slip in a visual seperator "/" between both fields
            array('Description', array('tag'  => 'small', 'class' => 'description')),
            // Show the label tag displayed for exp_month
            array('Label', array('tag' => 'td', 'width' => '20%')),
        //    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true, 'class' => 'commercial'))
        );

        public $dynamicElement = array(
           'ViewHelper',
          array('Description', array('tag'  => 'small', 'class' => 'description')),
           'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'width' => '80%', 'closeOnly' => true)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true))
        );
    

    public function init() {

//        parent::init();
//        $this->addPrefixPath('Admin_Form', 'Admin/Form')->addElementPrefixPath('Admin', 'Valiate');

        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setName('product_registration');

        // Add userID as a hidden
        // Add a user name element
        $this->addElement('hidden', 'id', array());
        $this->addElement('hidden', 'review', array());
        $this->addElement('hidden', 'create', array());
//        print_r(getPlatforms());
//        exit;
        // Add a product type element
        $this->addElement('select', 'product_type', array(
                'label'      => 'Product Type :',
                'required'   => true,
                'decorators' => $this->elementDecorators,
                'filters'    => array('StringTrim'),
                'class' => 'validate(required) select-input small',
                'multioptions' => array('FREEWARE' => 'Free', 'COMMERCIAL' => 'Commercial')
                //'multioptions' => array('DEMO' => 'Demo', 'FREEWARE' => 'Freeware', 'COMMERCIAL' => 'Commercial', 'SHAREWARE' => 'Shareware Registration','IN-APP' => 'In-app')
        ));

        // Add a registraion model element
//        $this->addElement('select', 'registration_model', array(
//                'decorators' => $this->elementDecoratorsCommercial,
//                'label'      => 'Registration Model :',
//                'required'   => true,
//                'filters'    => array('StringTrim'),
//                'class' => 'select-input small registration_model',
//                'multioptions' => array('NO' => 'No Key', 'STATIC' => 'Static', 'POOL' => 'Pool', 'DYNAMIC' => 'Dynamic')
//        ));
        
        

        // Add a product name element
//        $this->addElement('textarea', 'keys', array(
//                'decorators' => $this->keyElement,
//                'required'   => false,
//                'filters'    => array('StringTrim'),
//                'class' => 'txt-input large keys',
//                'cols' => 15,
//                'rows' => 5,
//        ));
        
        
                        // Add a product name element
//        $this->addElement('text', 'dynamic', array(
////          'decorators' =>  $this->dynamicElement,
////        'disableLoadDefaultDecorators' => true, 
////         'decorators' =>  $this->dynamic,
////          'Description' => '<span id="dynamicregistration"><a href="http://developers.nexva.com/wiki/Cp/DynamicRegistration" target="_blank">Learn more about dynamic registration </a></span>',
////         'required' => true,
// 
////          'size' => 250,
////          'filters' => array('StringTrim'),
////          'class' => 'txt-input medium validate(required, rangelength(1,250))',
////          'validators' => array(
////            array('StringLength', false, array(1, 250))
////          ),
//        ));
        

        // Add the submit button
        $this->addElement('submit', 'submit', array(
                'ignore'   => true,
                'label'    => 'Next',
                'decorators' => $this->buttonDecorators,
                'class' => 'small button'
        ));


        // And finally add some CSRF protection
//        $this->addElement('hash', 'csrf', array(
//                'ignore' => true,
//        ));
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
