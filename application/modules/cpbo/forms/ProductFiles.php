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
class Cpbo_Form_ProductFiles extends Zend_Form {

  protected $_maxfilecount;
  protected $_filetypes;

  public function setMaxCount($max = 5) {
    $this->_maxfilecount = $max;
  }

  public function setFileType($filetypes = 'jad') {
    $this->_filetypes = $filetypes;
  }

  public function createFileForm() {
//        parent::init();
//        $this->addPrefixPath('Admin_Form', 'Admin/Form')->addElementPrefixPath('Admin', 'Valiate');
    // Set the method for the display form to POST
    $this->setMethod('post');
    $this->setName('product_files');
    $this->setAttrib('enctype', 'multipart/form-data');
    // Application ID
    $this->addElement('hidden', 'id', array());
    $this->addElement('hidden', 'review', array());
    $this->addElement('hidden', 'build_id', array());


    // Addd a original file element
    $max = $this->_maxfilecount;
    $filetypes = $this->_filetypes;
//        exit;
    $max = 1;
    $requiredFiles = 2;
    $this->addElement('file', 'file', array(
//                'label'         => 'Screenshots',
//                'decorators' => $this->uploadDecorators,
      'class' => 'txt-input medium multi',
      'validators' => array(
        array('Count', false, array('min' => 1, 'max' => $max)),
        array('Size', false, 1024000),
//                        array('Extension', false, "'$filetypes'")
      ),
      'multiFile' => $max,
//                'destination'=> tempnam($dir, $prefix)
    ));

    // TODO add CSRF protections
    // And finally add some CSRF protection
//        $this->addElement('hash', 'csrf', array(
//                'ignore' => true,
//        ));
    return $this;
  }

  public function createUrlForm() {
//        parent::init();
//        $this->addPrefixPath('Admin_Form', 'Admin/Form')->addElementPrefixPath('Admin', 'Valiate');
    // Set the method for the display form to POST
    $this->setMethod('post');
    $this->setName('product_files');
    $this->setAttrib('enctype', 'multipart/form-data');
    // Application ID
    $this->addElement('hidden', 'id', array());
    $this->addElement('hidden', 'review', array());
    $this->addElement('hidden', 'build_id', array());


    $this->addElement('textarea', 'url', array(
//      'label' => 'URL (eg: http://www.nexva.com)',
//                'decorators' => $this->uploadDecorators,
      'class' => 'txt-input medium',
      'cols' => 50,
      'rows' => 8,
//                'destination'=> tempnam($dir, $prefix)
    ));

    // TODO add CSRF protections
    // And finally add some CSRF protection
//        $this->addElement('hash', 'csrf', array(
//                'ignore' => true,
//        ));
    return $this;
  }

  public function loadDefaultDecorators() {
    $this->setDecorators(array(
      array('ViewScript', array('script' => 'partials/product.phtml'))
    ));
  }

}

?>
