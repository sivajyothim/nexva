<?php
class Model_PromotionCode extends Nexva_Db_Model_MasterModel {
    const PROMOCODE_TYPE_DEBIT      = 'DEBIT';
    const PROMOCODE_TYPE_CREDIT     = 'CREDIT';
    const PROMOCODE_TYPE_STANDARD   = 'STANDARD';
    
    protected  $_id     = 'id';
    protected  $_name   = 'promocodes';
    
    private $validChars     = null;
    private $maxCodeSize    = null;

    public function __construct() {
        parent::__construct();
        $this->initCodeBoundaries();
    }
    
    public function setMaxCodeSize($max) {
        $this->maxCodeSize  = (int) $max;
    }
    
    /**
     * Initializes promotion code boundaries
     * Valid characters and maximum code size 
     */
    private function initCodeBoundaries() {
        $this->validChars     = Zend_Registry::get("config")->promo->code->validChars;
        $this->maxCodeSize    = Zend_Registry::get("config")->promo->code->maxsize;
    }
    
    public function savePromotionCodes($pattern, $numCodes, $promotionData, $productId) {
        
        $promotionData['created']       = date('Y-m-d H:i:s');
        $promotionData['valid_from']    = date('Y-m-d H:i:s', strtotime($promotionData['valid_from']));
        $promotionData['valid_to']      = date('Y-m-d H:i:s', strtotime($promotionData['valid_to']));
        $promotionData['promo_campaign_id']   = (trim($promotionData['promo_campaign_id']) == '') ? null : $promotionData['promo_campaign_id'];
        
        try {
            
            $pattern                = strtoupper(preg_replace("/[^a-zA-Z0-9[\]]/", '', $pattern));
            $promotionData['id']    = null;
            $codes  = $this->generateCodes($pattern, $numCodes);
            
            $db     = Zend_Registry::get('db');
            $db->beginTransaction();
            
            if ($codes === false) {
                throw new Exception("Too many collisions when creating random codes. Please choose a better pattern or try again");
            }
            
            $productPromoModel  = new Model_ProductPromotionCode();
            foreach ($codes as $code) {
                $promotionData['code']  = $code;
                $id = $this->insert($promotionData);
            
                $data   = array(
                    'product_id'    => $productId,
                    'promo_id'      => $id
                );
                $productPromoModel->insert($data);
            }
            $db->commit();
            return true;
        } catch (Exception $ex) {
            $db->rollBack();
            return $ex->getMessage();
        }
    }
    
    /**
     * 
     * Generates codes based on the pattern. The tag [CODE] is used to indicate the code 
     * 
     * @param $code
     */
    private function generateCodes($code, $numCodes, $codes = array(), $nestingLevel = 1) {
        if ($nestingLevel > 80) {
            return false;
        }
        
        $code           = trim($code);
        $prepend        = '';
        
        $hasPattern = stripos($code, '[code]');
        
        //If we only want 1 code and it's a simple code, return as is
        if ($numCodes == 1 && $hasPattern === false) {
            return array($code);
        } 
        
        if ($numCodes > 1 && $hasPattern === false) {
            $code   .= '[code]';
        }
        
        $staticCodeLength   = strlen(str_ireplace('[code]', '', $code));
        while (count($codes) < $numCodes) {
            $tempCode   = $this->getRandomCode($staticCodeLength);
            if (!isset($codes[$tempCode])) {
                $cleanCode          = str_ireplace('[code]', $tempCode, $code);
                $codes[$cleanCode]  = $cleanCode;
            }
        }
        //find out which codes exist now
        $select     = $this->select(false)->from('promocodes', 'code')->where('code IN (?)', $codes); 
        $existingCodes  = $select->query()->fetchAll();
        foreach ($existingCodes as $existingCode) {
            unset($codes[$existingCode->code]);
        }
        
        if (count($codes) >= $numCodes) {
            return $codes;
        } else {
            return $this->generateCodes($code, $numCodes - count($codes), $codes, ++$nestingLevel);
        }
        
    }
    
    /**
     * Returns a random chode of a defined length that has only valid chars
     * @param $codeLength
     */
    private function getRandomCode($codeLength) {
        $validChars     = $this->validChars;
        $maxCodeSize    = $this->maxCodeSize;
        $code           = '';
        $codeSize       = $maxCodeSize - $codeLength;
        
        while(strlen($code) < $codeSize) {
            $code   .= substr($validChars, rand(0, strlen($validChars)), 1);
        }
        return strtoupper($code);
    }
    
    /**
     * 
     * Returns the details for a single promotion code
     * @param int $id
     * @param boolean $searchCode if set, the system will search by promotion code and not ID. Default false
     * @param boolean $detailed Returns all the details about the promotion code if true. Default true
     */
    public function getPromotionCode($id = null, $searchCode = false, $detailed = true) {
        if (!$id) { 
            return null;
        }
        $id = trim($id);
        if ($searchCode) { 
            $promotion  = $this->fetchRow("code = '{$id}'");
        } else {
            $promotion  = $this->fetchRow('id = ' . $id);
        }
        
        if (!$promotion) {
            return null;
        }
        
        $promotion  = $promotion->toArray();
        
        if (!$detailed) {
            return $promotion;
        }
        
        $products       = $this->select(false)->setIntegrityCheck(false)
                        ->from('product_promocodes', array())
                        ->joinLeft('products', 'products.id = product_promocodes.product_id', array('*'))
                        ->where('promo_id = ?', $promotion['id'])->query()->fetchAll();
        $promotion['products']  = $products ? $products : array();
        
        //company name will always be present for users of type CP and RESELLER
        $user           = $this->select(false)->setIntegrityCheck(false)
                        ->from('promocodes', array())
                        ->joinLeft('user_meta', 'promocodes.user_id = user_meta.user_id AND user_meta.meta_name = "COMPANY_NAME"', array('user_meta.meta_value AS name', 'user_id'))
                        ->where('promocodes.id = ?', $promotion['id'])
                        ->query()->fetch();
        $promotion['user']      = $user;
        return $promotion;
    }
    
    
    /**
     * 
     * Retrieve codes by campaign ID. Send null to find codes that are not assigned to campaigns
     * @param $id campaign_id
     */
    public function getCodesByCampaign($id = null) {
        if ($id) {
            $select = $this->select()->where('promo_campaign_id = ?', $id);
        } else {
            $select = $this->select()->where('promo_campaign_id IS NULL');
        }
        $allCodes       = $select->query()->fetchAll();
        
        if (empty($allCodes)) {
            return array();
        }
        
        $codes  = $this->gePromotionCodeDetails($allCodes);
        return $codes;
    }
    
    /**
     * 
     * Retrieve codes by campaign ID. Send null to find codes that are not assigned to campaigns
     * @param $id campaign_id
     */
    public function getCodesByUser($id = null) {
        $select = $this->select()->where('user_id = ?', $id);
        $allCodes       = $select->query()->fetchAll();
        
        if (empty($allCodes)) {
            return array();
        }
        $codes  = $this->gePromotionCodeDetails($allCodes);
        
        return $codes;
    }
    

    /**
     * 
     * Enter description here ...
     * @param $allCodes Collection of rows, each in a format by the fetch mode
     */
    private function gePromotionCodeDetails($allCodes) {
        $codes          = array();
        $codeIds        = array();
        foreach ($allCodes as $code) {
            $codeIds[]  = $code->id;
            $codes[$code->id]   = (array) $code;    
        }

        $products       = $this->select(false)->setIntegrityCheck(false)
                        ->from('product_promocodes', array('promo_id'))
                        ->joinLeft('products', 'products.id = product_promocodes.product_id', array('*'))
                        ->where('promo_id IN (?)', $codeIds)->query()->fetchAll();
        
        foreach ($products as $product) {
            if (!isset($codes[$product->promo_id]['products'])) {
                $codes[$product->promo_id]['products']  = array();
            } 
            $codes[$product->promo_id]['products'][] = $product;
        }
        
        $userResults    = $this->select(false)->setIntegrityCheck(false)
                        ->from('promocodes', array('id AS promo_id'))
                        ->joinLeft('user_meta', 'promocodes.user_id = user_meta.user_id AND user_meta.meta_name = "COMPANY_NAME"', array('user_meta.meta_value AS name', 'user_id'))
                        ->where('promocodes.id IN (?)', $codeIds)
                        ->query()->fetchAll();
                        
        //make it associate for quick lookup
        foreach ($userResults as $user) {
            $codes[$user->promo_id]['user'] = $user;
        }
        
        return $codes;         
    }
    
    public function getPromotionCodeListForCampaign($cId = null) {
        $query  = $this->select(false)->from('promocodes', array('id', 'code'));
        if ($cId === null) {
            $query->where('promo_campaign_id IS NULL');
        } else { 
            $query->where('promo_campaign_id = ?', $cId);    
        }
          
        $results    = $this->fetchAll($query);
        
        if (!$results) {return false;}
        
        $codes      = array();
        foreach ($results as $result) {
            $codes[$result->id] = $result->code;
        }
        return $codes;
    }
 
}