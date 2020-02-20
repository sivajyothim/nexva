<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     CP
 * @version     $Id$
 */

/**
 * Description of ProductDevices Form
 *
 * @author Administrator
 */
class Cpbo_Form_ProductDevices extends Zend_Form {

    public function init() {
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setName('product_devices');

        $this->addElement('hidden', 'id', array());
        $this->addElement('hidden', 'review', array());
        $this->addElement('hidden', 'build', array());

        // Add the submit button
        $this->addElement('submit', 'submit', array(
                'ignore'   => true,
                'label'    => 'Save Build',
                'href' => 'tab-registration',
//                'decorators' => $this->buttonDecorators,
                'class' => 'txt-input small submit device_submit button'
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
