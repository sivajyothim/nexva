<?php

class Partnermobile_TicketController extends Nexva_Controller_Action_Partnermobile_MasterController {

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
                $this->_redirect ( '/user/login' );
            }
        }
        
        //$this->view->headLink()->appendStylesheet('/partnermobile/css/ticketing_styles.css');
        
        //Set the messages if exists
        $this->view->successMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
        //Set the messages if exists
        $this->view->errorMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();
        
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
        
        $auth = Zend_Auth::getInstance();        
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
        
        $this->view->userId = $userId;
        
        //Load the low end view if the device is low end
        $isLowEndDevice = $this->_isLowEndDevice;
        
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
    
        if($isLowEndDevice):
            $this->_helper->viewRenderer('add-ticket-low-end');
        endif;
    }
    
    //Every user add ticket details will be inserted here
    public function insertTicketAction() 
    {
        $auth = Zend_Auth::getInstance();        
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;

        $values = Array();

        if($this->_request->isPost())
        {
            $subject = trim($this->_getParam('txtSubject'));
            $type = trim($this->_getParam('selectType'));
            $priority = trim($this->_getParam('selectPriority'));            
            $description = trim($this->_getParam('txtDescription'));  
            
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
                $this->_helper->flashMessenger->setNamespace('error')->addMessage('Please fill all the required fields');
                $this->_redirect('/ticket/add-ticket');
            }
            else{
                $values = array(
                    'subject' => $subject, 
                    'description' => $description, 
                    'type' => $type, 
                    'priority' => $priority, 
                    'source' => 'Partnermobile', 
                    'status' => 'Open', 
                    'user_id' => $userId, 
                    'created_date' => date('Y-m-d H:i:s')
                    );


                $modelTickets = new Partner_Model_Tickets();
                $lastInsertId = $modelTickets->insertTicketingDetails($values);

                if($lastInsertId){
                    $this->_helper->flashMessenger->setNamespace('success')->addMessage('Your ticket submitted successfully');
                
                    //Skip header enrichment chaps to send notification. Coz the users don't have an email with registered
                    //$arrSkipChapIds = array(25022,23045,81449,);
                    
                    //Sending notification to user but no to header enrichment chaps
                    if(!$this->_headerEnrichment)
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
        
        $auth = Zend_Auth::getInstance();        
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
        
        $values = Array();

        if($this->_request->isPost())
        {           
            $ticketId = $this->_getParam('ticketId');
            $description = trim($this->_getParam('txtDescription'));            
          
            //Check the required fields if they are not null
            if($description == ''){
                $this->_helper->flashMessenger->setNamespace('error')->addMessage('Please fill all the required fields');
                $this->_redirect('/ticket/view-single-ticket/id/'.$ticketId);
            }
            else{
                $values = array(
                    'description' => $description, 
                    //'attachment_name' => $attachment, 
                    'user_id' => $userId, 
                    'response_date' => date('Y-m-d H:i:s'),
                    'ticket_id' => $ticketId
                    );

                $modelTicketResponses = new Partner_Model_TicketResponses();
                
                $lastInsertId = $modelTicketResponses->insertResponseDetails($values);
                
                if($lastInsertId){
                    $this->_helper->flashMessenger->setNamespace('success')->addMessage('Your response submitted successfully');
                    
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
        
        $auth = Zend_Auth::getInstance();        
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
        $limitTickets = 50;
        
        $modelTickets = new Partner_Model_Tickets();
        
        $tickets = $modelTickets->getAllUserTickets($userId);
        
        $paginator = Zend_Paginator::factory($tickets);
        $paginator->setItemCountPerPage($limitTickets);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $this->view->baseUrl = $this->_baseUrl.'/ticket/view-tickets/page/';
        $this->view->paginator = $paginator;
        //$this->view->tickets = $tickets;
        $this->view->attachmentPath = '/tickets/attachments/';
        $this->view->userId = $userId;
        $this->view->pageName = 'View my Tickets';
        
        //Load the low end view if the device is low end
        $isLowEndDevice = $this->_isLowEndDevice;
        
        if($isLowEndDevice):
            $this->_helper->viewRenderer('view-tickets-low-end');
        endif;
        
    }
    
    //Get a single ticket and it's responses
    public function viewSingleTicketAction(){

        $limitResponses = 5;
        
        $auth = Zend_Auth::getInstance();        
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
        
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
        
        //print_r($responses); die();
        $this->view->userId = $userId;
        $this->view->baseUrl = $this->_baseUrl.'/ticket/view-single-ticket/id/'.$ticketId.'/page/';
        //$this->view->paginator = $paginator;
        $this->view->responses = $responses;
        $this->view->pageName = 'View Ticket';
        
        //Load the low end view if the device is low end
        $isLowEndDevice = $this->_isLowEndDevice;
        
        if($isLowEndDevice):
            $this->_helper->viewRenderer('view-single-ticket-low-end');
        endif;
    }
    
    //A notification will be sent to users on every ticket submission
    public function sendTicketingNotificationUser($ticketId){
        
        $themeMetaModel  = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_chapId);
        $chapContactMail = $themeMetaModel->WHITELABLE_SITE_CONTACT_US_EMAIL;
        $siteName = $themeMetaModel->WHITELABLE_SITE_TITLE;
        
        $auth = Zend_Auth::getInstance();        
        $userId = ($auth->getIdentity()->id) ? $auth->getIdentity()->id : $this->_userId ;
        
        $userModel = new Model_User();
        $userRow = $userModel->getUserById($userId);        
        $userEmail = $userRow->email;
        $userFirstName = $userModel->getMetaValue($userId, 'FIRST_NAME');
        $ticketUrl = $this->_baseUrl.'/ticket/view-single-ticket/id/'.$ticketId;
        
        $template = 'notify_add_ticket_user.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('Ticket Received - [Ticket ID - #'.$ticketId.']');
        $mailer->setFrom($chapContactMail, utf8_decode($siteName))
                ->addTo($userEmail)
                ->setMailVar("firstName", $userFirstName)
                ->setMailVar("email", $userEmail)
                ->setMailVar("ticketUrl", $ticketUrl)
                ->setMailVar("ticketId", $ticketId)
                ->setMailVar("siteName", $siteName)
                ;
        $mailer->setLayout("generic_mail_template");     
        $mailer->sendHTMLMail($template);
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
        $mailer->setSubject($userEmail.' has submitted a new ticket - [Ticket ID - #'.$ticketId.']');
        $mailer->addTo($chapContactMail)
                ->addTo($userEmail)
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

        //echo $userEmail.'###'. $responseRow->ticket_id.'###'; die();
        $ticketUrl =Zend_Registry::get('config')->nexva->application->pbo->url.'/ticket/view-single-ticket/id/'.$ticketId;
        
        $template = 'notify_add_response_agent.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject($userEmail.' has submitted a new response - [Ticket ID - #'.$responseRow->ticket_id.']');
        $mailer->addTo($chapContactMail)
                ->addCc($agentGroupMail)
                ->setMailVar("email", $userEmail)
                ->setMailVar("ticketId", $responseRow->ticket_id)
                ->setMailVar("ticketUrl", $ticketUrl)
                ;
        $mailer->setLayout("generic_mail_template");
           /*This is removed from 13-03-2018*/
        //$mailer->sendHTMLMail($template);
    }

}