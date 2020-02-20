<?php

define('BADGE_CACHE', APPLICATION_PATH . '/../public/badges/cache/promotion/');
define('BADGES_LOAD_FROM_CACHE', true);


class Nexva_Badge_Promotion {
    private $promotion  = null;
    private $product    = null;
    private $chapData   = null;    
    
    public function __construct($code = null) {

        $promotionModel     = new Model_PromotionCode();
        $promoCode          = $promotionModel->getPromotionCode($code, true);
        $this->promotion    = (object) $promoCode;
        $this->product      = isset($promoCode['products'][0]) ? $promoCode['products'][0] : null;
        
        if ($this->promotion->chap_id) {
            $themeMeta      = new Model_ThemeMeta();
            $themeMeta->setEntityId($this->promotion->chap_id);
            $this->chapData = $themeMeta->getAll();
        }
    }
    
    public function displayBadge($opts = array()) {
        
        if (!$this->promotion || !$this->product) {
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
            'main_font'             => APPLICATION_PATH . '/../public/badges/tahoma.ttf',
        );
        
        $opts   = $opts + $defaults;
        $hRule  = -15;
        $filename       = md5($this->promotion->id . $this->product->id . implode(',', $opts)) . '.png';
        
        if (BADGES_LOAD_FROM_CACHE) {
            $cached     = $this->checkCache($filename);
            if ($cached !== false) {
                $this->output($cached); //Cached image available. Output and end it. Otherwise continue
            }
        }
        
        $canvas = $this->getBaseCanvas($opts);
        $hRule  = $this->addPromoText($canvas, $opts, $hRule);
        $this->addQrCode($canvas, $opts, $hRule);
        
        
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
    
    private function getBaseCanvas($opts) {
        
        $innerImage = new Imagick();
        $innerImage->newimage(320, 130, $opts['main_bg']); // just enough for the logo to jut out
        $innerImage->borderimage($opts['main_border'], 1, 1);
        $innerImage->setImageFormat("png");
        
        return $innerImage;  
    }
    
    private function addPromoText($canvas, $opts, $hRule) {
        $verticalBase   = 0;
        
        $appName = new ImagickDraw();
        $appName->setFontSize(14);
        $appName->setfont($opts['main_font']);
        $appName->setfillcolor($opts['font_color']);
        $appName->annotation($hRule + 23, $verticalBase + 18, substr($this->product->name, 0, 25)); 
        $canvas->drawImage($appName);
    
        $promoText  = new ImagickDraw();
        $promoText->setFontSize(11);
        $promoText->setfont($opts['main_font']);
        $promoText->setfillcolor($opts['font_color_dark']);
        $promoText->annotation($hRule + 25, $verticalBase + 40, wordwrap($this->promotion->description, 40, "\r\n"));  
        $canvas->drawImage($promoText);
        
        $codeLabel   = new ImagickDraw();
        $codeLabel->setFontSize(12);
        $codeLabel->setfont($opts['main_font']);
        $codeLabel->setfillcolor($opts['font_color_dark']);
        $codeLabel->annotation($hRule + 25, $verticalBase + 95, 'Discount Code : ');  
        $canvas->drawImage($codeLabel);
        
        $codeText   = new ImagickDraw();
        $codeText->setFontSize(14);
        $codeText->setfont($opts['main_font']);
        $codeText->setfillcolor($opts['font_color_dark']);
        $codeText->annotation($hRule + 116, $verticalBase + 95, $this->promotion->code);  
        $canvas->drawImage($codeText);
        
        $config         = Zend_Registry::get("config");
        $base           = ($this->chapData) ? $this->chapData->WHITELABLE_URL : $config->nexva->application->mobile->url;
        
        $shortUrl   = new ImagickDraw();
        $shortUrl->setFontSize(12);
        $shortUrl->setfont($opts['main_font']);
        $shortUrl->setfillcolor($opts['font_color_light']);
        
        $url    = "{$base}/{$this->product->id}";
        $shortUrl->annotation($hRule + 25, $verticalBase + 115, $url); 
        $canvas->drawImage($shortUrl);
        
        $hRule  += 235;
        
        return $hRule;
    }
    
    private function addQrCode($canvas, $opts, $hRule) {
        $config         = Zend_Registry::get("config");
        $base           = ($this->chapData) ? $this->chapData->WHITELABLE_URL : $config->nexva->application->mobile->url;
        
        $qrcodeHelper   = new Nexva_View_Helper_Qr();
        $promoUrl       = 'http://' . $base . '/app/apply-code/id/' . $this->product->id . '/code/' . $this->promotion->code; 
        $qrImage        = $qrcodeHelper->qr($promoUrl, 90, 90);
        $image          = file_get_contents($qrImage);
        $qr             = new Imagick();
        $qr->readimageblob($image);
        $canvas->compositeimage($qr, $qr->getimagecompose(), $hRule + 3, 25);

        $dlText = new ImagickDraw();
        $dlText->setFontSize(9);
        $dlText->setfont($opts['main_font']);
        $dlText->setfillcolor($opts['font_color_light']);
        $dlText->annotation($hRule, 30, "Scan this to Download"); 
        $canvas->drawImage($dlText);
                
        $hRule  += 100;
        return $hRule;
    }
    
    private function output($canvas) {
        $canvas->setImageFormat('png');
        header("Content-Type: image/png");
        echo $canvas;
        die();
    }
}