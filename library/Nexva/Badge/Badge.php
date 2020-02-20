<?php

/**
 * Image Badge Generator
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
// font to use

define('BADGE_DIRECTORY', 'cp/assets/badge/');

define('FONT', 'tahoma.ttf');

// cache timeout in seconds
define('CACHE_TIMEOUT', 300);

define('TEXT_FONT_SIZE', 10);

define('LINE_HEIGHT', 15);
define('LINE_LENGTH', 26);

define('TEXT_COLOR', 'DE6800');
define('TEXT_COLOR_GREY', '636363');
define('TEXT_COLOR_GREY_DARK', '939393');
define('SHADOW_COLOR', 'EAEAEA');
define('TEXT_COLOR_BOTTOM', '000000');

class Nexva_Badge_Badge {

    protected $__contentId;

    public function __construct($productId) {
        $this->__contentId = $productId;
    }

    public function createBadge($size = 'large', $template = 'nexva') {
        //load the content array
        $productId = $this->__contentId;
        $config = Zend_Registry::get("config");
        $baseUrl = $config->nexva->application->base->url;
        $product = new Model_Product();
        $productDetails = $product->getProductDetailsById($productId);
        // generate the image
        $badgeName = 'badges/' . $productId . '_' . $size . '_' . $template . '_cache.png';
    if (!file_exists(BADGE_DIRECTORY . $badgeName) || (filectime(BADGE_DIRECTORY . $badgeName) + CACHE_TIMEOUT) < time()) {
        switch ($size) {
            case 'large':
                $badgePlaceholder = 'templates/' . $template . '/badge_large.png';
                $textx = 120;
                $texty = 25;
                $qrx = 310;
                $qry = 10;
                $rx = 120;
                $ry = 64;
                break;
            case 'medium':
                $badgePlaceholder = 'templates/' . $template . '/badge_medium.png';
                $textx = 45;
                $texty = 25;
                $qrx = 240;
                $qry = 10;
                $rx = 45;
                $ry = 60;
                break;
            case 'small':
                $badgePlaceholder = 'templates/' . $template . '/badge_small.png';
                $textx = 40;
                $texty = 25;
                $rx = 40;
                $ry = 60;
                break;
            case 'qr':
                $badgePlaceholder = 'templates/' . $template . '/badge_qr.png';
                $qrx = 35;
                $qry = 10;
                break;
            default:
                $size = 'large';
                $badgePlaceholder = 'templates/' . $template . '/badge_large.png';
                $textx = 120;
                $texty = 25;
                $qrx = 310;
                $qry = 10;
                $rx = 120;
                $ry = 64;
                break;
        }
        $image = ImageCreateFromPNG(BADGE_DIRECTORY . $badgePlaceholder);
        // destination image size
        $dx = imagesx($image);
        $dy = imagesy($image);
        // add text product name
        $color = imagecolorallocate($image, hexdec(substr(TEXT_COLOR, 0, 2)), hexdec(substr(TEXT_COLOR, 2, 2)), hexdec(substr(TEXT_COLOR, 4, 2)));
        $colorGrey = imagecolorallocate($image, hexdec(substr(TEXT_COLOR_GREY, 0, 2)), hexdec(substr(TEXT_COLOR_GREY, 2, 2)), hexdec(substr(TEXT_COLOR_GREY, 4, 2)));
        $colorGreyDark = imagecolorallocate($image, hexdec(substr(TEXT_COLOR_GREY_DARK, 0, 2)), hexdec(substr(TEXT_COLOR_GREY_DARK, 2, 2)), hexdec(substr(TEXT_COLOR_GREY_DARK, 4, 2)));
        $colorBottom = imagecolorallocate($image, hexdec(substr(TEXT_COLOR_BOTTOM, 0, 2)), hexdec(substr(TEXT_COLOR_BOTTOM, 2, 2)), hexdec(substr(TEXT_COLOR_BOTTOM, 4, 2)));
        $colorShadow = imagecolorallocate($image, hexdec(substr(SHADOW_COLOR, 0, 2)), hexdec(substr(SHADOW_COLOR, 2, 2)), hexdec(substr(SHADOW_COLOR, 4, 2)));

        if ($size != 'qr') {
            // ADDING PLATFORMS
            $platforms = new Nexva_Badge_Platforms($productId);
            $platformPngPath = $platforms->generate();
            $platformPng = ImageCreateFromPNG(DIRECTORY . $platformPngPath);
            imagecopy($image, $platformPng, $textx, $texty, 0, 0, 200, 27);
            // PRODUCT NAME
            $productName = '';
            $productName = $productDetails['name'];
            // product name
//      ImageTTFText($image, TEXT_FONT_SIZE, 0, $textx, $texty, $colorShadow, BADGE_DIRECTORY . FONT, $productName);
            ImageTTFText($image, TEXT_FONT_SIZE, 0, ($textx - 1), ($texty - 5), $color, BADGE_DIRECTORY . FONT, $productName);
            // product description
//        $productDesc = strip_tags($productDetails['desc']);
//        ImageTTFText($image, 8, 0, $textx, $texty + 15, $colorGrey, BADGE_DIRECTORY . FONT, substr($productDesc, 0, 35));
//        ImageTTFText($image, 8, 0, $textx, $texty + 30, $colorGrey, BADGE_DIRECTORY . FONT, substr($productDesc, 35, 32) . ' ...');
//        adding platform icons
            // add fivestar ratings
//    @fopen('http://' . $baseUrl . '/qrcode/' . $productDetails['id'], $mode);
            @fopen('http://' . $baseUrl . '/index/rating/id/' . $productDetails['id'], 'r');
            if (file_exists('web/images/fivestar/' . $productId . '_cache.png')) {
                $ratings = ImageCreateFromPNG('web/images/fivestar/' . $productId . '_cache.png');
                imagecopy($image, $ratings, $rx, $ry-5, 0, 0, 57, 12);
                imagedestroy($ratings);
                // add review texts
                $rating = new Model_Review();
                $rating->getAverageRating($productId);
                $ratingValue = $rating->getNumberOfReviews();
                ImageTTFText($image, 8, 0, $textx + 65, $ry+5, $colorGreyDark, BADGE_DIRECTORY . FONT, '( ' . $ratingValue . ' Reviews )');
            }
            // add final sentence
            $textBottom = 'Download Now.';
//      ImageTTFText($image, TEXT_FONT_SIZE, 0, $textx, 94, $colorGrey, BADGE_DIRECTORY . FONT, $textBottom);
            ImageTTFText($image, TEXT_FONT_SIZE, 0, $textx, 88, $color, BADGE_DIRECTORY . FONT, $textBottom);
        }

        // add thumbnail water mark
        if ($size == 'large') {
            $thumbPath = substr($productDetails['thumb'], 1);
            $fileType = strtolower(end(explode(".", $thumbPath)));
            $size = getimagesize($thumbPath);
            switch ($size['mime']) {
                case "image/png":
                    $productThumb = ImageCreateFromPNG($thumbPath);
                    break;
                case "image/jpeg":
                    $productThumb = ImageCreateFromJPEG($thumbPath);
                    break;
                case "image/gif":
                    $productThumb = ImageCreateFromGIF($thumbPath);
                    break;
                case "image/bmp":
                    $productThumb = imagecreatefromwbmp($thumbPath);
                    break;
            }
            // Resize
            $y = imagesy($productThumb);
            $x = imagesx($productThumb);
//        imagecopyresized($productThumb, $productThumb, 0, 0, 0, 0, 80, 80, $x, $y);
//        $new_image = imagecreatetruecolor(80, 80);
//        imagecopyresampled($new_image, $productThumb, 0, 0, 0, 0, 80, 80, $x, $y);

            if ($y < 80 || $x < 80)
                $x = $y = 80;
            imagecopyresampled($productThumb, $productThumb, 0, 0, 0, 0, 80, 80, $x, $y);
// Set the margins for the stamp and get the height/width of the stamp image
            // Place the product Thumb
            $sx = (imagesx($productThumb) < 80) ? imagesx($productThumb) : 80;
            $sy = (imagesy($productThumb) < 80) ? imagesy($productThumb) : 80;
            $imagePlh = ImageCreateFromPNG(DIRECTORY . 'thumbnail_canves.png');
            imagecopy($imagePlh, $productThumb, 0, 0, 0, 0, $sx, $sy);
            imagecopy($image, $imagePlh, $dx - 367, $dy - 90, 0, 0, $sx, $sy);
            imagedestroy($productThumb);
            imagedestroy($imagePlh);
        }

        // Place the buy button
        // Buy now button
//    if ($size != 'qr') {
//      $buyButton = ImageCreateFromPNG(BADGE_DIRECTORY . 'images/buy_button_grey.png');
//      imagecopy($image, $buyButton, $buyButx, $buyButy, 0, 0, 100, 40);
//    }
        // Place the QR code
        // QR code
        if ($size != 'small') {
            $slugHelper = new Nexva_View_Helper_Slug();
            $slug = $slugHelper->slug($productDetails['name']);
            
            $qrcodeHelper = new Nexva_View_Helper_Qr();
            $qrcode = $qrcodeHelper->qr("http://" . $config->nexva->application->base->url . "/app/$slug/" . $productDetails['id'] . '/ref/nl',
                    100, 100);
            
            $qrCode = ImageCreateFromPNG($qrcode);
            
            imagecopy($image, $qrCode, $qrx, $qry, 10, 10, 80, 80);
            imagedestroy($qrCode);
            ImageTTFText($image, 7, 0, $qrx, $qry + 1, $colorGrey, BADGE_DIRECTORY . FONT, 'Download to mobile');
            ImageTTFText($image, 7, 0, $qrx + 7, 68 + $qry + 17, $colorGrey, BADGE_DIRECTORY . FONT, 'nexva.com/' . $productDetails['id']);
//        ImageTTFText($image, 7, 0, $qrx + 54, 68 + $qry + 16, $colorBottom, BADGE_DIRECTORY . FONT, $productDetails['id']);
        }
//      die();
//    exit;
        // create the image file
        ImagePng($image, BADGE_DIRECTORY . $badgeName);
        imagedestroy($image);
    }
//      exit;
        header("Content-Type: image/PNG");
        readfile(BADGE_DIRECTORY . $badgeName);
    }

}

?>
