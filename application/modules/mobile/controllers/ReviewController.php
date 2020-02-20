<?php
class Mobile_ReviewController extends Nexva_Controller_Action_Mobile_MasterController {
    
    function init() {
        parent::init();
        $this->view->showUtility    = true;
        $this->view->showPageTitle  = false;
        $this->view->pageTitle      = 'Apps for ' . $this->getDeviceName();
        $this->view->enableSearch   = true;
        $this->view->showToplinks   = true;
        $this->view->reviewPage     = true; //setting the selected tab
    }
    
    function indexAction() {
        $this->view->htmlTitle = 'App Reviews for ' . $this->getDeviceName();
        $page           = $this->_getParam('page', 0);
        $limit          = 20;
        
        $reviewModel    = new Model_Review();
        $reviews        = $reviewModel->getReviewsForCompatibleApps($this->getDeviceId(), $page, $limit, $this->themeMeta->WHITELABLE_APP_FILTER);
        $this->view->reviews    = $reviews;
        $this->view->page       = $page;
        $this->view->limit      = $limit;
    }
    
    function addAction() {
        
        $config     =   Zend_Registry::get('config');
        $productId  = $this->_getParam('pid', false);
        if (!is_numeric(trim($productId))) {
            $this->__addNoticeMessage("Sorry, we couldn't find this product.");
            $this->_redirect('/');
            return;
        }
        
        $this->view->productId = $productId;
        
        $reviewModel    =   new Model_Review();
        if($this->_request->isPost()){
            
            $reviewModel    =   new Model_Review();
            $userId         = null;
            if(Zend_Auth::getInstance()->hasIdentity()){
                $userId = Zend_Auth::getInstance()->getIdentity()->id;
            }
            
            if(is_null($userId)){
                $userId = $this->_request->userId;
                //Check hash. This comes from email
                $hash   =   $this->_request->hash;
                $checkhash  =   md5($userId . $config->nexva->application->salt);
                if(strcmp($hash, $checkhash)!=0){
                    $this->__addNoticeMessage('Please login to continue adding a review');
                    $this->_redirect('/user/login');
                }
            }
            
            if ($reviewModel->hasUserReviewedProduct($userId, $productId)) {
                $this->__addNoticeMessage("You've already reviewed this app");
                $this->_redirect('/' . $productId);
            }
            
            //do the validation
            $review             = new stdClass();
            $review->body       = preg_replace("/\s+/", ' ', substr($this->_getParam('body', ''), 0, 1000));
            $review->rating     = max(min($this->_getParam('rating', 5), 5), 1); //getting value between 1 and 5 
            $review->reviewer   = $this->_getParam('reviewer', false);
            $review->title      = $this->_getParam('title', null);
            
            $error              = array();
            if (!$review->reviewer) {
                $error[]    = 'Reviwer name/nickname is required';
            }
            if (!$review->body || strlen($review->body) < 10) {
                $error[]    = 'Review body should have at least 20 characters';
            }
            
            if (count($error)) {
                $this->view->errors = $error;
            } else {
                $data   = array();
                $data['user_id']        = $userId;
                $data['product_id']     = $productId;
                $data['name']           = $review->reviewer;
                $data['review']         = $review->body;
                $data['title']          = trim($review->title);
                $data['type']           = 'USER';
                $data['rating']         = $review->rating;
                $data['status']         = 'NOT_APPROVED';
                
                
                $reviewModel    = new Model_Review();
                $reviewModel->insert($data);
                $this->__addMessage('Thank you for your review. Reviews are moderated and may take some time to appear on site.');
                
                //add the rating into the system as well
                $rating     = $review->rating;
                $ratingNamespace    = new Zend_Session_Namespace('Ratings');
                if (isset($ratingNamespace->ratedProducts)) {
                    $ratedProducts          = $ratingNamespace->ratedProducts;
                    if (isset($ratedProducts[$productId])) {
                        $this->_redirect('/' . $productId);
                        return;
                    }
                    $ratedProducts[$productId]  = $rating;
                } else {
                    $ratedProducts  = array($productId => $rating);
                }
                $request     = new Zend_Controller_Request_Http();
                $ratingModel = new Model_Rating();
                $data   = array(
                    'product_id'    => $productId,
                    'rating'        => $rating,
                    'ip'            => $request->getClientIp(true)
                );
                $ratingModel->insert($data);
                $ratingModel->clearCache($productId);
                $ratingNamespace->ratedProducts = $ratedProducts;
                
                $this->_redirect('/' . $productId);
                return;
            }
        } else {
            $userId = null;
            if(Zend_Auth::getInstance()->hasIdentity()){
                $userId = Zend_Auth::getInstance()->getIdentity()->id;
            }
            if(is_null($userId)){
                $this->__addNoticeMessage('Please login to continue adding a review');
                $this->_redirect('/user/login/next/1/url/' . base64_encode('/review/add/pid/' . $productId));
                return;
            }
            if ($reviewModel->hasUserReviewedProduct($userId, $productId)) {
                $this->__addNoticeMessage("You've already reviewed this app");
                $this->_redirect('/' . $productId);
                return;
            }
            
            $userMeta   = new Model_UserMeta();
            $userMeta->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);
            $name       = $userMeta->FIRST_NAME . ' ' . $userMeta->LAST_NAME;
            
            $defaults   = array('body' => '', 'rating' => 5, 'reviewer' => $name, 'title' => '');
            $review     = new stdClass();
            foreach ($defaults as $field => $value) {
                $review->$field     = $value;
            }
        }
        $this->view->review = $review;   
    }
    
    
    function evaAction() {
        $reviewId   = $this->_getParam('id', false);
        
        $reviewModel    = new Model_Review();
        $review         = $reviewModel->find($reviewId)->current();
        if ($review->status != 'APPROVED' || $review->type != 'EVA') {
            $this->__addNoticeMessage("Oops, we couldn't find that review");
            $this->_redirect('/');
        }
        $productModel   = new Model_Product();
        $product        = $productModel->getProductDetailsById($review->product_id, true);
        
        $this->view->product    = $product;
        $this->view->review     = $review;
    }
    
    function allAction() {
        $productId  = $this->_getParam('pid', false);
        if (!$productId) {
            $this->__addNoticeMessage("Sorry, we couldn't find this app");
        }
        
        $reviewModel    = new Model_Review();
        $reviews        = $reviewModel->getReviewsByContentId($productId, 20);
        $this->view->productId  = $productId;
        $this->view->reviews    = $reviews;
    }
}