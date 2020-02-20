<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 11/26/13
 * Time: 12:00 PM
 * To change this template use File | Settings | File Templates.
 */

class Admin_TicketController extends Nexva_Controller_Action_Admin_MasterController
{
    function preDispatch() {
        /*if (! Zend_Auth::getInstance ()->hasIdentity ()) {

            if ($this->_request->getActionName () != "login") {
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
            }
            if ($this->_request->getActionName () != "login")
                $this->_redirect ( '/user/login' );
        }*/
        parent::preDispatch();
    }

    public function init()
    {
        //Set the messages if exists
        $this->view->successMessages = $this->_helper->flashMessenger->setNamespace('success')->getMessages();
        //Set the messages if exists
        $this->view->errorMessages = $this->_helper->flashMessenger->setNamespace('error')->getMessages();

        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'admin/assets/css/ticket_styles.css');
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'admin/assets/css/tickets.css');

        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');
        $this->view->headScript()->appendFile('/cp/assets/js/tinymce/tinymce.min.js');
    }

    function indexAction()
    {
        $chapId = $this->_request->getParam('chap_id');
        $priority = $this->_request->getParam('priority');
        $status = $this->_request->getParam('status');
        $source = $this->_request->getParam('source');

        if(empty($chapId)){
            $chapId = '4348';   //make default chap for neXva
        }

        $this->view->chapId = $chapId;
        $this->view->priority = $priority;
        $this->view->status = $status;
        $this->view->source = $source;

        $ticketModel = new Admin_Model_Ticket();
        $pages = 0;

        $this->view->chaps = $ticketModel->getActiveChaps();
        //Zend_Debug::dump($this->view->chaps);die();
        $tickets = $ticketModel->getTicketsByChap($chapId,$priority,$status,$source);

        $pagination = Zend_Paginator::factory($tickets);

        if(count($pagination))
        {
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);

            $this->view->tickets = $pagination;

            unset($pagination);
        }

        //open ticket count for dashboard
        $this->view->openTickets = $ticketModel->getOpenTicketCount();

        //overdue ticket count for dashboard
        $openTickets = $ticketModel->getAllOpenTickets();
        $this->view->overdueCount = $this->overdueTicketCount($openTickets);

        //closed today count for dashboard
        $this->view->closedToday = $ticketModel->getClosedTodayTickets();

        //overdue today count for dashboard
        $this->view->overdueToday = $this->getOverdueTodayTickets($openTickets);

    }

    function singleTicketAction()
    {
        $ticketId = $this->_request->getParam('id');

        if($ticketId == null)
        {
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'ticket');
        }

        $ticketModel = new Admin_Model_Ticket();
        $this->view->ticket = $ticketModel->getTicketById($ticketId);

        //$responses = $ticketModel->getResponsesForTicket($ticketId);
        /*$pagination = Zend_Paginator::factory($responses);
        if(count($pagination)){
            $pagination->setCurrentPageNumber($this->_request->getParam('page', 1));
            $pagination->setItemCountPerPage(10);

            $this->view->responses = $pagination;

            unset($pagination);
        }*/

        $this->view->responses = $ticketModel->getResponsesForTicket($ticketId);
    }

    function deleteAction()
    {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $ticketIds = $this->_request->getParam('tickets');
        $chap = $this->_request->getParam('chap');
        $priority = $this->_request->getParam('priority');
        $status = $this->_request->getParam('status');
        $source = $this->_request->getParam('source');

        $ticketIds = array_filter($ticketIds);

        if(empty($ticketIds)){
            //$this->_helper->flashMessenger->setNamespace('error')->addMessage('Select at least one Ticket.');
            //$this->_helper->flashMessenger->setNamespace('error')->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'ticket/index/chap_id/'.$chap);
        }

        $ticketsModel = new Admin_Model_Ticket();
        $ticketResponseModel = new Admin_Model_TicketResponse();

        foreach($ticketIds as $ticketId)
        {
            $ticketsModel->deleteTicket($ticketId);
            $ticketResponseModel->deleteResponse($ticketId);
        }

        $this->_helper->flashMessenger->setNamespace('success')->addMessage('Selected ticket(s) deleted successfully.');
        $url = '/ticket/index';
        if($chap){ $url .= '/chap_id/'.$chap;}
        if($priority){ $url .= '/priority/'.$priority;}
        if($status){ $url .= '/status/'.$status;}
        if($source){ $url .= '/source/'.$source;}
        $this->_redirect($url);
    }

    function closeAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $ticketIds = $this->_request->getParam('tickets');
        $chap = $this->_request->getParam('chap');

        if(empty($ticketIds)){
            //$this->_helper->flashMessenger->setNamespace('error')->addMessage('Select at least one Ticket.');
            //$this->_helper->flashMessenger->setNamespace('error')->addMessage(implode(', ', $fileUploadAdapter->getMessages()));
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'ticket/index/chap_id/'.$chap);
        }

        $ticketsModel = new Admin_Model_Ticket();

        foreach($ticketIds as $ticketId)
        {
            $ticketsModel->closeTicket($ticketId);
        }

        $this->_helper->flashMessenger->setNamespace('success')->addMessage('Selected ticket(s) closed successfully.');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'ticket/index/chap_id/'.$chap);
    }

    function updateAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $ticketId = $this->_request->getParam('id');
        $priority = $this->_request->getParam('priority');
        $status = $this->_request->getParam('status');
        $type = $this->_request->getParam('type');
        //$source = $this->_request->getParam('source');
        $assign = $this->_request->getParam('assign');

        //echo $ticketId;die();
        $ticketsModel = new Admin_Model_Ticket();

        $data = array();
        if(!empty($priority))
        {
            $data['priority'] = $priority;
        }
        if(!empty($status))
        {
            $data['status'] = $status;
        }
        if(!empty($type))
        {
            $data['type'] = $type;
        }

        $ticketsModel->updateTicket($ticketId,$data);

        $this->_helper->flashMessenger->setNamespace('success')->addMessage('Ticket properties updated successfully.');
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'ticket/single-ticket/id/'.$ticketId);
    }

    function addResponseAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        //$userId = '21134';

        $id = $this->_request->getParam('id');
        /*get chap details*/
        $ticketsObj= new Admin_Model_Ticket();
        $ticketRow = $ticketsObj->getTicketById($id);
        $userObj= new Cpbo_Model_User();
        $userDetails=$userObj->getUserDetails($ticketRow->user_id);
        $userId = $userDetails[0]['chap_id'];
        /*end*/
        $description = $this->_request->getParam('description');

        $fileUploadAdapter = new Zend_File_Transfer_Adapter_Http();

        //Adding validation for uploaded files
        $fileUploadAdapter->addValidator('Size', false, 5242880); //5MB
        $fileUploadAdapter->addValidator('Extension',false,'jpg,jpeg,eps,gif,png,zip,doc,docx,jad,cod,sis,sisx,apk,cab,mp3,jar,ipk,wgz,prc,jpg,jpeg,gif,png,bar,pdf'); //Allowed file types - got from upload.js

        $attachment = $fileUploadAdapter->getFileName('attach',false);

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
            $this->_helper->flashMessenger->setNamespace('success')->addMessage('Your response submitted successfully.');

            //Send notification to agent when user adding a response
            //$this->sendResponseNotificationAgent($lastInsertId);
            $this->sendTicketingNotificationUser($id);
        }
        else{
            $this->_helper->flashMessenger->setNamespace('Error')->addMessage('Error on adding your response.');
        }

        //$this->_helper->flashMessenger->setNamespace('success')->addMessage('Your response submited successfully.');
        $this->_redirect("/ticket/single-ticket/id/$id");
    }

    function downloadAttachementAction()
    {
        // disable the view ... and perhaps the layout
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $file = $this->_request->getParam('file');

        //$zipFilename = 'tickets/attachment/noooo.apk';
        $filename = 'tickets/attachments/'.$file;

        if(file_exists($filename))
        {
            //$archive = new Nexva_Util_FileFormat_Zip_PclZip($zipFilename);
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
            $this->_helper->flashMessenger->setNamespace('Error')->addMessage('Error downloading file.');
            //$url = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();     //echo $url;die();
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'ticket');
        }
    }

    function overdueTicketCount($openTickets){
        $overdueCount = 0;
        //Get current time
        $now = strtotime(date('d-m-Y H:i:s'));
        foreach($openTickets as $openTicket){
            $createDate = Date('d-m-Y H:i:s', strtotime($openTicket->created_date));
            $timeDiff = round(($now - strtotime($createDate))/60);

            switch ($openTicket->priority){
                case 'Urgent';
                    if($timeDiff > 720){
                        $overdueCount++;
                    }
                    break;
                case 'High';
                    if($timeDiff > 1440){
                        $overdueCount++;
                    }
                    break;
                case 'Medium';
                    if($timeDiff > 2160){
                        $overdueCount++;
                    }
                    break;
                case 'Low';
                    if($timeDiff > 2880){
                        $overdueCount++;
                    }
                    break;
            }
        }
        return $overdueCount;
    }

    function getOverdueTodayTickets($openTickets){
        $overdueTodayCount = 0;
        //Get current time
        $now = date('d-m-Y');
        foreach($openTickets as $openTicket){
            switch ($openTicket->priority){
                case 'Urgent';
                    $dueTime = Date('d-m-Y H:i:s', strtotime($openTicket->created_date.'+12 hour'));
                    break;
                case 'High';
                    $dueTime = Date('d-m-Y H:i:s', strtotime($openTicket->created_date.'+24 hour'));
                    break;
                case 'Medium';
                    $dueTime = Date('d-m-Y H:i:s', strtotime($openTicket->created_date.'+36 hour'));
                    break;
                case 'Low';
                    $dueTime = Date('d-m-Y H:i:s', strtotime($openTicket->created_date.'+48 hour'));
                    break;
            }
            if($now == Date('d-m-Y',strtotime($dueTime))){
                $overdueTodayCount++;
            }
        }
        return $overdueTodayCount;
    }

    function sendTicketingNotificationUser($ticketId){

        $ticketModel = new Admin_Model_Ticket();
        $ticket = $ticketModel->getTicketById($ticketId);

        $userId = $ticket->user_id;

        $userModel = new Model_User();
        $userRow = $userModel->getUserById($userId);
        $userEmail = $userRow->email;
        $userFirstName = $userModel->getMetaValue($userId, 'FIRST_NAME');
        //$baseUrl = $this->view->serverUrl();
        $ticketUrl = Zend_Registry::get('config')->nexva->application->cpbo->url.'/ticket/single-ticket/id/'.$ticketId;

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