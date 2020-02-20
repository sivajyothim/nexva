<?php

class Pbo_TicketController extends Zend_Controller_Action
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
    
        $this->_helper->layout->setLayout('pbo/pbo'); 
    }
    
    
    /** Get all tickets
     * @param - chap id $chapId
    */
    public function indexAction()
    {        
        $chapId = Zend_Auth::getInstance()->getIdentity()->id; 
        
        $ticketModel = new Pbo_Model_Tickets();
        $pages = 0;
        
        $tickets = $ticketModel->getTicketsByChap($chapId);
        
        $this->view->title = "Tickets : Manage Tickets";  
        
        $pagination = Zend_Paginator::factory($tickets);        
       
        if(count($pagination))
        {
            
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(8);
            
            $this->view->tickets = $pagination;
            
            unset($pagination);
        }
                
        //Set the messages if exists
        $this->view->ticketMessages = $this->_helper->flashMessenger->getMessages();
    }
    
    /** Shows ticket details, responses and add response
    */
    public function detailsAction()
    {
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
        
        $ticketId = trim($this->_request->id);
        
        $this->view->title = "Tickets : Ticket #$ticketId";  
        
        $ticketModel = new Pbo_Model_Tickets();        
        $ticketDetails = $ticketModel->getTicketDetailsById($ticketId);
        
        $this->view->ticketDetails = $ticketDetails;
        
        $ticketResponsesModel = new Pbo_Model_TicketResponses();        
        $responses = $ticketResponsesModel->getResponsesByTicketId($ticketId);
        
        $this->view->responses = $responses;
        
        //Add response
        if($this->_request->isPost())
        { 
            $ticketResponse = trim($this->getRequest()->getPost('txtResponse', null));
            $ticketId = trim($this->getRequest()->getPost('ticketId', null));
            
            /******************** Uploading the attachment ****************************/
            //Load file uploading adapter
            $fileUploadAdapter = new Zend_File_Transfer_Adapter_Http();
            $attachment = $fileUploadAdapter->getFileName('ticketAttachment',false);

            if(!empty($attachment))
            {
                $attachmentDate = strtotime(date("Y-m-d H:i:s"));       

                //Rename the favicon
                $newAttachmentName = $attachmentDate.'_'.$attachment;

                $fileUploadAdapter->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'tickets/attachments/'. $newAttachmentName));

                if(!$fileUploadAdapter->receive('ticketAttachment'))
                {
                    $errMsg = $fileUploadAdapter->getMessages();
                }
            }
            else 
            {
                $newAttachmentName = null;
            }
            /******************** End uploading the attachment ****************************/
           
            $responseDate = date("Y-m-d H:i:s");
            //Add ticket response
            $ticketResponsesModel->addTicketResponse($ticketResponse, $newAttachmentName, $responseDate, $chapId, $ticketId);
            
            //Update ticket action date
            $ticketModel->updateResponseDate($ticketId, $responseDate);
            
            //Send email
            $this->sendTicketingNotificationUser($ticketId);
            
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Response successfully sent.');
        
            $this->_redirect('/ticket/details/id/'.$ticketId);  
        }
        
         //Set the messages if exists
        $this->view->ticketMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
        $this->view->ticketErMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();
    }
    
   /** Change ticket properties
    *  
    */
    public function changeStatusAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        
        if($this->_request->isPost())
        { 
            $type = trim($this->getRequest()->getPost('chkType', null));
            $priority = trim($this->getRequest()->getPost('chkPriority', null));
            $status = trim($this->getRequest()->getPost('chkStatus', null));
            $ticketId = trim($this->getRequest()->getPost('ticketId', null));
            
            $ticketModel = new Pbo_Model_Tickets();
            $ticketModel->updateTicketProperties($ticketId, $type, $priority, $status);
            
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Ticket properties successfully changed.');
        
            $this->_redirect('/ticket/details/id/'.$ticketId);  
        }
    }
    
    function downloadAttachmentAction()
    {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
  
        $responseId = trim($this->_request->res_id);
        $ticketId = trim($this->getRequest()->getPost('ticketId', null));
        
        $ticketResponsesModel = new Pbo_Model_TicketResponses();        
        $details = $ticketResponsesModel->getResponseDetailsById($responseId);
      
        $file = $details->attachment_name;

        $filename = 'tickets/attachments/'.$file;

        if(file_exists($filename))
        {
            $fp = fopen($filename, 'rb');     
            
            header("Content-Length: " . filesize($filename));
            header('Content-Disposition: attachment; filename="'.$file);
            header("Content-Transfer-Encoding: binary");

            fpassthru($fp);
            fclose($fp);
            die();
        }
        else
        {
            $this->_helper->flashMessenger->setNamespace('error')->addMessage('Error downloading file.');
            $this->_redirect('/ticket/details/id/'.$ticketId);
        }
    }
    
    function sendTicketingNotificationUser($ticketId){

        $ticketModel = new Admin_Model_Ticket();
        $ticket = $ticketModel->getTicketById($ticketId);

        $userId = $ticket->user_id;

        $userModel = new Model_User();
        $userRow = $userModel->getUserById($userId);
        $userEmail = $userRow->email;
        $userFirstName = $userModel->getMetaValue($userId, 'FIRST_NAME');
        $baseUrl = $this->view->serverUrl();
        $ticketUrl = $baseUrl.'/ticket/single-ticket/id/'.$ticketId;

        $template = 'notify_add_response_user.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('New Response Added - [Ticket ID - #'.$ticketId.']');
        $mailer->addTo($userEmail)
            ->setMailVar("firstName", $userFirstName)
            ->setMailVar("ticketUrl", $ticketUrl)
            ->setMailVar("ticketId", $ticketId)
        ;
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);
    }

}