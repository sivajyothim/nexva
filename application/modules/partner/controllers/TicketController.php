<?php

class Partner_TicketController extends Nexva_Controller_Action_Partner_MasterController {

    public function init()
    {
         //Check the actions and authonticate for allowed users
         if (!Zend_Auth::getInstance()->hasIdentity()) 
         {
            $notAllowedActions = array ('add-ticket', 'view-tickets', 'view-single-ticket', 'insert-ticket', 'insert-response');
            
            if (in_array ( $this->getRequest()->getActionName(), $notAllowedActions )) 
            {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock();
                $this->_redirect ( '/info/login' );
            }
        }
        
        //Set the messages if exists
        $this->view->successMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
        //Set the messages if exists
        $this->view->errorMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();
        
        $this->view->headLink()->appendStylesheet('/partner/default/assets/css/ticketing_styles.css');
        
        $this->view->headScript()->appendFile('/partner/default/assets/js/tinymce/tinymce.min.js');
        
        parent::init();
    }

    public function indexAction() 
    {
        $this->_redirect('/ticket/view-tickets');
    }
    
    //Render the add a ticket view
    public function addTicketAction()
    {
        $this->view->pageName = 'Add a Ticket';
        $this->view->userId = $this->_userId;
        
        //Initiate 2 arrays for type and priroty
        $arrType = array('Question','Incident','Problem','Feature');
        $arrPriority = array('Low','Medium','High','Urgent');
        
        $this->view->optTypes = $arrType;
        $this->view->optPrioritys = $arrPriority;
        
        //Set the values to view if submitted by user
        $sessionTicketValues = new Zend_Session_Namespace('arrTicketValues');
        if($sessionTicketValues){
            $this->view->txtSubject = $sessionTicketValues->arrTicketValues['subject'];
            $this->view->selectType = $sessionTicketValues->arrTicketValues['type'];
            $this->view->selectPriority = $sessionTicketValues->arrTicketValues['priority'];
            $this->view->txtDescription = $sessionTicketValues->arrTicketValues['description'];
        }
    }
    
    //Every user add ticket details will be inserted here
    public function insertTicketAction() 
    {
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
        
        $userId = $this->_userId;
        
        $values = Array();

        if($this->_request->isPost())
        {
            //Load file uploading adapter
            $fileUploadAdapter = new Zend_File_Transfer_Adapter_Http(); 
          
            //Adding validation for uploaded files
            $fileUploadAdapter->addValidator('Size', false, 5242880); //2MB
            $fileUploadAdapter->addValidator('Extension',false,'jpg,jpeg,eps,gif,png,zip,doc,docx,jad,cod,sis,sisx,apk,cab,mp3,jar,ipk,wgz,prc,jpg,jpeg,gif,png,bar'); //Allowed file types - got from upload.js
            
            $subject = trim($this->_getParam('txtSubject'));
            $type = trim($this->_getParam('selectType'));
            $priority = trim($this->_getParam('selectPriority'));            
            $description = trim($this->_getParam('txtDescription'));            
            $attachment = $fileUploadAdapter->getFileName('txtAttachment',false);
            
            //Storing submitted values to session as an array * but have to find a better way *
            $sessionTicketValues = new Zend_Session_Namespace('arrTicketValues');
            
            $arrSessionTicketValues = array(
                                'subject' => $subject, 
                                'description' => $description, 
                                'type' => $type, 
                                'priority' => $priority, 
                                );
            
            $sessionTicketValues->arrTicketValues = $arrSessionTicketValues;
            

            //Check the required fields if they are not null
            if($subject == '' || $type == '' || $priority == ''|| $description == ''){
                $this->_helper->flashMessenger->setNamespace('error')->addMessage('Pleae fill all the required fields.');
                $this->_redirect('/ticket/add-ticket');
            }
            else{
                
                //If attachment is uploaded copy to server
                if(!empty($attachment)){
                    $attachmentDate = strtotime(date("Y-m-d H:i:s"));       
                    $newAttachmentName = $attachmentDate.'_'.$attachment;
                    $fileUploadAdapter->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'/tickets/attachments/'. $newAttachmentName));
                    $fileUploadAdapter->receive('txtAttachment');
                    //print_r($fileUploadAdapter->getMessages());
                    
                    //Check the errors when uploading files
                    if(count($fileUploadAdapter->getMessages()) > 0){
                        $this->_helper->flashMessenger->setNamespace('error')->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
                        $this->_redirect('/ticket/add-ticket');
                    }
                    else{
                        $attachment = $newAttachmentName;
                    }
                }
                else{
                    $attachment = NULL;
                }

                $values = array(
                    'subject' => $subject, 
                    'description' => $description, 
                    'attachment_name' => $attachment, 
                    'type' => $type, 
                    'priority' => $priority, 
                    'source' => 'Partnerweb', 
                    'status' => 'Open', 
                    'user_id' => $userId, 
                    'created_date' => date('Y-m-d H:i:s')
                    );


                $modelTickets = new Partner_Model_Tickets();
                $lastInsertId = $modelTickets->insertTicketingDetails($values);
                
                if($lastInsertId){
                    $messageSuccess = "Your ticket submitted successfully";
                    $messageSuccess =  ($translate != null) ? $translate->translate($messageSuccess) : $messageSuccess ;
                    $this->_helper->flashMessenger->setNamespace('success')->addMessage($messageSuccess);
                    
                    //Sending notification to user
                    $this->sendTicketingNotificationUser($lastInsertId);
                    
                    //Sending notification to agent
                    $this->sendTicketingNotificationAgent($lastInsertId);
                    
                    //unset the session of ticket detail
                    unset($sessionTicketValues->arrTicketValues);
                    
                    $this->_redirect('/ticket/view-tickets');
                    
                }
                else{
                    $this->_helper->flashMessenger->setNamespace('Error')->addMessage('Error on submiting your ticket.');
                }

            }

            $this->_redirect('/ticket/add-ticket');
            //$this->_helper->viewRenderer('add-ticket');
        }
    }  
    
    //Every user response details will be inserted here
    public function insertResponseAction(){
        
        $userId = $this->_userId;
        
        $values = Array();

        if($this->_request->isPost())
        {
            //Load file uploading adapter
            $fileUploadAdapter = new Zend_File_Transfer_Adapter_Http(); 

            //Adding validation for uploaded files
            $fileUploadAdapter->addValidator('Size', false, 5242880); //2MB
            $fileUploadAdapter->addValidator('Extension',false,'jpg,jpeg,eps,gif,png,zip,doc,docx,jad,cod,sis,sisx,apk,cab,mp3,jar,ipk,wgz,prc,jpg,jpeg,gif,png,bar'); //Allowed file types - got from upload.js
           
            $ticketId = $this->_getParam('ticketId');
            $description = trim($this->_getParam('txtDescription'));            
            $attachment = $fileUploadAdapter->getFileName('txtAttachment',false);
          
            //Check the required fields if they are not null
            if($description == ''){
                $this->_helper->flashMessenger->setNamespace('error')->addMessage('Pleae fill all the required fields.');
                $this->_redirect('/ticket/view-single-ticket/id/'.$ticketId);
            }
            else{
                
                //If attachment is uploaded copy to server
                if(!empty($attachment)){
                    $attachmentDate = strtotime(date("Y-m-d H:i:s"));       
                    $newAttachmentName = $attachmentDate.'_'.$attachment;
                    $fileUploadAdapter->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'/tickets/attachments/'. $newAttachmentName));
                    $fileUploadAdapter->receive('txtAttachment');
                    
                    //Check the errors when uploading files
                    if(count($fileUploadAdapter->getMessages()) > 0){
                        $this->_helper->flashMessenger->setNamespace('error')->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
                        $this->_redirect('/ticket/view-single-ticket/id/'.$ticketId);
                    }
                    else{
                         $attachment = $newAttachmentName;
                    } 
                }
                else{
                    $attachment = NULL;
                }

                $values = array(
                    'description' => $description, 
                    'attachment_name' => $attachment, 
                    'user_id' => $userId, 
                    'response_date' => date('Y-m-d H:i:s'),
                    'ticket_id' => $ticketId
                    );

                $modelTicketResponses = new Partner_Model_TicketResponses();
                
                $lastInsertId = $modelTicketResponses->insertResponseDetails($values);
                
                if($lastInsertId){
                    $this->_helper->flashMessenger->setNamespace('success')->addMessage('Your response submited successfully.');
                    
                    //Send notification to agent when user adding a response
                    $this->sendResponseNotificationAgent($lastInsertId);
                }
                else{
                    $this->_helper->flashMessenger->setNamespace('Error')->addMessage('Error on adding your response.');
                }
            }

            $this->_redirect('/ticket/view-single-ticket/id/'.$ticketId);
            //$this->_helper->viewRenderer('add-ticket');
        }
        
    }
    
    //List all ticket of the user
    public function viewTicketsAction(){
        
        $userId = $this->_userId;
        $limitTickets = 5;
        
        $modelTickets = new Partner_Model_Tickets();
        
        $tickets = $modelTickets->getAllUserTickets($userId);
        
        $paginator = Zend_Paginator::factory($tickets);
        $paginator->setItemCountPerPage($limitTickets);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
        
        $this->view->baseUrl = $this->_baseUrl.'/ticket/view-tickets/page/';
        $this->view->paginator = $paginator;
        //$this->view->tickets = $tickets;
        $this->view->attachmentPath = '/tickets/attachments/';
        $this->view->userId = $this->_userId;
        $this->view->pageName = 'View my Tickets';
        
    }
    
    //Get a single ticket and it's responses
    public function viewSingleTicketAction(){
        
        $limitResponses = 5;
        
        $userId = $this->_userId;
        
        $ticketId = $this->getRequest()->getParam('id', 1);
        
        $modelTickets = new Partner_Model_Tickets();
        
        $this->view->ticket = $modelTickets->getSingleTicket($ticketId, $userId);
        
        $responses = $modelTickets->getResponsesForTicket($ticketId, $userId);
        
        $this->view->otherData = array(
                                'ticketId' => $ticketId,
                                'attachmentPath' => '/tickets/attachments/',
                                'userId' => $userId
                                );
        
        //$paginator = Zend_Paginator::factory($responses);
        //$paginator->setItemCountPerPage($limitResponses);
        //$paginator->setCurrentPageNumber($this->_request->getParam('page', 1));
        
        $this->view->userId = $this->_userId;
        $this->view->baseUrl = $this->_baseUrl.'/ticket/view-single-ticket/id/'.$ticketId.'/page/';
        //$this->view->paginator = $paginator;
        $this->view->responses = $responses;
        $this->view->pageName = 'View Ticket';
        
    }
    
    //Download attachment action
    /*function downloadAttachementAction()
    {

        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $fileName = $this->_request->getParam('file');

        $zipFilename = '/tickets/attachments/'.$fileName;

        if(file_exists($zipFilename))
        {
            
            $archive = new Nexva_Util_FileFormat_Zip_PclZip($zipFilename);
            $archive->add('tickets/tmp');
            $fp = fopen($zipFilename, 'rb');

            header("Content-Type: application/zip");
            header("Content-Length: " . filesize($zipFilename));
            header('Content-Disposition: attachment; filename="'.$zipFilename.'".zip');
            header("Content-Transfer-Encoding: binary");
            fpassthru($fp);
            fclose($fp);
            die();
        }
        else
        {
            echo $zipFilename;
            $this->_helper->flashMessenger->setNamespace('Error')->addMessage('Error downloading file.');
            //$url = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();     //echo $url;die();
            //$this->_redirect('/ticket/list');
        }
    }*/
    
    function downloadAttachementAction()
    {
        // disable the view ... and perhaps the layout
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //$file = $this->_request->getParam('file');
        //$filename = 'tickets/attachments/'.$file;
        
        $path_file = 'tickets/attachments/';
        $filename  = $this->_request->getParam('file');
        $file = $path_file.$filename; 

        if(file_exists($file))
        {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false); // required for certain browsers 
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'. basename($file) . '";');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($file));
            readfile($file);
        }
        else
        {
            $this->_helper->flashMessenger->setNamespace('Error')->addMessage('Error downloading file.');
            //$url = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();     //echo $url;die();
            $this->_redirect('/ticket');
        }
    }
    
    //A notification will be sent to users on every ticket submission
    public function sendTicketingNotificationUser($ticketId){
        
        $themeMetaModel  = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_chapId);
        $chapContactMail = $themeMetaModel->WHITELABLE_SITE_CONTACT_US_EMAIL;
        $siteName = $themeMetaModel->WHITELABLE_SITE_TITLE;
        
        $userId = $this->_userId;
        $userModel = new Model_User();
        $userRow = $userModel->getUserById($userId);        
        $userEmail = $userRow->email;
        $userFirstName = $userModel->getMetaValue($userId, 'FIRST_NAME');
        $ticketUrl = $this->_baseUrl.'/ticket/view-single-ticket/id/'.$ticketId;
        
        $template = 'notify_add_ticket_user.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('Ticket Received - [Ticket ID - #'.$ticketId.']');
        $mailer->setFrom($chapContactMail, $siteName)
                ->addTo($userEmail)
                ->setMailVar("firstName", $userFirstName)
                ->setMailVar("ticketUrl", $ticketUrl)
                ->setMailVar("ticketId", $ticketId)
                ->setMailVar("siteName", $siteName)
                ;
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);
        //$mailer->getHTMLMail($template); die();
        //$this->_helper->viewRenderer('add-ticket');
        //$mailer->getHTMLMail($template); die();
    }
    
    //A notification will be sent to agent and content managers of nexva on every ticket submission
    public function sendTicketingNotificationAgent($ticketId){
        
        $themeMetaModel  = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_chapId);
        $chapContactMail = $themeMetaModel->WHITELABLE_SITE_CONTACT_US_EMAIL;
        $agentGroupMail = Zend_Registry::get('config')->nexva->application->content_admin->contact;
        
        $ticketsModel = new Partner_Model_Tickets();
        $ticketRow = $ticketsModel->getTicketRow($ticketId);
        
        
        $userModel = new Model_User();
        $userRow = $userModel->getUserById($ticketRow->user_id);        
        $userEmail = $userRow->email;
        $userFirstName = $userModel->getMetaValue($ticketRow->user_id, 'FIRST_NAME');

        $template = 'notify_add_ticket_agent.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject($userFirstName.'('.$userEmail.') has submitted a new ticket - [Ticket ID - #'.$ticketId.']');
        $mailer->addTo($chapContactMail)
                ->addCc($agentGroupMail)
                ->setMailVar("ticketId", $ticketId)
                ->setMailVar("ticketRow", $ticketRow)
                ;
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);
    }
    
    //A notification will be sent to agent and content managers of nexva on every response submission
    public function sendResponseNotificationAgent($responseId){
        
        $themeMetaModel  = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_chapId);
        $chapContactMail = $themeMetaModel->WHITELABLE_SITE_CONTACT_US_EMAIL;
        $agentGroupMail = Zend_Registry::get('config')->nexva->application->content_admin->contact;
        
        $ticketResponseModel = new Partner_Model_TicketResponses();
        $responseRow = $ticketResponseModel->getResponseRow($responseId);

        $userModel = new Model_User();
        $userRow = $userModel->getUserById($responseRow->user_id);        
        $userEmail = $userRow->email;
        $userFirstName = $userModel->getMetaValue($responseRow->user_id, 'FIRST_NAME');

        $template = 'notify_add_response_agent.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject($userFirstName.'('.$userEmail.') has submitted a new response - [Ticket ID - #'.$responseRow->ticket_id.']');
        $mailer->addTo($chapContactMail)
                ->addCc($agentGroupMail)
                ->setMailVar("responseRow", $responseRow)
                ;
        $mailer->setLayout("generic_mail_template");
           /*This is removed from 13-03-2018*/
        //$mailer->sendHTMLMail($template);
    }

}