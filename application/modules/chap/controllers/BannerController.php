<?php

class Chap_BannerController extends Zend_Controller_Action 
{
    
    public function preDispatch() 
    {        
         if( !Zend_Auth::getInstance()->hasIdentity() ) 
         {

            $skip_action_names = array ('login', 'register', 'forgotpassword', 'resetpassword', 'claim', 'impersonate');
            
            if (! in_array ( $this->getRequest()->getActionName (), $skip_action_names)) 
            {            
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
                $this->_redirect ( '/user/login' );
            } 
        }    
    
    }
    
    
    function indexAction()
    {
        
    }
    
    //add a banner
    function createAction()
    {
        if($this->_request->isPost())
        {
            $bannerType = trim($this->_getParam('bannerType'));
            $caption = trim($this->_getParam('caption'));
            $url = trim($this->_getParam('bannerUrl'));            
            $chapId = Zend_Auth::getInstance()->getIdentity()->id;      
           
             
            $uploadedFile = new Zend_File_Transfer_Adapter_Http();                    
            
            
            $image = $uploadedFile->getFileName('bannerImage',false);
            $imageDate = strtotime(date("Y-m-d H:i:s"));       
           
            
            $newImageName = $imageDate.'_'.$image;
            $uploadedFile->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'/wl/banners/'. $newImageName));
            
            // upload the file
            if($uploadedFile->receive())
            {
                    $bannerModel = new Chap_Model_Banner();
                    $lastInsertId = $bannerModel->addBanner($chapId, $newImageName, $bannerType, $url, $caption);

                    if(!empty($lastInsertId) && $lastInsertId > 0)
                    {
                        $this->view->messagesSuccess = 'Banner uploaded successfully';
                    } 
            }
            else
            {
               $this->view->messagesError = 'Some error occured.Plese try again';
            }    
    
            
        }
        
        
    }
}
?>
