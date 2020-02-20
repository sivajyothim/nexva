<?php
class Admin_ReviewController extends Nexva_Controller_Action_Admin_MasterController {

    function predispatch() {
        if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
        }
    }

    function indexAction(){
        
        $type           = $this->_getParam('type', 'USER');
        $page           = $this->_getParam('page', 0);
        $reviewModel    = new Model_Review();
        $searchQry      = $reviewModel->select(false)->setIntegrityCheck(false);
        $searchQry->where('reviews.type = ?', strtoupper($type));
        $searchQry->from('reviews', array('*'));
        $searchQry->joinLeft('products', 'products.id = reviews.product_id', array('name'));
        $searchQry->joinLeft('users', 'users.id = reviews.user_id', array('email'));
        
        
        $paginator = Zend_Paginator::factory($searchQry);
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($page);
        
        $this->view->type       = $type;
        $this->view->reviews    = $paginator; 
    }

    function saveAction() {
        $pid    = $this->_getParam('pid', false);
        $id     = $this->_getParam('id', false);
        if (!$pid && !$id) {
            $this->__addErrorMessage('Product ID not found');
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'review/list');
            return;
        }   
        
        if($this->_request->isPost()){
            //error checking done on client side only since this is admin
            $data               = array();
            $data['title']      = $this->_getParam('title', false);
            $data['review']     = $this->_getParam('body', false);
            $data['rating']     = $this->_getParam('rating', false);
            $data['user_id']    = Zend_Auth::getInstance()->getIdentity()->id;
            $data['product_id'] = $pid;
            $data['type']       = 'EVA';
            $data['name']       = 'Eva';
            $data['status']     = $this->_getParam('status', false);
            $reviewId     = $this->_getParam('review_id', false);
            $reviewModel    = new Model_Review();
            if ($reviewId) {
                $reviewModel->update($data, 'id = ' . $reviewId);
            } else {
                $reviewModel->insert($data);
            }
            $this->__addErrorMessage('Review Saved');
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'review/index/type/eva');
            return;
        } else {
            $review     = new stdClass();
            $review->title  = '';
            $review->review = '';
            $review->rating = 5;
            $review->id     = 0;
            $review->status     = 'NOT_APPROVED';
        }
        
        if ($id) {
            $reviewModel    = new Model_Review();
            $review         = $reviewModel->find($id)->current();
            $pid            = $review->product_id;
        }
                
        $productModel   = new Model_Product();
        $product        = $productModel->getProductDetailsById($pid, true);
        
        
        $this->view->review     = $review;
        $this->view->product    = $product;
    }
    


    function listAction(){

       $reviews =  $this->getLatestReviews();
       
       $unApprovedlistReview = new Model_Review();
       $this->view->unApprovedlistReview = $unApprovedlistReview->unApprovedlistReview();
       $reviewsUnApproved = $this->getLatestApprovedReviews();

       $paginater = Zend_Paginator::factory($reviews);

       $paginater->setItemCountPerPage(20);
       $paginater->setCurrentPageNumber($this->_request->getParam('page', 0));
       $this->view->reviews = $paginater;
        
       $paginaterNonApproved = Zend_Paginator::factory($reviewsUnApproved);

       $paginaterNonApproved->setItemCountPerPage(20);
       $paginaterNonApproved->setCurrentPageNumber($this->_request->getParam('page', 0));
       $this->view->reviewsNonApproved = $paginaterNonApproved;
       
       $this->view->page = $this->_request->getParam('page', 0);
       
       $this->view->status = $this->_request->getParam('status');
       
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

    function getLatestReviews(){

        $reviewModel    =   new Model_Review();
        $reviews        =   $reviewModel->select();

        $reviews ->setIntegrityCheck(false)
                 ->from("reviews")
                 ->where('reviews.status = ?', 'NOT_APPROVED')
                 ->joinInner('users', 'reviews.user_id = users.id', 'email')
                 ->join('products','reviews.product_id = products.id','name')
                 ->order('status asc') 
                 ->order('date desc') 
                 ->query();

      return $reviewModel->fetchAll($reviews)->toArray();



    }
    
     function getLatestApprovedReviews(){

        $reviewModel    =   new Model_Review();
        $reviews        =   $reviewModel->select();

        $reviews ->setIntegrityCheck(false)
                 ->from("reviews")
                 ->where('reviews.status = ?', 'APPROVED')
                 ->joinInner('users', 'reviews.user_id = users.id', 'email')
                 ->join('products','reviews.product_id = products.id','name')
                 ->order('status asc') 
                 ->order('date desc') 
                 
                 ->query();

      return $reviewModel->fetchAll($reviews)->toArray();



    }
    
    function deleteAction(){
        $reviewModel  =   new Model_Review();
        $reviewModel->delete('id='.$this->_request->id);
        $this->view->message = "Record is deleted";
        $page = $this->_request->getParam('page', 0);
        
        $this->listAction();
        $this->getRequest()->setActionName('list');
        
        if($this->_request->status == 'non-approved')
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'review/list/page/'.$page.'/status/non-approved');
        else
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'review/list/page/'.$page.'/status/approved');
        
    }
    
    
   function updateAction(){
    
        $reviewModel  =   new Model_Review();
        $reviewModel->update(array( 'status' => 'approved'), array('id = ?' => $this->_request->id));
        $page   = $this->_request->getParam('page', 0);

        //send mail to CP with review details
        $data   = $reviewModel->getReviwedProductAndUser($this->_request->id);
        $review = $reviewModel->fetchRow('id = ' . $this->_request->id)->toArray();
        $mailer     = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('A Review has been added to your application');
        $mailer->addTo($data['email'], $data['username'])
            ->setLayout("generic_mail_template")     
            ->setMailVar("productName", $data['product_name'])
            ->setMailVar("productId", $data['product_id'])
            ->setMailVar("review", $review)
            ->setMailVar("baseurl", Zend_Registry::get('config')->nexva->application->base->url)
            ->setMailVar("username", $data['username']);

        //don't want to send this out unless it's live
        if (APPLICATION_ENV == 'production') {
            $mailer->sendHTMLMail('review_approved.phtml'); //change this. mail templates are in /views/scripts/mail-templates
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'review/list/page/'.$page.'/status/non-approved');    
        } else {
            echo $mailer->getHTMLMail('review_approved.phtml');
            die();
        }
    }
    
    function rejectAction(){
    
        $reviewModel  =   new Model_Review();
        $page = $this->_request->getParam('page', 0);
        $reviewModel->update(array( 'status' => 'NOT_APPROVED'), array('id = ?' => $this->_request->id));
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'review/list/page/'.$page.'/status/approved');

    }
    
    
    
    
    
}

?>
