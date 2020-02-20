<?php
/**
 * Helper class to get the site version as well as iterate through resources 
 * and appending the site version to the URI
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Feb 7, 2011
 */
class Nexva_View_Helper_SiteVersion extends Zend_View_Helper_Abstract {
    public function siteVersion() {
        return $this;
    }
    
    /**
     * Appends the version to all JS and CSS currently stored in the view helpers
     */
    public function appendVersion() {
        foreach ($this->view->headScript() as $item) {
            $atts   = $item->attributes;
            if (isset($atts['src'])) {
                $atts['src']    .= $this->view->VER;
                $item->attributes   = $atts;
            }
        }
        
        foreach($this->view->headLink() as $item) {
            if (isset($item->href)) {
                $item->href .= $this->view->VER;
            }
        }
        
    }
    
    public function getVersion() {
        return $this->view->siteVersion;
    }
}