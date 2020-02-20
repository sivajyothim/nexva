<?php
/**
 * This class uses PHP iMagick to generate the badge. Use this over the 
 * GD version (Badge.php)
 * 
 * There are a lot of arbitary numbers in this class. These are just pixel perfect
 * placements of elements. I've used the $verticalAxis variable to align 
 * columns
 */

define('BADGE_CACHE', APPLICATION_PATH . '/../public/cp/assets/badge/badges/');
define('BADGES_LOAD_FROM_CACHE', true);


class Nexva_Badge_BadgeImagick {
    private $product    = null;
    private $themeData  = null;
    private $chapId     = null;
    private $imgPath    = null;
    private $webroot    = null;
    private $productModel   = null;
    
    public function __construct($productId, $chapId = false) { 

        if ($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
        	ini_set('display_errors',0);
        	ini_set('display_startup_errors',0);

        
        
        
        }
        $productModel       = new Model_Product();
        $this->product      = $productModel->getProductDetailsById($productId, true);
        if (!$this->product) {
            return false;
        }
        $this->productModel = $productModel;
        
        $this->themeData    = array();
        if ($chapId) {
            $themeModel         = new Model_ThemeMeta();
            $themeModel->setEntityId($chapId);
            $themeName          = (string) $themeModel->WHITELABLE_SITE_NAME;
            if (!empty($themeName)) {
                $this->themeData    = $themeModel->getAll();
                $this->chapId       = $chapId;
            }
        }
    }
    
    
    public function displayBadge($opts = array()) {
        if (!$this->product) {
            return false;
        }
        
        $defaults   = array(
            'type'                  => 'large',
            'show_logo'             => true,
            'font_color'            => '#E77716',
            'font_color_light'      => '#666',
            'font_color_dark'       => '#333',
            'main_bg'               => '#fff',
            'main_border'           => '#888',
            'main_font'             => APPLICATION_PATH . '/../public/cp/assets/badge/tahoma.ttf',
            'img_path'              => APPLICATION_PATH . '/../public/mobile/whitelables/nexva/badge/' 
        );

        $chapOpts   = array();
        if (isset($this->themeData->WHITELABLE_THEME_NAME)) {
            //load up the options for badges
            $wlOptPath  = APPLICATION_PATH . '/../public/mobile/whitelables/' . $this->themeData->WHITELABLE_THEME_NAME . '/nexlinker/badge.php';
            
            if (file_exists($wlOptPath)) {
                $chapOpts   = include($wlOptPath);
            }
        }
        $opts   = $opts + $chapOpts + $defaults;

        
        $filename   = ((string) $this->chapId) . '_' . md5($this->product['id'] . implode(',', $opts)) .  '.png';
 
      
        if (BADGES_LOAD_FROM_CACHE) {
            $cached     = $this->checkCache($filename);  
            if ($cached !== false) {
                $this->output($cached); //Cached image available. Output and end it. Otherwise continue
            }
        }
    
             
        $type       = $opts['type'];
        $ds         = DIRECTORY_SEPARATOR;
        $imgPath    = APPLICATION_PATH . '/../public/badges/nexva/';
        $this->imgPath  = $imgPath;
        $this->webroot  = dirname(dirname($this->imgPath));
        
        

   
        
        $verticalAxis = 0; //This is used to align everything properly
        $canvas = $this->getBaseCanvas($opts);
        
        if ($opts['show_logo']) {
            $this->addSiteLogo($canvas, $opts, $verticalAxis);    
        } else {
            $verticalAxis = 20; //otherwise you go off the image
        }
        
        if ($type == 'large') {
            $this->addProductImage($canvas, $opts, $verticalAxis);    
        }

        if ($type != 'qr') {
            $this->addProductInfo($canvas, $opts, $verticalAxis);
            $this->addProductReviews($canvas, $opts, $verticalAxis);
        }
    
           
        if ($type != 'small') {
            $this->addQrCode($canvas, $opts, $verticalAxis);    
        }

   
        $this->cacheImage($canvas, $filename);
        $this->output($canvas);
    }
    
    private function cacheImage($canvas, $filename) {
        file_put_contents(BADGE_CACHE . $filename, (string)$canvas);
        return;
    }
    
    private function checkCache($filename = '') {
        if (file_exists(BADGE_CACHE . $filename)) {
            return new Imagick(BADGE_CACHE . $filename);
        } else {
            return false;
        }
    }
    
    private function addQrCode($canvas, $opts, &$verticalAxis) {
        
        $config         = Zend_Registry::get("config");
        
        if (isset($this->themeData->WHITELABLE_URL_WEB)) {
            $base           = $this->themeData->WHITELABLE_URL_WEB;
        } else {
            $base           = $config->nexva->application->base->url;    
        }
  

        
        
        $slugHelper     = new Nexva_View_Helper_Slug();
        $slug           = $slugHelper->slug($this->product['name']);
        $qrcodeHelper   = new Nexva_View_Helper_Qr();
        
        $qrImage    = $qrcodeHelper->qr("http://" . $base . "/app/$slug/" . $this->product['id'] . '/ref/nl', 90, 90);
        
        
/************************************    This was modified on 30/10/2012 to cater  new linker **************************
 
        if (isset($this->themeData->WHITELABLE_URL)) {
            $qrImage    = $qrcodeHelper->qr("http://" . $base . "/app/show/id/" . $this->product['id'], 90, 90);
        } else {
            $qrImage    = $qrcodeHelper->qr("http://" . $base . "/app/$slug/" . $this->product['id'] . '/ref/nl', 90, 90);
            
        }
 
***************************************************************************************************************************/

        //This is the new logi to cater new nexlinker - 30/10/2012
        if (isset($this->themeData->WHITELABLE_URL_WEB)) 
        {            
            $isPartner = false;
            
            //check if this sis a new WL
            if(isset($this->themeData->WHITELABLE_URL_WEB))
            {
                $themeModel  = new Model_ThemeMeta();
                $isPartner = $themeModel->checkIsPartner($this->themeData->WHITELABLE_URL_WEB, $this->chapId);
            }
            
            //if new WL redirect to app detection pag
            if($isPartner)
            {  
                $qrImage    = $qrcodeHelper->qr("http://" .$this->themeData->WHITELABLE_URL_WEB .'/'. $this->product['id'], 90, 90);
            }
            else //otherwise normal way of neXlinker (old WL and nexva.mobi)
            {
                $qrImage    = $qrcodeHelper->qr("http://" . $base . "/app/show/id/" . $this->product['id'], 90, 90);
            }
            
        }         
        else
        {   
            $qrImage    = $qrcodeHelper->qr("http://" . $base . "/app/$slug/" . $this->product['id'] . '/ref/nl', 90, 90);
        }
        

        
        $image      = file_get_contents($qrImage);
        $qr         = new Imagick();
        $qr->readimageblob($image);
        $canvas->compositeimage($qr, $qr->getimagecompose(), $verticalAxis + 10, 6);

        $dlText = new ImagickDraw();
        $dlText->setFontSize(9);
        $dlText->setfont($opts['main_font']);
        $dlText->setfillcolor($opts['font_color_light']);
        $dlText->annotation($verticalAxis + 18, 12, "Scan to download"); 
        $canvas->drawImage($dlText);
        
        $shortUrl   = new ImagickDraw();
        $shortUrl->setFontSize(9);
        $shortUrl->setfont($opts['main_font']);
        $shortUrl->setfillcolor($opts['font_color_light']);
        
        $mobiUrl    = "{$base}/{$this->product['id']}";
        if (strlen($mobiUrl) < 20) {
            $shortUrl->annotation($verticalAxis + 15, 93, $mobiUrl);               
        }else{
            $shortUrl->annotation($verticalAxis + -20, 93, $mobiUrl); 
        }
         $canvas->drawImage($shortUrl); 
    }
    
    private function addProductInfo($canvas, $opts, &$verticalAxis) {
        
        $appName = new ImagickDraw();
        $appName->setFontSize(14);
        $appName->setfont($opts['main_font']);
        $appName->setfontweight(900);
        $appName->setfillcolor($opts['font_color']);
        $appName->annotation($verticalAxis + 23, 18, substr($this->product['name'], 0, 32)); //$this->product['name']
        $canvas->drawImage($appName);
    
        $platforms = $this->productModel->getSupportedPlatforms($this->product['id']);
        if (is_array($platforms)) {
            $platformOffset = $verticalAxis + 21;
            foreach ($platforms as $platformObj) {
                $platformImg    = $this->webroot . '/web/images/platforms/' . $platformObj->id . '.png';
                $platform   = new Imagick($platformImg);
                $platform->thumbnailimage(17, 17);
                $canvas->compositeimage($platform, $platform->getimagecompose(), $platformOffset, 28);
                $platformOffset += 18; 
            }
        }
        
        $cpName = new ImagickDraw();
        $cpName->setFontSize(11);
        $cpName->setfont($opts['main_font']);
        $cpName->setfontweight(900);
        $cpName->setfillcolor($opts['font_color_dark']);
        $cpNameText = $this->product['user_meta']->COMPANY_NAME ? $this->product['user_meta']->COMPANY_NAME: '';
        $cpName->annotation($verticalAxis + 25, 60, substr($cpNameText, 0, 30));  
        $canvas->drawImage($cpName);
        
        $price = new ImagickDraw();
        $price->setFontSize(13);
        $price->setfont($opts['main_font']);
        $price->setfontweight(900);
        $price->setfillcolor($opts['font_color']);
        $priceValue = ($this->product['cost'] <= 0) ? 'FREE' : '$' . $this->product['cost'];
        $price->annotation($verticalAxis + 25, 78, $priceValue);  
        $canvas->drawImage($price);
    }
    
    private function addProductReviews($canvas, $opts, &$verticalAxis) { 
        $ratingModel    = new Model_Rating();
        $avgRating      = $ratingModel->getAverageRatingByProduct($this->product['id']);    
        
        $starOffset = $verticalAxis + 23; // this is used in reviews as well!
        $gap        = 14;
        $heightOffset   = 85;
        for ($i = 0; $i < floor($avgRating); $i++) {
            $star   = new Imagick($this->imgPath . 'star_full.png');
            $canvas->compositeimage($star, $star->getimagecompose(), $starOffset, $heightOffset);
            $starOffset += $gap;
        }
        if ($avgRating - (floor($avgRating)) == 0.5) {
            $star   = new Imagick($this->imgPath . 'star_half.png');
            $canvas->compositeimage($star, $star->getimagecompose(), $starOffset, $heightOffset);
            $starOffset += $gap;
            $i++; //decrement empty stars by 1 in the next loop
        }
        for ($j = $i; $j < 5; $j++) {
            $star   = new Imagick($this->imgPath . 'star_empty.png');
            $canvas->compositeimage($star, $star->getimagecompose(), $starOffset, $heightOffset);
            $starOffset += $gap;
        }

        
        $reviewModel    = new Model_Review();
        $reviewsCount   = $reviewModel->getReviewsCountByContentId($this->product['id'], false);
        
        if ($reviewsCount) {
            $reviews = new ImagickDraw();
            $reviews->setFontSize(11);
            $reviews->setfont($opts['main_font']);
            $reviews->setfillcolor($opts['font_color_light']);
            $reviews->setfontweight(900);
            $reviewsText    = '(' . $reviewsCount . ($reviewsCount > 1 ? ' reviews' : ' review') . ')';
            $reviews->annotation($starOffset, 95, $reviewsText);
            $canvas->drawImage($reviews);
        }
        
        $verticalAxis   += 210;
    }
    
    
    private function addProductImage(&$canvas, $opts, &$verticalAxis) {
        
        //Set the product image
        if (is_file($this->webroot . $this->product['thumb'])) {
            $appImage   = new Imagick($this->webroot . $this->product['thumb']);    
        } else {
            $appImage   = new Imagick($this->imgPath . '../default.jpg');
        }
        
        $appImage->thumbnailimage(60, 60, true);
        $appImage->roundcorners(3, 3);
        
        $reflection = $appImage->clone(); if (false) $reflection = new Imagick(); //for autocomplete :) 
        $reflection->flipImage();
        $reflection->thumbnailimage(60, 60, true);
        
        $gradient = new Imagick();
        //Setting the gradient width and height manually so it fades out nicely
        $gradient->newPseudoImage($reflection->getImageWidth() + 10, 35, "gradient:transparent-white");
        $reflection->compositeImage($gradient, imagick::COMPOSITE_OVER, 0, 0);

        $reflection->setImageOpacity( 0.3 );
        $reflection->roundcorners(3, 3);
        try {
            $reflection->chopimage(30, $appImage->getimagewidth(), $appImage->getimagewidth(), 30);    
        } catch (Exception $ex) {}
        
        $canvas->compositeImage($reflection, imagick::COMPOSITE_OVER, $verticalAxis + 5, 70);
        $canvas->compositeimage($appImage, $appImage->getimagecompose(), $verticalAxis + 5 , 8);
        $verticalAxis   += 60;
    }
    
    private function addSiteLogo(&$canvas, $opts, &$verticalAxis) {
        //Load the vertical company logo
        $logo   = new Imagick($opts['img_path'] . 'logo.png');
        $logo->thumbnailimage(25, 80);
        $canvas->compositeimage($logo, $logo->getimagecompose(), $verticalAxis, 10);
        $verticalAxis   += $logo->getimagewidth();   
        
        return $canvas;
    }
    
    private function getBaseCanvas($opts) {
        
        
        switch ($opts['type']) {
            case 'medium' :
                $cols   = 340; 
                $rows = 100;
                break;
                
            case 'small' :
                $cols   = 230; 
                $rows = 100;
                break;
                
            case 'qr' :
                $cols   = 130; 
                $rows = 100; 
                break;
                
            case 'large' :
            default: 
                $cols   = 400; 
                $rows   = 100;
                break;
                
        }
        
        $canvas     = new Imagick();
        $canvas->newimage($cols + 2, $rows + 2, 'none'); //for the borders to show through
        
        $innerImage = new Imagick();
        $innerImage->newimage($cols - 15, $rows, $opts['main_bg']); // just enough for the logo to jut out
        $innerImage->borderimage($opts['main_border'], 1, 1);
        
        /*
        $watermark          = new Imagick();
        $watermarkImage     = file_get_contents(APPLICATION_PATH . '/../public/mobile/whitelables/' . $this->themeData->WHITELABLE_THEME_NAME . '/badge/nexva_logo.gif');
        $watermark->readimageblob($watermarkImage);
        $watermark->setimageopacity(0.2);
        */
        
        //$innerImage     = $innerImage->textureImage($watermark); 
        $canvas->compositeimage($innerImage, $innerImage->getimagecompose(), 12, 0);
        //$canvas->compositeimage($watermark, $watermark->getimagecompose(), 120, 10);
        
        $canvas->setImageFormat("png");
        
       /* $image = new Imagick();
        $image->newImage(500, 500, new ImagickPixel('red'));
        $image->setImageFormat("png");
        $type=$image->getFormat();
        header("Content-type: $type");
        
        $texture = new Imagick();
        $texture->readimageblob($watermarkImage);
        $image = $image->textureImage($texture);
        
        echo $image;
        die();
        */
        return $canvas;    
    }
    
    private function output($canvas) {
        $canvas->setImageFormat('png');
        header("Content-Type: image/png");
        echo $canvas;
        die();
    }
}
