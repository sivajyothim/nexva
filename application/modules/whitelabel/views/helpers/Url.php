<?php
class Whitelabel_View_Helper_Url extends Zend_View_Helper_Abstract {
    
    public function Url() {
        return $this;
    }
    
    /**
     * Returns the category view page url for an app
     * @param $catId
     * @param $opts
     */
    public function category($catId, $opts = array()) {
        return '/category/view/id/' . $catId;
    }
    
    /**
     * Returns the app view url for a product 
     * @param $productId
     * @param $opts
     */
    public function product($productId, $opts = array()) {
        return '/app/view/id/' . $productId;
    }
    
    /**
     * Method returns the URL for an image stored in the themefolder 
     * @param $themename name of the theme. This maps to the folder in the filesystem
     * @param $image
     */
    public function wlImage($themename, $image) {
        return "/mobile/whitelables/{$themename}/web/images/{$image}";
    }
    
    /**
     * Returns the url for a stylesheet given the themename and the stylesheetname
     * @param $themename
     * @param $styleName
     */
    public function wlCss($themename, $styleSheetName) {
        return "/mobile/whitelables/{$themename}/web/css/{$styleSheetName}";
    }
}