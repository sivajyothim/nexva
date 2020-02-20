<?php

/**
 * Helper to trim and optionally add elipses of your choice to long string.
 *
 * @author jahufar
 * @version $id$
 *
 */
class Nexva_View_Helper_Trim extends Zend_View_Helper_Abstract {

  /**
   * Trim and optionally add elipses of your choice to long string.
   *
   * @param string $string
   * @param int $length
   * @param boolean $addElipsis
   * @param string $elipsis
   * @return string Trimmed (and elipsed) version of the string that was passed in.
   */
  public function trim($string, $length, $addElipsis = true, $elipsis = '...', $html = false, $unicode = true) {
    if (strlen($string) < $length)
      return $string;

    
    $sessionChapDetails = '';
    
    $sessionChapDetails = new Zend_Session_Namespace('partnermobile');
    
    if($sessionChapDetails and $sessionChapDetails->id == 131024)
        return strtok(wordwrap($string, $length, "..."), "");
    
    $value = rtrim(substr($string, 0, $length));
    if ($addElipsis)
      $value .= $elipsis;
    if ($html)
      return $value;
    
    //Added for farsi unicode issue when limits the length
    if($unicode){
       if(mb_strlen($string, 'utf-8') >= $length){
           $value = mb_substr($string, 0, $length - 5, 'utf-8').'...';
       } 
    }
    return strip_tags($value);

  }
}

?>
