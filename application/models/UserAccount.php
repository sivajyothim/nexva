<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_UserAccount extends Zend_Db_Table_Abstract {

  protected $_name = 'user_accounts';
  protected $_id = 'id';

  public function credit($credit, $params = array()) {
    $total = 0;
    $total = $this->getTotalByUid($params['uid']);
    $total += $credit;
    // insert a new row
    
    if(isset($params['chap_id']))
        $chapId =  $params['chap_id'];
    else 
        $chapId =  null;
    
    if(isset($params['paid_user_id']))
    	$paidUserId =  $params['paid_user_id'];
    else
    	$paidUserId =  null;
    
    if(isset($params['trans_id'])) 
        $transActionId = $params['trans_id'];
    else 
        $transActionId = 0;
    
    if (empty($params['payment_method'])) {

      $data = array(
        'user_id' => $params['uid'],
        'date' => new Zend_Db_Expr('NOW()'),
        'description' => $params['desc'],
        'credit' => $credit,
        'debit' => '',
        'total' => $total,
        'chap_id' =>  $chapId,
        'paid_user_id' =>  $paidUserId
      );
      
    } else {
    	
      $data = array(
        'user_id' => $params['uid'],
        'date' => new Zend_Db_Expr('NOW()'),
        'description' => $params['desc'],
        'credit' => $credit,
        'debit' => '',
        'total' => $total,
        'payment_method' => $params['payment_method'],
        'chap_id' =>  $chapId,
        'paid_user_id' =>  $paidUserId,
        'trans_id' =>  $transActionId
      );
      
    }


    $this->insert($data);
  }

  public function debit($debit, $params = array()) {
    $total  = 0;
    $total  = $this->getTotalByUid($params['uid']);

    $total -= $debit;
    // insert a new row
    $data = array(
      'user_id'     => $params['uid'],
      'date'        => new Zend_Db_Expr('NOW()'),
      'description' => $params['desc'],
      'credit'      => 0,
      'debit'       => $debit,
      'total'       => $total
    );
    $this->insert($data);
  }

  public function getTotalByUid($userId) {
    $row = '';
    $row = $this->fetchRow(
                $this->select()
                ->where('user_id = ?', $userId)
                ->order('date DESC')
    );
    if (isset($row->total))
      return $row->total;
    else
      return 0;
  }

  /**
   * 
   * Saves the royalties for a given sale
   * @param unknown_type $productId
   * @param unknown_type $pgId
   * @param unknown_type $paymentMethod
   * @param Model_Payout $payout
   * @param Nexva_PromotionCode_Type_Abstract $promoCodeType
   */
  public function saveRoyalities($productId, $pgId, $paymentMethod='MOBILE', $payoutRatios = null, $promoCodeType = null, $chapId = null) {
    $product        = new Model_Product();
    $productDetail  = $product->getProductDetailsById($productId);
    
    //If a promotion code is given, apply that and modify the cost 
    //The modified cost is what the user actually paid
    $promoMessage   = '';
    if ($promoCodeType) {
        $productDetail['cost']  = $promoCodeType->applyPriceModification($productDetail['cost']);//apply new price
        $promoMessage           = ' : Promotion used - ' . $promoCodeType->getPromoCode()->code;    
    } 

    // Adding CP royality
    //get the user model
    $userModel          = new Model_User();
    if (!$payoutRatios) {
        $payoutRatios       = $userModel->getPayoutRatio($productDetail['uid']);    
    } 
    
    $paymentGatewayCost = $userModel->getPaymentGatewayCharges($pgId, $productDetail['cost']);
    
    $totalPaid          = $productDetail['cost'];
    $payoutType         = Model_User::PAYOUT_TYPE_DEFAULT;
    if ($promoCodeType) {
        //overrirde with CP cutom payout
        $cpObject   = $userModel->fetchRow('id = ' . $productDetail['uid']);
        $payoutType = $cpObject->payout_type;
    } 
    $royalties  = $this->getRoyalty($totalPaid, $paymentGatewayCost, $payoutType, $payoutRatios);
    
    $nexvaRoyalty       = $royalties['NEXVA'];
    $cpRoyalty          = $royalties['CP'];
    
    // add % neXva ADMIN account
    $params             = array();
    $params['desc']             = 'Nexva Commission (' . $payoutRatios->payout_nexva .'%) for ' . $productDetail['name'] . $promoMessage;
    $params['payment_method']   = $paymentMethod;
    $params['uid']              = 0;//the nexva account
    isset($chapId) ?  $params['chap_id'] = $chapId  : $params['chap_id'] = null;
    $this->credit($nexvaRoyalty, $params);

    
    $params                     = array();
    $params['desc']             = 'Royalties for ' . $productDetail['name'] . $promoMessage;
    $params['payment_method']   = $paymentMethod;
    $params['uid']              = $productDetail['uid'];
    isset($chapId) ?  $params['chap_id'] = $chapId  : $params['chap_id'] = null;
    $this->credit($cpRoyalty, $params);
  }
  
  
  /**
   * 
   * Saves the royalties for a given sale from API 
   * @param int $productId
   * @param int $amount
   * @param string $paymentMethod
   * @param int $chapId
   */
  
  
    public function saveRoyalitiesForApi($productId, $amount, $paymentMethod='API', $chapId, $userId = null, $transactionid = null ) 
    {

        	
        
  	$chapName     = '';
  	$payoutScheme = '';
  	$totalAmount  = '';
  	$superChapId  = '';
  	$paymentType  = 'WEB';
  	
  	// Fetch products details
  	$product        = new Model_Product();
    $productDetail  = $product->getProductDetailsById($productId);

    // Fetch chap details
    $userModel = new Model_User();
    $userDetails =  $userModel->getUserById($chapId);
    $superChapId = $userDetails->chap_id;
    
    // Fetch chap theme details
    $themeMetaModel = new Model_ThemeMeta();
    $themeValues =  $themeMetaModel->getThemeMeta($chapId);
    
    if(isset($themeValues['WHITELABLE_SITE_NAME']))
        $chapName = $themeValues['WHITELABLE_SITE_NAME'];

    $payoutScheme =  $userModel->getPayoutRatio($chapId);

    $totalAmount = $amount;
    
    // calculate royalties 
    $nexvaRoyalty      = ($totalAmount * $payoutScheme->payout_nexva)/100;
    $cpRoyalty         = ($totalAmount * $payoutScheme->payout_cp)/100;
    $chapRoyalty       = ($totalAmount * $payoutScheme->payout_chap)/100;
    $superChapRoyalty  = ($totalAmount * $payoutScheme->payout_super_chap)/100;
    
    // add % neXva ADMIN account
    $params             = array();
    $params['desc']             = 'Nexva Commission of (' . $payoutScheme->payout_nexva .'%) for Product ' . $productDetail['name'] .' from Channel Partner - '.$chapName;
    $params['payment_method']   = $paymentMethod;
    $params['payment_type']   = $paymentType;
    $params['uid']              = 0;//the nexva account
    $params['paid_user_id']     = is_null($userId)? 0  : $userId;
    isset($chapId) ?  $params['chap_id'] = $chapId  : $params['chap_id'] = null;
    $params['trans_id'] = ($transactionid) ?  $transactionid : 0 ;

    $this->credit($nexvaRoyalty, $params);

    


    // add cp royalties
    $params                     = array();
    $params['desc']             = 'Royalties of (' . $payoutScheme->payout_cp .'%) for Product '. $productDetail['name'].' from Channel Partner - '.$chapName;
    $params['payment_method']   = $paymentMethod;
    $params['payment_type']     = $paymentType;
    $params['uid']              = $productDetail['uid'];
    $params['paid_user_id']     = is_null($userId)? 0  : $userId;
    isset($chapId) ?  $params['chap_id'] = $chapId  : $params['chap_id'] = null;
    $params['trans_id'] = ($transactionid) ?  $transactionid : 0 ;
    $this->credit($cpRoyalty, $params);
    
    // add chap royalties
    $params                     = array();
    $params['desc']             = 'Commission of (' . $payoutScheme->payout_chap .'%) for Product '. $productDetail['name'];
    $params['payment_method']   = $paymentMethod;
    $params['payment_type']     = $paymentType;
    $params['uid']              = $chapId;
    $params['paid_user_id']     = is_null($userId)? 0  : $userId;
    isset($chapId) ?  $params['chap_id'] =  $superChapId  : $params['chap_id'] = null;
    $params['trans_id'] = ($transactionid) ?  $transactionid : 0 ;
    $this->credit($chapRoyalty, $params);
    
    
    if($superChapRoyalty > 0) {
    
    // add super chap royalties
    $params                     = array();
    $params['desc']             = 'Commission  of (' . $payoutScheme->payout_super_chap .'%) for  Product '. $productDetail['name'].' from Channel Partner - '.$chapName;
    $params['payment_method']   = $paymentMethod;
    $params['payment_type']     = $paymentType;
    $params['uid']              = $userDetails->chap_id;
    $params['paid_user_id']     = is_null($userId)? 0  : $userId;
    isset($chapId) ?  $params['chap_id'] = $chapId  : $params['chap_id'] = null;
    $params['trans_id'] = ($transactionid) ?  $transactionid : 0 ;
    
    }

    $this->credit($superChapRoyalty, $params);

    
    
    }
  
    
    public function saveRoyalitiesForInapp($productId, $amount, $paymentMethod='API', $chapId, $userId = null, $transactionid)
    {
    
    	$chapName     = '';
    	$payoutScheme = '';
    	$totalAmount  = '';
    	$superChapId  = '';
    	$paymentType  = 'INAPP';
    	 
    	// Fetch products details
    	$product        = new Model_Product();
    	$productDetail  = $product->getProductDetailsById($productId);
    
    	// Fetch chap details
    	$userModel = new Model_User();
    	$userDetails =  $userModel->getUserById($chapId);
    	$superChapId = $userDetails->chap_id;
    
    	// Fetch chap theme details
    	$themeMetaModel = new Model_ThemeMeta();
    	$themeValues =  $themeMetaModel->getThemeMeta($chapId);
    
    	if(isset($themeValues['WHITELABLE_SITE_NAME']))
    		$chapName = $themeValues['WHITELABLE_SITE_NAME'];
    
    	$payoutScheme =  $userModel->getPayoutRatio($chapId);
    
    	$totalAmount = $amount;
    	
    	if($chapId == 21134 ) {
    	    
    	    $nexvaRoyalty      = ($totalAmount * 12)/100;
    	    $cpRoyalty         = ($totalAmount * 40)/100;
    	    $chapRoyalty       = ($totalAmount * 48)/100;
    	   // $superChapRoyalty  = ($totalAmount * $payoutScheme->payout_super_chap)/100;
    	    
    	} else {
    	    
    	    // calculate royalties
    	    $nexvaRoyalty      = ($totalAmount * $payoutScheme->payout_nexva)/100;
    	    $cpRoyalty         = ($totalAmount * $payoutScheme->payout_cp)/100;
    	    $chapRoyalty       = ($totalAmount * $payoutScheme->payout_chap)/100;
    	    $superChapRoyalty  = ($totalAmount * $payoutScheme->payout_super_chap)/100;
    	    
    	}
    

    	
 
    	// add % neXva ADMIN account
    	$params             = array();
    	$params['desc']             = 'Nexva Commission of (' . $payoutScheme->payout_nexva .'%) for Product Inapp Payment ' . $productDetail['name'] .' from Channel Partner - '.$chapName;
    	$params['payment_method']   = $paymentMethod;
    	$params['payment_type']   = $paymentType;
    	$params['uid']              = 0;//the nexva account
    	$params['paid_user_id']     = is_null($userId)? 0  : $userId;
    	$params['trans_id']     = $transactionid;
    	isset($chapId) ?  $params['chap_id'] = $chapId  : $params['chap_id'] = null;
    
    	$this->credit($nexvaRoyalty, $params);
    
    
    
    
    	// add cp royalties
    	$params                     = array();
    	$params['desc']             = 'Royalties of (' . $payoutScheme->payout_cp .'%) for Product Inapp Payment '. $productDetail['name'].' from Channel Partner - '.$chapName;
    	$params['payment_method']   = $paymentMethod;
    	$params['payment_type']     = $paymentType;
    	$params['uid']              = $productDetail['uid'];
    	$params['paid_user_id']     = is_null($userId)? 0  : $userId;
    	$params['trans_id']     = $transactionid;
    	isset($chapId) ?  $params['chap_id'] = $chapId  : $params['chap_id'] = null;
    	$this->credit($cpRoyalty, $params);
    
    	// add chap royalties
    	$params                     = array();
    	$params['desc']             = 'Commission of (' . $payoutScheme->payout_chap .'%) for Product Inapp Payment '. $productDetail['name'];
    	$params['payment_method']   = $paymentMethod;
    	$params['payment_type']     = $paymentType;
    	$params['uid']              = $chapId;
    	$params['paid_user_id']     = is_null($userId)? 0  : $userId;
    	$params['trans_id']     = $transactionid;
    	isset($chapId) ?  $params['chap_id'] =  $superChapId  : $params['chap_id'] = null;
    	$this->credit($chapRoyalty, $params);
    
    
    	if($superChapRoyalty > 0) {
    
    		// add super chap royalties
    		$params                     = array();
    		$params['desc']             = 'Commission  of (' . $superChapRoyalty .'%) for  Product Inapp Payment '. $productDetail['name'].' from Channel Partner - '.$chapName;
    		$params['payment_method']   = $paymentMethod;
    		$params['payment_type']     = $paymentType;
    		$params['uid']              = $userDetails->chap_id;
    		$params['paid_user_id']     = is_null($userId)? 0  : $userId;
    		$params['trans_id']     = $transactionid;
    		isset($chapId) ?  $params['chap_id'] = $chapId  : $params['chap_id'] = null;
    
    	}
    
    
    	$this->credit($superChapRoyalty, $params);
    
    
    
    }
    
  
  /**
   * 
   * Returns the payouts for a CP and nexva
   * @param float $total
   * @param string $payoutType
   * @param Model_Payout $payoutRatios
   */
    private function getRoyalty($total, $transactionCosts, $payoutType, $payoutRatio) {

        if ($payoutType == Model_User::PAYOUT_TYPE_DEFAULT) {
            if ($payoutRatio->payout_cp > 70) {
                $payoutType = Model_User::PAYOUT_TYPE_CP;
            } else {
                $payoutType = Model_User::PAYOUT_TYPE_NEXVA;
            }
        }
        
        switch ($payoutType) {
            case Model_User::PAYOUT_TYPE_CP: 
                $nexvaRoyalty       = round(($total * (floatval($payoutRatio->payout_nexva / 100))), 2);
                $cpRoyalty          = $total - $nexvaRoyalty - $transactionCosts;
                break;
            
            case Model_User::PAYOUT_TYPE_NEXVA:
                $cpRoyalty       = round(($total * (floatval($payoutRatio->payout_cp / 100))), 2);
                $nexvaRoyalty    = $total - $cpRoyalty - $transactionCosts;
                break;
        }
        
        return array('CP' => $cpRoyalty, 'NEXVA' => $nexvaRoyalty);
    }
  
  public function creditCp($userId, $amount, $invoiceId, $paymentType='CP' ) {
    // Adding CP royality
    $params = array();
    $params['desc'] = 'Deposit made via PAYPAL Invoice ID - '.$invoiceId ;
    $params ['payment_method'] = 'WEB';
    $params ['payment_type'] = $paymentType;
    $params['uid'] = $userId;
    $this->creditCpInvoices($amount, $params);
    
  }
  
 public function creditCpInvoices($credit, $params = array()) {
    $total = 0;
    $total = $this->getTotalForCpByUid($params['uid']);
    $total += $credit;
    // insert a new row
      $data = array(
        'user_id' => $params['uid'],
        'date' => new Zend_Db_Expr('NOW()'),
        'description' => $params['desc'],
        'credit' => $credit,
        'debit' => '',
        'total' => $total,
        'payment_method' => $params['payment_method'],
        'payment_type' => $params['payment_type']
      );



    $this->insert($data);
  }

  protected function getTotalForCpByUid($userId) {
    $row = '';
    $row = $this->fetchRow(
                $this->select()
                ->where('user_id = ?', $userId)
                ->where('payment_type = ?', 'CP')
                ->order('date DESC')
    );
    if (isset($row->total))
      return $row->total;
    else
      return 0;
  }
  
    public function getTotalRevenueForCpPerChannel($cpid, $startDate,  $endDate) {
 	
 	             $revenuePerChannel  = $this->select()
        				->setIntegrityCheck(false)
                    	->from(array('ua' => $this->_name), array("sum" => "sum(ua.credit)"))
                       	->join(array('tm' => 'theme_meta'), 'tm.user_id = ua.chap_id', array('channel' => 'tm.meta_value'))
                        ->where("ua.date between '$startDate' and '$endDate'")
                        ->where("tm.meta_name = 'WHITELABLE_SITE_NAME'")
                    	->where("ua.user_id=?", $cpid)
                    	->group(array('ua.chap_id'))
                    	->query()
                    	->fetchAll(); 

        return $revenuePerChannel;
      

    }
    
    public function getTotalRevenueForUser($userId, $startDate,  $endDate) {
	             $revenueforUser  = $this->fetchRow($this->select()
                    	->from(array('ua' => $this->_name), array("sum" => "sum(ua.credit)"))
                        ->where("ua.date between '$startDate' and '$endDate'")
                    	->where("ua.user_id=?", $userId)); 
                    	
        if(isset($revenueforUser->sum)) 
            return $revenueforUser->sum;
         else 
             return  '0.00';


    }

    /**
     * @param $chapId
     * @param $startDate
     * @param $endDate
     * @return total revenue for particular chap
     */
    function getTotalRevenueForChap($chapId, $startDate,  $endDate)
    {
        $sql =  $this->select()
                ->from(array('sd' => 'statistics_downloads'), array("sum" => "sum(p.price)"))
                ->setIntegrityCheck(false)
                ->join(array('p' => 'products'), 'sd.product_id = p.id', array())
                ->where('sd.chap_id =?',$chapId)
                ->where('p.status =?','APPROVED')
                ->where('p.deleted =?',0)
                ->where('p.product_type =?','COMMERCIAL')
                ->where('sd.date >= ?',$startDate)
                ->where('sd.date <= ?',$endDate)
        ;
        $result = $this->fetchRow($sql);
        if(isset($result->sum))
        {
            return $result->sum;
        }
        else
        {
            return  '0.00';
        }
    }

    
    function getPaymentReceivedList($startDate, $endDate, $cpid, $paymentType = 'WEB')
    {
            $earnings = $this->select()
        				->setIntegrityCheck(false)
                    	->from(array('ua' => $this->_name))
                       	->join(array('tm' => 'theme_meta'), 'tm.user_id = ua.chap_id', array('channel' => 'tm.meta_value'))
                        ->where("ua.date between '$startDate' and '$endDate'")
                        ->where("tm.meta_name = 'WHITELABLE_SITE_NAME'")
                        ->where("ua.payment_type = ?", $paymentType)
                    	->where("ua.user_id=?", $cpid)
                    	->query()
                    	->fetchAll(); 
        return $earnings;
    }
    
    function partnerPaymentReceivedList($startDate, $endDate, $cpid, $paymentType = 'WEB')
    {
            $earnings = $this->select()
                    	->from(array('ua' => $this->_name), array('*'))
                        ->where("ua.date between '$startDate' and '$endDate'")
                        ->where("ua.payment_type = ?", $paymentType)
                    	->where("ua.user_id=?", $cpid)
                    	->query()
                    	->fetchAll(); 
        return $earnings;
    }

}

?>
