<?php
/* 
 * Create a simple validator for a url
 * 
 */

/**
 * Description of Uri
 *
 * @author chathura 
 */
class Nexva_Util_Validation_UrlValidator extends Zend_Validate_Abstract
{
    const INVALID_URL = 'invalidUrl';
    	

    protected $_messageTemplates = array(
        self::INVALID_URL   => "'%value%' is not a valid address must be http://www.nexva.com/path/",
    );
    
    


    public function isValid($value)
    {
    	
    
        $valueString = (string) $value;
        $this->_setValue($valueString);

        if (!Zend_Uri::check($value)) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        return true;
    }
}