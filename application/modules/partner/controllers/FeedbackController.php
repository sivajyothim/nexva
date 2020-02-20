<?php

class Partner_FeedbackController extends Nexva_Controller_Action_Partner_MasterController {

    public function init()
    {
        parent::init();
    }

    public function indexAction() 
    {

        if ($this->_request->isPost()) 
        {          
            $userName = trim($this->_getParam('txtName'));
            $email = trim($this->_getParam('txtEmail'));
            $message = trim($this->_getParam('txtMessage'));  
            
            //Validate email            
            if(empty ($userName)) 
            {
                $this->view->messages = array('error' => 'The email address you supplied is not valid.');
                return;
            }
            
            if(empty ($email)) 
            {
                $this->view->messages = array('error' => 'The email address you supplied is not valid.');
                return;
            }

            if(empty ($message)) 
            {
                $this->view->messages = array('error' => 'Please add your message.');
                return;
            }

            // construct the mail template
            $mailer = new Nexva_Util_Mailer_Mailer();
            $mailer->setSubject('Website - Need Help');
           
            $config = Zend_Registry::get('config');

            //Load chap id via session
            $sessionChapDetails = new Zend_Session_Namespace('partner');
            $chap_ID = $sessionChapDetails->id;

            //Load them meta model
            $themeMeta   = new Model_ThemeMeta();
            $themeMeta->setEntityId($chap_ID);

            $to = $themeMeta->WHITELABLE_SITE_CONTACT_US_EMAIL;

            $mailer->addTo($to, 'Maheel')
                    ->setLayout("generic_mail_template")         //change if needed. remember, stylesheets cannot be linked in HTML email, so it has to be embedded. see the layout for more.
                    ->setMailVar("email", $email)
                    ->setMailVar("name", $userName)
                    ->setMailVar("body", $message)
                    ->sendHTMLMail('wl_feedback.phtml'); //change this. mail templates are in /views/scripts/mail-templates
            
            if($this->_chapId == '585474' or $this->_chapId == '585480')
                $this->view->messages_sent = 'Merci ! Votre commentaire a bien &eacute;t&eacute; enregistr&eacute;.';
            else 
                $this->view->messages_sent = 'Thank you! Your feedback has bent sent to the admin.';
        }

    }  
   
    

}