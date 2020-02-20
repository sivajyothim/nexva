<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */


class Admin_Form_UpdateOptions extends Zend_Form {

    public function init() {

//        parent::init();
//        $this->addPrefixPath('Admin_Form', 'Admin/Form')->addElementPrefixPath('Admin', 'Valiate');

        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setName('update_options');

        // Add userID as a hidden
                // Add a user name element
//        $this->addElement('hidden', 'id', array());

        // Add a status element
        $this->addElement('select', 'update', array(
                'label'      => 'User Update Options',
                'class' => 'select-input small',
                'multioptions' => array('Blocked', 'Active', 'Delete')
        ));

        // Add the filter button
        $this->addElement('submit', 'submit', array(
                'ignore'   => true,
                'label'    => 'Update Users',
                'class' => 'txt-input small'
        ));

        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf_update', array(
                'ignore' => true,
        ));
    }
}
