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
class Admin_Form_ProductVisuals extends Zend_Form {
	
public $buttonDecorators = array(
      'ViewHelper',
      'Errors',
      array(array('data' => 'HtmlTag'), array('align' => 'right'))//,
//            array(array('label' => 'HtmlTag'), array('tag' => 'td')),
 //     array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
    );
	

  public function init() {

//        parent::init();
//        $this->addPrefixPath('Admin_Form', 'Admin/Form')->addElementPrefixPath('Admin', 'Valiate');
    // Set the method for the display form to POST
    $this->setMethod('post');
    $this->setName('product_visuals');
    $this->setAttrib('enctype', 'multipart/form-data');
    // Application ID
    $this->addElement('hidden', 'id', array());
    $this->addElement('hidden', 'review', array());

    // get init configurations
    $config = Zend_Registry::get('config');
    $visualPath = $_SERVER['DOCUMENT_ROOT'] . $config->product->visuals->dirpath;
    $size = $config->product->visuals->size;
    $count = $config->product->visuals->count;
    $types = $config->product->visuals->types;
    // TODO : add destination path and file Size file Extentions to the config
    $min = 0;

    
    // Add a scrennshots element
    $this->addElement('file', 'screenshots', array(
//                'label'         => 'Screenshots',
//                'decorators' => $this->uploadDecorators,
      'class' => 'txt-input medium',
      'validators' => array(
        array('Count', false, array('min' => $min, 'max' =>  $count)),
        array('Size', false, $size),
        array('Extension', false, $types),
        array('ImageSize', false,
          array('minwidth' => 200,
            'minheight' => 200,
            'maxwidth' => 1500,
            'maxheight' => 1500))
      ),
      'multiFile' => $count,
      'destination' => $visualPath
    ));

    // add tumbnails elements
    $this->addElement('file', 'thumbnail', array(
//                'label'         => 'Screenshots',
//                'decorators' => $this->uploadDecorators,
      'class' => 'txt-input medium',
      'validators' => array(
        array('Count', false, array('min' => $min, 'max' => 1)),
        array('Size', false, $size),
        array('Extension', false, $types),
        array('ImageSize', false,
          array('minwidth' => 200,
            'minheight' => 200,
            'maxwidth' => 1500,
            'maxheight' => 1500))
      ),
      'filters' => array(
        'Rename' => md5(uniqid()) . '.png'
      ),
      'destination' => $visualPath
    ));


    // Add the submit button
    $this->addElement('submit', 'submit', array(
      'ignore' => true,
      'label' => 'Next',
      'href' => 'tab-category',
//      'decorators' => $this->buttonDecorators,
      'class' => 'submit button'
    ));

    // And finally add some CSRF protection
//        $this->addElement('hash', 'csrf_protection', array(
//                'ignore' => true,
//        ));
  }

  public function loadDefaultDecorators() {
    $this->setDecorators(array(
      array('ViewScript', array('script' => 'partials/product.phtml'))
    ));
  }

}

?>
