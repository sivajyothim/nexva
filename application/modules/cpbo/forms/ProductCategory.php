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
class Cpbo_Form_ProductCategory extends Zend_Form {

    public function init() {
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setName('product_category');

        $this->addElement('hidden', 'id', array());
        $this->addElement('hidden', 'review', array());

        // Add the submit button
        $this->addElement('submit', 'submit', array(
                'ignore'   => true,
                'label'    => 'Save and Continue',
                'href' => 'tab-files',
//                'decorators' => $this->buttonDecorators,
                'class' => 'txt-input small submit button'
        ));


        // And finally add some CSRF protection
//        $this->addElement('hash', 'csrf', array(
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
