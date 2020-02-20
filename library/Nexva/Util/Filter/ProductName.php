<?php
/**
 * A filter to the content name which filters content name string. 
 *
 * @author chathura
 */
class Nexva_Util_Filter_ProductName implements Zend_Filter_Interface {

    /**
     * (non-PHPdoc)
     * @see Filter/Zend_Filter_Interface::filter()
     */
      public function filter($name) {
            //We're now disabling this as we're going to make all chars valid 
            //for internationalization
          return $name;
      	  $name = trim($name);
      	  $name = preg_replace('/-+/', "-", $name);
          $name = preg_replace('/[^a-zA-Z0-9-\s_]/', '-', $name);
          return $name;
      }
}
