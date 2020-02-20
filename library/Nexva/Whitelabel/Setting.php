<?php
/**
 * This is a settings container for the whitelabel options
 * @author John
 *
 */
class Nexva_Whitelabel_Setting {
    
    protected $options  = null;
    
    public function setOptions($options) {
        $this->options  = $options;
    }
    
    public function __get($name) {
        if (isset($this->options->{$name})) {
            return $this->options->{$name};
        } else {
            return null;
        }
    }
}