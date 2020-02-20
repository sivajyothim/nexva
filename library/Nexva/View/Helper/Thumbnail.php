<?php

/**
 * A view helper to generate/display image thumbnails
 *
 * @author jahufar
 * @version $id$
 *
 */
class Nexva_View_Helper_Thumbnail extends Zend_View_Helper_Abstract {

  public function thumbnail($src, $options, $htmlOptions = array(), $returnUrl = false) {
  	$config = Zend_Registry::get ( "config" )->nexva->application->product_images_domain;
  	
  	if($_SERVER['HTTP_HOST'] == 'admin.nexva.com' || $_SERVER['HTTP_HOST'] == 'cp.nexva.com' )
  	     $url = 'http://nexva.com';
  	else
  	    $url = 'http://'.$config;

    $filePath = APPLICATION_PATH . '/../public' . $src;
    if (!is_file($filePath)) {
      $src = '/mobile/images/default.jpg';
      $src = '/mobile/images/coming_soon.jpg';
    }
    $args = '';
    if (is_array($options)) {
      foreach ($options as $key => $value) {
        $args .= "&$key=$value";
      }
    }
    
    unset($key); unset($value);
    $htmlArgs   = '';
    if (!empty($htmlOptions) && is_array($htmlOptions)) {
        foreach ($htmlOptions as $key => $value) {
            $value      = htmlspecialchars($value);
            $htmlArgs   .= " $key = '{$value}' ";
        }
    }

    $width  = $height  = '';
    if (!isset($htmlOptions['autoheight'])) {
        $width = !empty($options['w']) ? 'width = ' . $options['w'] : '';
        $height = !empty($options['h']) ? 'height = ' . $options['h'] : '';    
    }
    
    $radious = 0;
    
    if ($returnUrl) {
        return $url . '/vendors/phpThumb/phpThumb.php?src=' . $src . $args . '&fltr[]=ric|' . $radious . '|' . $radious . '&q=100&f=png';
    } else {
        return '<img ' . $htmlArgs . ' ' . $width . ' ' . $height . ' src="'.$url.'/vendors/phpThumb/phpThumb.php?src=' . $src . $args . '&fltr[]=ric|' . $radious . '|' . $radious . '&q=100&f=png">';    
    }
    
    
  }

}

?>