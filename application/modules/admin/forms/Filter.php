<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */


class Admin_Form_Filter extends Zend_Form {

    public function init() {

//        parent::init();
//        $this->addPrefixPath('Admin_Form', 'Admin/Form')->addElementPrefixPath('Admin', 'Valiate');

        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setName('user_filter');

        // Add userID as a hidden
                // Add a user name element
//        $this->addElement('hidden', 'id', array());

        // Add a status element
        $this->addElement('select', 'filter', array(
                'label'      => 'Filter by User Status',
                'class' => 'select-input small',
                'multioptions' => array(2 => 'All', 0 => 'Blocked', 1=> 'Active'),
        ));

        // Add the filter button
        $this->addElement('submit', 'submit', array(
                'ignore'   => true,
                'label'    => 'Filter',
                'class' => 'txt-input small'
        ));

        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf_filter', array(
                'ignore' => true,
        ));
    }
}
