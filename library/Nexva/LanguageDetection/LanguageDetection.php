<?php
/**
 * A class to reliably (in most cases) detect the user's language. 
 *
 * Multiple strategies will be used to acomplish this:
 *  1. Examine cookie for previously saved prefered language (@todo)
 *  2. Sniffing HTTP_ACCEPT_LANGUAGE (supports language weights).
 *  3. IP-To-Country translation (@todo)
 *
 * @author jahufar
 */

class Nexva_LanguageDetection_LanguageDetection  {

    protected $defaultLanguageCode = 'en';

    /**
     * Does its best to detect the language of the user. Uses UA sniffing for now. 
     * 
     * @todo Also implement geoIP
     * @return string The 2 character ISO code for the language that is detected. Always falls back to defaultLanguageCode
     */
    public function detectLanguage() {
        
        return 1;

        
    }

    /**
     * Parses HTTP_ACCEPT_LANGUAGE HTTP header to figure out the accepted languages. Supports multiple langs with weights.
     * Note: this function will strip away any locale specific stuff
     * 
     * @return array An array of accepted languages by the browser ordered by prefered (weight).
     *
     */
    private function __parseAcceptedLanguages() {

        $langs = array();

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // break up string into pieces (languages and q factors)
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

            if (count($lang_parse[1])) {
                // create a list like "en" => 0.8
                
                //strip away the locale bit (if it exists).
                $temp = array();
                foreach($lang_parse[1] as $lang) {
                    $r = explode("-", $lang);
                    if( count($r) == 2 ) array_push($temp, $r[0]); else array_push($temp, $lang);
                    
                }
               
                $langs = array_combine($temp, $lang_parse[4]);
                //$langs = array_combine($lang_parse[1], $lang_parse[4]);
                //Zend_Debug::dump($langs); die();

                // set default to 1 for any without q factor
                foreach ($langs as $lang => $val) {
                        if ($val === '') $langs[$lang] = 1;
                    
                }

                // sort list based on value
                arsort($langs, SORT_NUMERIC);
            }
        }

        return $langs;
        

    }

    private function array_key_exists_recursive($needle,$haystack) {

    foreach($haystack as $key=>$val) {
       if(is_array($val)) { if($this->array_key_exists_recursive($needle,$val)) return 1; }
       elseif($val == $needle) return 1;
      }
      return 0;
    }





}
?>
