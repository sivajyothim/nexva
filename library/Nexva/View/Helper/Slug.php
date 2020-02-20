<?php
/**
 * A view helper to convert strings to web friendly urls. 
 *
 * @author jahufar
 */
class Nexva_View_Helper_Slug extends Zend_View_Helper_Abstract {

      public function slug($url) {
          $url = strtolower(trim($url));
          $url = str_replace(array("(", ")", "!"), "", $url);
          
          $url = preg_replace('/[^a-z0-9-]/', '-', $url);
          $url = preg_replace('/-+/', "-", $url);
          return $url;
      }
}
?>
