<?php
class Default_RatingController extends Nexva_Controller_Action_Web_MasterController{
	
    function rateAction() {
        $proId  = intval($this->_getParam('productId', 0));
        $rating = intval($this->_getParam('rating', 0));
        if (!$this->_request->getParam('do', false)) {
            //I broke this functionality for non js enabled users. Also stops search engines hitting the url -jp
            if($this->getRequest()->isXmlHttpRequest()) {
                die();
            } else {
                $this->_redirect('/' . $proId);
            }
        } 
        
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
                    $this->_redirect('/' . $proId);    
                }
                
            }
            
            $ratedProducts[$proId]  = $rating;
        } else {
            $ratedProducts  = array($proId => $rating);
        }
        
        if ($proId && $rating) {
            $ratingModel = new Model_Rating();
            $data   = array(
                'product_id'    => $proId,
                'rating'        => $rating,
                'ip'            => @$_SERVER['REMOTE_ADDR']
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
                $this->_redirect('/' . $proId);                    
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