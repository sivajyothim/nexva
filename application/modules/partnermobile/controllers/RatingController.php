<?php
class Partnermobile_RatingController extends Nexva_Controller_Action_Partnermobile_MasterController
{
	
    function rateAction() {

        $chap = new Zend_Session_Namespace('partner');
        $chapId = $chap->id;

        $auth = Zend_Auth::getInstance();
        $user = $auth->getIdentity();
        $userId = $user->id;

        if(!$userId){
            $userId = $this->_userId;
        }

        $proId  = intval($this->_getParam('id', 0));
        $rating = intval($this->_getParam('rating', 0));
         
        //checking session for ratings if present
        $ratingNamespace    = new Zend_Session_Namespace('Ratings');
        if (isset($ratingNamespace->ratedProducts)) {
            $ratedProducts          = $ratingNamespace->ratedProducts;
            
            if (isset($ratedProducts[$proId])) {
                if($this->getRequest()->isXmlHttpRequest()) {
                    echo "You've already rated this application";
                    die();
                } else {
                    $flashMessenger =   $this->_helper->getHelper('FlashMessenger');
                    $flashMessenger->addMessage("You've already rated this application");
                    $this->_redirect('/app/detail/id/' . $proId);    
                }
                
            }
            
            $ratedProducts[$proId]  = $rating;
        } else {
            $ratedProducts  = array($proId => $rating);
        }

        $ratingModel = new Model_Rating();
        $hasRated = $ratingModel->userHasRated($userId,$proId);

        $statisticDownloadModel = new Model_StatisticDownload();
        $downloaded = $statisticDownloadModel->userHasDownloaded($userId,$proId);

        if(!count($downloaded)){
            $flashMessenger =   $this->_helper->getHelper('FlashMessenger');
            $flashMessenger->addMessage("You haven't downloaded this App.");
            $this->_redirect('/app/detail/id/' . $proId);
        }

        if($hasRated){
            $flashMessenger =   $this->_helper->getHelper('FlashMessenger');
            $flashMessenger->addMessage("You've already rated this application within last Month.");
            $this->_redirect('/app/detail/id/' . $proId);
        }

        if ($proId && $rating) {
            $ratingModel = new Model_Rating();
            $data   = array(
                'product_id'    => $proId,
                'rating'        => $rating,
                'ip'            => @$_SERVER['REMOTE_ADDR'],
                'chap_id'       => $chapId,
                'user_id'       => $userId
            );
            
            $ratingModel->insert($data);
            $ratingModel->clearCache($proId);
            //put it into the session so user can't rate the same thing
            $ratingNamespace->ratedProducts = $ratedProducts;
            
            if($this->getRequest()->isXmlHttpRequest()) {
                echo 'Thank you for rating this application';
                die();
            } else {
                $flashMessenger = $this->_helper->getHelper('FlashMessenger');
                $reviewLink     = "<a href='#reviews'>Why not add a review too?</a>";
                $flashMessenger->addMessage('Thank you for rating this application. ' . $reviewLink);
                $this->_redirect('/app/detail/id/' . $proId);                    
            }
            
        } else {
            if($this->getRequest()->isXmlHttpRequest()) {
                die();
            } else {
                $this->_redirect('/');
            }
        }
    }
}