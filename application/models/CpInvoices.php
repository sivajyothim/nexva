<?php


class Model_CpInvoices extends Zend_Db_Table_Abstract {


    protected $_id = 'id';
    protected $_name = 'cp_invoices';

    function __construct() {
        parent::__construct();
    }
    
    /**
     * Get invoice by given ID
     * @param int $id
     * @return obj rowset
     * @author Chathura 
     */ 
    
    function getInvoice($id)    {
    	$rowset = $this->find($id);
        $invoiceRow = $rowset->current();
        return $invoiceRow;
    	
    }
    
    /**
     * Get invoices by given user id
     * @param int $userId
     * @return obj rowset
     * @author Chathura 
     */ 
    
    function getInvoiceByUserId($userId)    {
    	$rowset = $this->fetchAll("user_id = $userId", 'id DESC');
        return $rowset;
    	
    }
    
    
  /**
   * Send the invoice for the reloaded account
   */
  public function sendInvoice($userId, $data = array()) {
    $config = Zend_Registry::get('config');
    $userModel = new Model_User();
    $userRow = $userModel->getUserDetailsById($userId);
    $userMeta = new Model_UserMeta();
    $userMeta->setEntityId($userId);
    $mailer = new Nexva_Util_Mailer_Mailer();
    $mailer->setSubject('neXva - Receipt for your deposit payment');
    $mailer->addTo($userRow['email'], $userRow['email'])
        ->setLayout("generic_mail_template")
        ->setMailVar("email", $userRow['email'])
        ->setMailVar("name", $userMeta->FIRST_NAME . ' ' . $userMeta->LAST_NAME)
        ->setMailVar("address", $userMeta->ADDRESS)
        ->setMailVar("city", $userMeta->CITY)
        ->setMailVar("country", $userMeta->COUNTRY)
        ->setMailVar("mobile", $userMeta->MOBILE)
        ->setMailVar("transid", $data['transaction_id'])
        ->setMailVar("invoice_id", $data['invoice_id'])
        ->setMailVar("amount", $data['amount'])
        ->setMailVar("paymentgateway", $data['payment_gateway'])
         ->sendHTMLMail('cp_invoice.phtml');
  }
    
}
