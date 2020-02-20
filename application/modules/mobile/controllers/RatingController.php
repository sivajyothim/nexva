<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Mobile_RatingController extends Nexva_Controller_Action_Mobile_MasterController {

    
        //function for add a rating for a app
        function rateAction() 
        {
               $proId  = intval(trim($this->_getParam('productId', 0)));
               $rating = intval(trim($this->_getParam('rating', 0)));
                
   
               if(!empty($proId) && !empty($rating))
               {
                   //add to db
                   $ratingModel = new Mobile_Model_Rating();
                   $rate_Id = $ratingModel->addRatingForProduct($proId,$rating);                   
                  
               }
               //if rating is successfull, redirect and show the message
               if(!empty($rate_Id)  && $rate_Id > 0)
               {     
                   $this->_helper->flashMessenger->addMessage('Thank you for rating this application.');
                   $this->_redirect('/'.$proId);
               }
               else
               {
                    $this->_redirect('/');
               }
                            
        }   
        
}