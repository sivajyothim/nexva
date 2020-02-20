<?php
/**
 * A view helper that parses wiki-markup into HTML
 *
 * @author jahufar 
 */

class Nexva_View_Helper_Wikimarkup extends Zend_View_Helper_Abstract {

    public function Wikimarkup($data) {

        $creole = new Nexva_Util_WikiMarkupParser_Creole_Creole();
        return $creole->parse($data);
    }
}
?>
