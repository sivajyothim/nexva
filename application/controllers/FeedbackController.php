<?php
class Default_FeedbackController extends Nexva_Controller_Action_Web_MasterController {

    public function init() {
        /* Initialize action controller here */
        // include Ketchup libs
        parent::init();
        $this->setLastRequestedUrl();
        $this->view->headLink()->appendStylesheet( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/css/jquery.ketchup.css');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.js');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.messages.js');
        $this->view->headScript()->appendFile( PROJECT_BASEPATH.'common/js/jquery/plugins/ketchup-plugin/js/jquery.ketchup.validations.basic.js');

    }

    public function indexAction() {

        $this->_helper->layout->setLayout('web/web_innerpage');
        if ($this->_request->isPost()) {// submit the feedback form
            $requestPerms = $this->_request->getParams();
            $type = $requestPerms['type'];
            $email = trim($requestPerms['email']);
            $body = trim($requestPerms['body']);

            // validate email
            // check email in email field
            if(empty ($email)) {
                $this->view->messages = array('error' => 'The email address you supplied is not valid.');
                return;
            }

            if(empty ($body)) {
                $this->view->messages = array('error' => 'Please add your message.');
                return;
            }
//            exit;

            // construct the mail template
            $mailer = new Nexva_Util_Mailer_Mailer();
            $mailer->setSubject('neXva - Website Feedback');
            // @TODO : add feedback senders email to this
            $config = Zend_Registry::get('config');
//            $mailer->addTo($config->nexva->application->admin->contact, 'Admin')
            $mailer->addTo('heshanmw@gmail.com', 'Heshan')
                    ->setLayout("generic_mail_template")         //change if needed. remember, stylesheets cannot be linked in HTML email, so it has to be embedded. see the layout for more.
                    ->setMailVar("email", $email)
                    ->setMailVar("type", $type)
                    ->setMailVar("body", $body)
                    ->sendHTMLMail('feedback.phtml'); //change this. mail templates are in /views/scripts/mail-templates
            $this->view->messages = array('info' => 'Thanks! Your feedback is sent to the admin.');
        }
        $this->view->formValues = $this->_request;
    }

}
?>
