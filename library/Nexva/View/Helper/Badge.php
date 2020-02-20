<?php

/**
 * A view helper to generate/display image thumbnails
 *
 * @author jahufar
 * @version $id$
 *
 */
class Nexva_View_Helper_Badge extends Zend_View_Helper_Abstract {

  public function badge($contentId = null, $size = 'large') {
    $product = new Model_Product();
    $productDetails = $product->getProductDetailsById($contentId);

//        return '<img alt="image" ' . $width . ' ' . $height . ' src="/vendors/phpThumb/phpThumb.php?src=' . $src . $args . '&fltr[]=ric|8|8&q=100">';
//    return '<img src="/vendors/phpThumb/phpThumb.php?src=../../cp/assets/badge/badge.png&fltr[]=wmi|images/watermark.jpg|*|25&fltr[]=bord|1|0|0|E47924&fltr[]=wmt|neXva|12|L|E47924|cp/assets/badge/arial.ttf|100|100&f=png" alt="">';
    return '<img src="/vendors/phpThumb/phpThumb.php?src=../../cp/assets/badge/badge.png&fltr[]=wmi|images/watermark.jpg|*|25" alt="">';
  }

}

?>