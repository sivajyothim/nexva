<?php
class Default_ReviewController extends Nexva_Controller_Action_Web_MasterController{

    function addAction(){
        $this->_helper->layout->setLayout('web/web_innerpage');
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
            $review->body       = preg_replace("/\s+/", ' ', substr($this->_getParam('body', false), 0, 1000));
            $review->rating     = max(min($this->_getParam('rating', 5), 5), 1); //getting value between 1 and 5 
            $review->reviewer   = $this->_getParam('reviewer', false);
            $review->title      = $this->_getParam('title', null);
            
            $error              = array();
            if (!$review->reviewer) {
                $error[]    = 'Reviwer name/nickname is required';
            }
            if (!$review->body || strlen($review->body) < 20) {
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
                $this->setLastRequestedUrl();
                $this->_redirect('/user/login');
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

    function saveAction(){

    }
    
    function evaIndexAction() {
        $this->_helper->layout->setLayout('web/web_innerpage');

        $page           = $this->_getParam('page', 0);
        $reviewModel    = new Model_Review();
        $searchQry      = $reviewModel->select(false)->setIntegrityCheck(false);
        $searchQry->where('reviews.type = ?', 'EVA');
        $searchQry->where('reviews.status = ?', 'APPROVED');
        $searchQry->from('reviews', array('*'));
        $searchQry->joinLeft('products', 'products.id = reviews.product_id', array('name', 'thumbnail'));
        $searchQry->order('reviews.id DESC');
        
        $paginator = Zend_Paginator::factory($searchQry);
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($page);
        
        $this->view->reviews    = $paginator;     
    }
    
    function evaAction() {
        
        
        
        $this->_helper->layout->setLayout('web/web_innerpage');
        $reviewId   = $this->_getParam('id', false);
        
        $reviewModel    = new Model_Review();
        $review         = $reviewModel->find($reviewId)->current();
        if ($review->status != 'APPROVED' || $review->type != 'EVA') {
            $this->__addNoticeMessage("Oops, we couldn't find that review");
            $this->_redirect('/');
        }
        $productModel   = new Model_Product();
        $product        = $productModel->getProductDetailsById($review->product_id, true);
        
        
        //FB open-graph stuff
        $this->view->headMeta()->appendProperty('og:title', "Eva reviews " . $product['name'] . " &mdash; " . $review['title']);
        $this->view->headMeta()->appendProperty('og:description', substr(strip_tags($this->view->Wikimarkup($review['review'])), 0, 300) . '...');
        $this->view->headMeta()->appendProperty('og:image', 'http://' . Zend_Registry::get('config')->nexva->application->base->url . $product['thumb']);
        $this->view->headMeta()->appendProperty('og:site_name', 'neXva');
        //$this->view->headMeta()->appendProperty('og:type', 'article');
        //$this->view->headMeta()->appendProperty('fb:app_id', Zend_Registry::get('config')->web->facebook->connect->appid);
        $this->view->headMeta()->appendProperty('og:url',
            'http://' . Zend_Registry::get('config')->nexva->application->base->url . "/review/eva/id/" . $reviewId);
        
        $this->view->product    = $product;
        $this->view->review     = $review;
    }

    function moreAction(){
        
        if (is_null($this->_request->id)) {
            $this->_redirect('/');
        }

        $reviewModel  =   new Model_Review();
        $reviews = $reviewModel->getReviewsByContentId($this->_request->id,50, 'USER');
        $this->view->reviews = $reviews;
        
        $productModel = new Model_Product();
        $categoryModel = new Model_Category();

        $productInfo = $productModel->getProductDetailsById($this->_request->id, true);

        if (is_null($productInfo)) {
            $this->_redirect('/'); //app not found - go back home.
        }
        //
        //@todo: find a better way to handle this condition - die() is not appropriate.
        if ($this->_request->preview) {
            $adminUrl = Zend_Registry::get('config')->nexva->application->admin->url;

            if (isset($_SERVER['HTTP_REFERER'])) {
                if ($this->_request->uid != $productInfo['uid'] && false == strstr($_SERVER['HTTP_REFERER'], $adminUrl))
                die('Invalid request: You do not have permission to preview this application');
            }else {
                die('Invalid request: You do not have permission to preview this application');
            }
        }

        $this->view->headTitle($productInfo['name'] . " for " . $productInfo['platform_name']);
        $this->view->headTitle($productInfo['user_meta']->COMPANY_NAME);
        $this->view->headTitle("neXva.com");
        $this->view->headMeta()->appendName('keywords', $productInfo['keywords'] . "," . $productInfo['name'] . "," . $productInfo['user_meta']->COMPANY_NAME .
            "," . $productInfo['platform_name'] . ", neXva, nexva.com");

        $this->view->productInfo   = $productInfo;
    }

    function listAction(){

        $reviews =  $this->getLatestReviews(10);


        $paginater = Zend_Paginator::factory($reviews);

        $paginater->setItemCountPerPage(10);
        $paginater->setCurrentPageNumber($this->_request->getParam('page', 0));
        $this->view->reviews = $paginater;

    }

    function reviewsByContent($contentId){

        $reviewModel    =   new Model_Review();
        $reviews        =   $reviewModel->select();

        $reviews ->setIntegrityCheck(false)
        ->from("reviews")
        ->where("product_id = $contentId")
        ->joinInner('users', 'reviews.user_id = users.id', 'email')
        ->join('products','reviews.product_id = products.id','name')
        ->query();

        return $reviewModel->fetchAll($reviews)->toArray();

    }

    function getLatestReviews($limit){

        $reviewModel    =   new Model_Review();
        $reviews        =   $reviewModel->select();

        $reviews ->setIntegrityCheck(false)
        ->from("reviews")
        ->joinInner('users', 'reviews.user_id = users.id', 'email')
        ->join('products','reviews.product_id = products.id','name')
        ->order('date desc')
        ->limit($limit)
        ->query();

        return $reviewModel->fetchAll($reviews)->toArray();

    }



    function deleteAction(){
        $reviewModel  =   new Model_Review();
        $reviewModel->delete('id='.$this->_request->id);
        $this->view->message = "Record is deleted";

        $this->listAction();
        $this->getRequest()->setActionName('list');
    }
}

?>
