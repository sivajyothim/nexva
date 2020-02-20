<?php

/**
 * Fivestart Badge Generator
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
define('DIRECTORY', 'web/images/');

define('CACHE_TIMEOUT', 300);

class Nexva_Badge_Fivestar {

    protected $__contentId;

    public function __construct($productId) {
        $this->__contentId = $productId;
    }

    public function ratings() {
        $productId = $this->__contentId;
        $rating = new Model_Review();
        $ratingValue = $rating->getAverageRating($productId);
        // create badge
        $placeholder = 'fivestar_bg.png';
        $imagePlh = ImageCreateFromPNG(DIRECTORY . $placeholder);
        $badgeName = 'fivestar/' . $productId . '_cache.png';
        if (!file_exists(DIRECTORY . $badgeName) || (filectime(DIRECTORY . $badgeName) + CACHE_TIMEOUT) < time()) {
            $starFull = ImageCreateFromPNG(DIRECTORY . 'star_full.png');
            $starHalf = ImageCreateFromPNG(DIRECTORY . 'star_half.png');
            $starEmpty = ImageCreateFromPNG(DIRECTORY . 'star_empty.png');
            for ($i = 0; $i < 5; $i++) {
                if (($ratingValue == NULL))
                    $img = $starEmpty;
                else if (0 < $ratingValue && $ratingValue < 1)
                    $img = $starHalf;
                else
                    $img = $starFull;
                imagecopy($imagePlh, $img, 11 * $i, 0, 0, 0, 11, 12);
                if ($ratingValue >= 1)
                    $ratingValue--;
            }
            // create the image file
            ImagePng($imagePlh, DIRECTORY . $badgeName);
            imagedestroy($imagePlh);
        }
        header("Content-Type: image/PNG");
        readfile(DIRECTORY . $badgeName);
    }

}

?>
