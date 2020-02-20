<?php

/**
 * A general utility class to send HTML emails easily using Zend_Layout/Zend_View to build the HTML content.
 *
 * @version $id$
 * @author jahufar 
 */

define("DEFAULT_MAIL_FROM_NAME", "neXva");
define("DEFAULT_MAIL_FROM_EMAIL", "do-not-reply@nexva.com");





class Nexva_Util_Mailer_Mailer extends Zend_Mail {
    
    /**
     * Layout object
     *
     * @var Zend_Layout
     */
    protected $_layout = null;

    /**
     * View object
     *
     * @var Zend_View
     */
    protected $_view = null;


    public  function  __construct() {
        
        parent::__construct('utf-8');
        
        $this->setDefaultFrom(DEFAULT_MAIL_FROM_EMAIL, DEFAULT_MAIL_FROM_NAME);
        $this->initView();

        //Dynamically switch outgoing emails in one hour gap
        $currentHour = date('H');
        $emailOrder = $currentHour % 4;
        
        $emailId = null;
        $password =null;
        
        switch($emailOrder){
        case 0:
            $emailId = 'support@nexva.com';
            $password = '24dragonballs';
            $password = 'cQpor2NPtJoa2nRorE22Wg';
            break;
        
        case 1:
            $emailId = 'support_admin@nexva.com';
            $password = 'dragonbane123';
            $password = 'z2RB4uG5ZMuKlo_LmOGJ_w';
            break;
        
        case 2:
            $emailId = 'support_contentadmin@nexva.com';
            $password = 'dragonbane123';
            $password = 'NrZ9TKd6NMNqa47ru6edfw';
            
            break;
        
        default:
            $emailId = 'chathura@nexva.com';
            $password = 'IQWO04Pt1MVJygGRKHTTqA';
            break;
            
        }
        

        	$emailId = 'neXva, Inc.';
        	$password = 'zboSGO_p5MjrH_hgc-xaEw';
        
        $config = array(
        	
        		'type' => 'smtp',
        		'port' =>  587,
        		'auth' => 'plain',
        		'username' => $emailId,
        		'password' => $password
        );


        /*
     

     

      
        $config = array(
        		'ssl' => 'tls',
        		'port' => 587,
        		'auth' => 'login',
        		'username' => $emailId,
        		'password' => $password
        );
      

     transport[type] = smtp
     transport[host] = smtp.mandrillapp.com
     transport[name] = mandrill
     transport[ssl] = ssl
     transport[port] =
     transport[auth] = plain
     transport[username] = USERNAME
     transport[password] = API_KEY

        
        if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            $transport = new Zend_Mail_Transport_Smtp('smtp.mandrillapp.com', $config);
            Zend_Mail::setDefaultTransport($transport);
            $this->setDefaultTransport($transport);
            
                   $transport = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $config);
        Zend_Mail::setDefaultTransport($transport);
        $this->setDefaultTransport($transport);
            
        }
 */
 
        
        $transport = new Zend_Mail_Transport_Smtp('smtp.mandrillapp.com', $config);
        Zend_Mail::setDefaultTransport($transport);
        $this->setDefaultTransport($transport);
    
    }


    /**
     * Initializes view (and layout) that will be used to render the HTML email
     *
     */
    protected function initView() {
       
        $this->_view = new Zend_View();
        $this->_view->setScriptPath(APPLICATION_PATH. "/views/scripts/mail-templates/");

        $this->_layout = new Zend_Layout(APPLICATION_PATH . '/views/layouts/mail-templates');
        $this->_layout->setView($this->_view);    
    }

    /**
     * Sets the mail template layout to use
     *
     * @param string $layout
     * @return Nexva_Util_Mailer_Mailer
     */
    public function setLayout($layout)
    {
        $this->_layout->setLayout($layout);
        return $this;
    }

    /**
     * Send the HTML email
     *
     * @return Zend_Mail
     */
    public function sendHTMLMail($template) {

        $this->_layout->content = $this->_view->render($template);

        $html= $this->_layout->render();

        $this->setBodyHtml($html);
        
        /*if($_SERVER['REMOTE_ADDR'] == '220.247.236.99'){
            //error_reporting(E_ALL);
            //ini_set('display_errors',1);
            //echo implode(', ', $this->getDefaultFrom()).'##'.implode(', ', $this->_to).'##'.$this->_subject.'##'.date('Y-m-d H:i:s');
            // die();

            //Inserting the mail details to the DB
            $modelMailLogs = new Model_MailLogs();
            $data = array(
                'from' => $this->getDefaultFrom(),
                'to' => implode(', ', $this->_to),
                'message' => $this->_subject,
                'date_time' => date('Y-m-d H:i:s')
            );
            
            $modelMailLogs->insertRecords($data);
            
           
        }*/
        
        //Inserting the mail details to the DB
        $modelMailLogs = new Model_MailLogs();
        
        $currentUrlDetails = '';
        foreach($_SERVER as $key => $value){
            $currentUrlDetails .= $key.'=>'. $value.'###';         
        }
        $data = array(
            'from' => implode(', ', $this->getDefaultFrom()),
            'to' => implode(', ', $this->_to),
            'message' => $this->_subject,
            'date_time' => date('Y-m-d H:i:s'),
            'remote_ip' => $_SERVER['REMOTE_ADDR'],
            //'current_url' =>  $_SERVER['PHP_SELF'].'###'.basename($_SERVER['REQUEST_URI']).'###'.$_SERVER["SCRIPT_NAME"]
            //'current_url' => implode('###', $_SERVER)
            'current_url' => $currentUrlDetails
        );

        $modelMailLogs->insertRecords($data);

        return $this->send();
    }

    /**
     * Returns the rendered HTML content to be sent. Useful for debugging purposes.
     *  
     * @param string $template Template to render
     * @return string
     */
    public function getHTMLMail($template) {

        $this->_layout->content = $this->_view->render($template);
        $html= $this->_layout->render();

        return $html;
    }

    /**
     * Sets a variable in the internal Zend_View object.
     *
     * @param string $name
     * @param mixed $value
     * @return Nexva_Util_Mailer_Mailer
     */
    public function setMailVar($name, $value) {

        $this->_view->$name = $value;
        return $this;
    }

    /**
     * Overriden so that emails such as "email1@domain.com, email2.domain2.com..[]" is supported
     * 
     * @param string $email
     * @param string $name
     * @return Nexva_Util_Mailer_Mailer
     */
    public function addTo($email, $name='') {
        if (!is_array($email)) {
            $emails = explode(",", $email);

            foreach($emails as $email)
                parent::addTo($email);

            return $this;
        }

        parent::addTo($email, $name);
        return $this;

    }
}
?>