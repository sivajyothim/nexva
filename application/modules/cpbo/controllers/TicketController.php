<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/18/13
 * Time: 11:37 AM
 * To change this template use File | Settings | File Templates.
 */

class Cpbo_TicketController extends Nexva_Controller_Action_Cp_MasterController {

    function preDispatch() {
        //clearing the login check that is defined in the master controller
        if( !Zend_Auth::getInstance()->hasIdentity() ) {
            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();
            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect('/user/login');
        }
    }

    public function init()
    {
        //Set the messages if exists
        $this->view->successMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
        //Set the messages if exists
        $this->view->errorMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();

        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/cp/assets/css/ticket_styles.css');

        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $chapId = Zend_Auth::getInstance()->getIdentity()->id;
         if(!in_array($chapId, array('585474','585480'))){
            $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
        }else{
            $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages_fr.js');
        }        
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/cp/assets/js/tinymce/tinymce.min.js');
    }

    function indexAction()
    {

    }

    function createAction()
    {
        if($this->getRequest ()->isPost ())
        {
            $fileUploadAdapter = new Zend_File_Transfer_Adapter_Http();

            //Adding validation for uploaded files
            $fileUploadAdapter->addValidator('Size', false, 5242880); //5MB
            $fileUploadAdapter->addValidator('Extension',false,'jpg,jpeg,eps,gif,png,zip,doc,docx,jad,cod,sis,sisx,apk,cab,mp3,jar,ipk,wgz,prc,jpg,jpeg,gif,png,bar'); //Allowed file types - got from upload.js

            $subject = $this->_request->getParam('subject');
            $type =  $this->_request->getParam('type');
            $priority = $this->_request->getParam('priority');
            $description = $this->_request->getParam('description');
            $attachment = $fileUploadAdapter->getFileName('attach',false);
            $userId = $this->_getCpId();

            if(!empty($attachment)){
                $attachmentDate = strtotime(date("Y-m-d H:i:s"));
                $newAttachmentName = $attachmentDate.'_'.$attachment;
                $fileUploadAdapter->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'/tickets/attachments/'. $newAttachmentName));
                $fileUploadAdapter->receive('attach');
               
                if(count($fileUploadAdapter->getMessages()) > 0){
                    $this->_helper->flashMessenger->setNamespace('error')->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
                    $this->_redirect('/ticket/create');
                }
                else
                {
                    $attach = $newAttachmentName;
                }
            }
            else
            {
                $attach = NULL;
            }

            $values = array(
                "subject" => $subject,
                "type" => $type,
                "status" => 'Open',
                "priority" => $priority,
                "source" => 'CP',
                "description" => $description,
                "attachment_name" => $attach,
                "user_id" => $userId,
                "created_date"=>date('Y-m-d H:i:s')
            );

            $ticketsModel = new Cpbo_Model_Ticket();
            $lastInsertId = $ticketsModel->createTicket($values);

            //Sending notification to user
            $this->sendTicketingNotificationUser($lastInsertId);

            //Sending notification to agent
            $this->sendTicketingNotificationAgent($lastInsertId);
            /*get translater*/
            $translate = Zend_Registry::get('Zend_Translate');
            
            $this->_helper->flashMessenger->setNamespace('success')->addMessage($translate->translate("Your ticket submited successfully."));
            $this->_redirect('/ticket/list');
        }
    }

    function listAction()
    {
        $userId = $this->_getCpId();

        $ticketsModel = new Cpbo_Model_Ticket();
        $tickets = $ticketsModel->getTicketsForCp($userId);

        $paginator = Zend_Paginator::factory($tickets);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

        $this->view->tickets = $paginator;
    }

    function deleteAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $ids = $this->_request->getParam('tickets');

        $ticketsModel = new Cpbo_Model_Ticket();
        $ticketResponseModel = new Cpbo_Model_TicketResponse();
        foreach($ids as $id)
        {
            $ticketsModel->deleteTicket($id);
            $ticketResponseModel->deleteResponse($id);
        }
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');
        $this->_helper->flashMessenger->setNamespace('success')->addMessage($translate->translate("Selected ticket(s) deleted successfully."));
        $this->_redirect('/ticket/list');
    }

    function singleTicketAction()
    {
        $ticketId = $this->_request->getParam('id');
        $userId = $this->_getCpId();

        if($ticketId == null)
        {
            $this->_redirect('/ticket/list');
        }

        $ticketModel = new Cpbo_Model_Ticket();
        $this->view->ticket = $ticketModel->getTicketById($ticketId);

        $this->view->responses = $ticketModel->getResponsesForTicket($ticketId, $userId);

        //Zend_Debug::dump($this->view->responses);die();
    }

    function addResponseAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $userId = $this->_getCpId();

        $id = $this->_request->getParam('id');
        $description = $this->_request->getParam('description');

        $fileUploadAdapter = new Zend_File_Transfer_Adapter_Http();

        //Adding validation for uploaded files
        $fileUploadAdapter->addValidator('Size', false, 5242880); //5MB
        $fileUploadAdapter->addValidator('Extension',false,'jpg,jpeg,eps,gif,png,zip,doc,docx,jad,cod,sis,sisx,apk,cab,mp3,jar,ipk,wgz,prc,jpg,jpeg,gif,png,bar'); //Allowed file types - got from upload.js

        $attachment = $fileUploadAdapter->getFileName('attach',false);
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        if(!empty($attachment)){
            $attachmentDate = strtotime(date("Y-m-d H:i:s"));
            $newAttachmentName = $attachmentDate.'_'.$attachment;
            $fileUploadAdapter->addFilter('Rename', array('target' => $_SERVER['DOCUMENT_ROOT'].'/tickets/attachments/'. $newAttachmentName));
            $fileUploadAdapter->receive('attach');

            if(count($fileUploadAdapter->getMessages()) > 0){
                $this->_helper->flashMessenger->setNamespace('error')->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
                $this->_redirect("ticket/single-ticket/id/$id");
            }
            else{
                $attach = $newAttachmentName;
            }

        }
        else{
            $attach = NULL;
        }

        $values = array(
            "description" => $description,
            "attachment_name" => $attach,
            "user_id" => $userId,
            "ticket_id" => $id,
            "response_date"=>(date("Y-m-d H:i:s"))
        );

        $ticketResponseModel = new Cpbo_Model_TicketResponse();
        $lastInsertId = $ticketResponseModel->createResponse($values);

        if($lastInsertId){
            $this->_helper->flashMessenger->setNamespace('success')->addMessage($translate->translate("Your response submited successfully."));

            //Send notification to agent when user adding a response
            $this->sendResponseNotificationAgent($lastInsertId);
        }
        else{
            $this->_helper->flashMessenger->setNamespace('Error')->addMessage($translate->translate("Error on adding your response."));
        }

        //$this->_helper->flashMessenger->setNamespace('success')->addMessage('Your response submited successfully.');
        $this->_redirect("/ticket/single-ticket/id/$id");
    }

    //A notification will be sent to agent and content managers of nexva on every ticket submission
    public function sendTicketingNotificationAgent($ticketId){

        $themeMetaModel  = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_getCHAPid());
        $chapContactMail = $themeMetaModel->WHITELABLE_SITE_CONTACT_US_EMAIL;
        $agentGroupMail = Zend_Registry::get('config')->nexva->application->content_admin->contact;

        $ticketModel = new Cpbo_Model_Ticket();
        $ticketRow = $ticketModel->getTicketRow($ticketId);

        
        $userModel = new Model_User();
        $userRow = $userModel->getUserById($ticketRow->user_id);
        $userEmail = $userRow->email;
        $userFirstName = $userModel->getMetaValue($ticketRow->user_id, 'FIRST_NAME');

        $template = 'notify_add_ticket_agent.phtml';
        

        
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject($userFirstName.'('.$userEmail.') has submitted a new ticket - [Ticket ID - #'.$ticketId.']');
        $mailer ->addTo($agentGroupMail)
                //->addCc($chapContactMail)
                ->setMailVar("ticketId", $ticketId)
                ->setMailVar("ticketRow", $ticketRow)
        ;
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);
    }

    //A notification will be sent to agent and content managers of nexva on every response submission
    public function sendResponseNotificationAgent($responseId){

        $themeMetaModel  = new Model_ThemeMeta();
        $themeMetaModel->setEntityId($this->_getCHAPid());
        $chapContactMail = $themeMetaModel->WHITELABLE_SITE_CONTACT_US_EMAIL;
        $agentGroupMail = Zend_Registry::get('config')->nexva->application->content_admin->contact;

        //$ticketResponseModel = new Partner_Model_TicketResponses();
        $ticketResponseModel = new Cpbo_Model_TicketResponse();
        $responseRow = $ticketResponseModel->getResponseRow($responseId);

        $userModel = new Model_User();
        $userRow = $userModel->getUserById($responseRow->user_id);
        $userEmail = $userRow->email;
        $userFirstName = $userModel->getMetaValue($responseRow->user_id, 'FIRST_NAME');

            $template = 'notify_add_response_agent.phtml';
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject($userFirstName.'('.$userEmail.') has submitted a new response - [Ticket ID - #'.$responseRow->ticket_id.']');
        $mailer ->addTo($agentGroupMail)
                //->addCc($chapContactMail)
                ->setMailVar("responseRow", $responseRow)
        ;
        $mailer->setLayout("generic_mail_template");
        /*This is removed from 13-03-2018*/
        //$mailer->sendHTMLMail($template);
    }

    //A notification will be sent to users on every ticket submission
    public function sendTicketingNotificationUser($ticketId){

        $userId = $this->_getCpId();
        $userModel = new Model_User();
        $userRow = $userModel->getUserById($userId);
        $userEmail = $userRow->email;
        $userFirstName = $userModel->getMetaValue($userId, 'FIRST_NAME');
        $baseUrl = $this->view->serverUrl();
        $ticketUrl = $baseUrl.'/ticket/single-ticket/id/'.$ticketId;

        $session=new Zend_Session_Namespace('chap');
        $config = Zend_Registry::get('config');
        $chapIds = explode(',', $config->nexva->application->frenchchaps);

        if (in_array($session->chap->id, $chapIds)) {
            $template = 'notify_add_ticket_user_fr.phtml';
        } else {
            $template = 'notify_add_ticket_user.phtml';
        }
        
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('Ticket Received - [Ticket ID - #'.$ticketId.']');
        $mailer->addTo($userEmail)
                ->setMailVar("firstName", $userFirstName)
                ->setMailVar("ticketUrl", $ticketUrl)
                ->setMailVar("ticketId", $ticketId)
                ;
        $mailer->setLayout("generic_mail_template");
        $mailer->sendHTMLMail($template);
    }

    function downloadAttachementAction()
    {
        // disable the view ... and perhaps the layout
        /*$this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        header('Content-Type: application/apk');
        header('Content-Disposition: attachment; filename="mtn.apk"');
        readfile('tickets/attachment/Nexva - mtn.apk');*/


        // disable the view ... and perhaps the layout
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $file = $this->_request->getParam('file');

        //$zipFilename = 'tickets/attachment/noooo.apk';
        $filename = 'tickets/attachments/'.$file;
        
        /*get translater*/
        $translate = Zend_Registry::get('Zend_Translate');

        if(file_exists($filename))
        {
            //$archive = new Nexva_Util_FileFormat_Zip_PclZip($filename);
            //$archive->add('tickets/tmp');

            $fp = fopen($filename, 'rb');        //Zend_Debug::dump($fp);die();

            //header("Content-Type: application/zip");
            header("Content-Length: " . filesize($filename));
            //header('Content-Disposition: inline; filename=".$zipFilename."zip');
            header('Content-Disposition: attachment; filename="'.$file);
            header("Content-Transfer-Encoding: binary");
            fpassthru($fp);
            fclose($fp);
            die();
        }
        else
        {
            $this->_helper->flashMessenger->setNamespace('Error')->addMessage($translate->translate("Error downloading file."));
            //$url = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();     //echo $url;die();
            $this->_redirect('/ticket/list');
        }
    }


}