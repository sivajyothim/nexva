<?php
class Model_UserMessage extends Zend_Db_Table_Abstract {
	
	protected $_id = 'id';
	protected $_name = 'user_messages';
	
	function __construct() {
		parent::__construct ();
	}
	
	function getAllUserMessage($userId) {
		
		$db = Zend_Registry::get ( 'db' );
		
		//$messages = $this->select ()->where ( 'user_id = ?', $userId )->query ()->fetchAll ();
		//$messages = $this->select()->where ( 'user_id = ?', $userId )->query();
		

		$messages = $this->select ()->where ( 'user_id = ?', $userId );
		
		return $messages;
	
	}
	
   function getAllUserProductMessage($productId) {
        
        $db = Zend_Registry::get ( 'db' );
        
        //$messages = $this->select ()->where ( 'user_id = ?', $userId )->query ()->fetchAll ();
        //$messages = $this->select()->where ( 'user_id = ?', $userId )->query();
        

        $messages = $this->select ()->where ( 'product_id = ?', $productId );
        
        return $messages;
    
    }   
	
	function sendMessageProductUpdates($productId, $userId, $subject, $message) {
		
		
		$values = array (
		          "user_id"   => $userId, 
		          "product_id"   => $productId, 
		          "subject"   => $subject, 
		          "message"   => $message, 
		          "sent_date" =>  new Zend_Db_Expr('now()')
                );
                
  
		 $this->insert ( $values );


	}
	
function sendMessage($userId, $subject, $message) {
        
        
        $values = array (
                  "user_id"   => $userId, 
                  "subject"   => $subject, 
                  "message"   => $message, 
                  "sent_date" =>  new Zend_Db_Expr('now()')
                );
                
  
         $this->insert ( $values );
         
         $user  =  new Model_User();
         $userRow = $user->getUserById($userId);
         
            $userMeta = new Model_UserMeta();
            $fisrtName  =  $userMeta->getAttributeValue($userRow->id,'FIRST_NAME');
            $lastName  =  $userMeta->getAttributeValue($userRow->id,'LAST_NAME');
             
            
            if(empty($fisrtName)) 
            {
                $name = $userRow->email;
            
            }
            else
            {
                $name =  $fisrtName . ' ' .$lastName;
            }
 
            
                $mailer = new Nexva_Util_Mailer_Mailer();
                $mailer->addTo($userRow->email, $userRow->email)
                ->setSubject($subject)
                ->setLayout("generic_mail_template")
                ->setMailVar("name",$name)
                ->setMailVar("body",$message)
                ->sendHTMLMail('cp_send_email.phtml');
                $mailer->send();
         

    }
}
?>
