<?php

/**
 * Platform Badge Generator
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
define('DIRECTORY', 'web/images/');

class Nexva_Badge_Platforms {

    protected $__contentId;

    public function __construct($productId) {
        $this->__contentId = $productId;
    }

    public function generate() {
        $productId = $this->__contentId;
        // create badge
        $placeholder = 'platform_canvas.png';
        $product = new Model_Product();
        $config = Zend_Registry::get("config");
        $baseUrl = $config->nexva->application->base->url;
        $imagePlh = ImageCreateFromPNG(DIRECTORY . $placeholder);
        $badgeName = 'platforms_badges/' . $productId . '_cache.png';
        if (!file_exists(DIRECTORY . $badgeName) || (filectime(DIRECTORY . $badgeName) + CACHE_TIMEOUT) < time()) {
            $platforms = $product->getSupportedPlatforms($productId);
            $i = 0;
            if (is_array($platforms)) {
                foreach ($platforms as $platform) {
                    if ($platform->id == 0)
                        continue;
                    $platformImg = ImageCreateFromPNG('http://' . $baseUrl . '/web/images/platforms/' . $platform->id . '.png');
                    imagecopy($imagePlh, $platformImg, 1 + ($i * 32), 1, 0, 0, 26, 26);
                    imagedestroy($platformImg);
                    $i++;
                }
            }
            // create the image file
            ImagePng($imagePlh, DIRECTORY . $badgeName);
            imagedestroy($imagePlh);
        }
        return $badgeName;
    }

}

?>
