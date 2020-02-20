<?php

/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */
class Model_Order extends Zend_Db_Table_Abstract {

  protected $_name = "orders";
  protected $_id = "id";
  protected $_dependentTables = "order_details";

  function __construct() {
    parent::__construct();
  }

  protected $_referenceMap = array(
    "user" => array(
      'columns' => array("user_id"),
      'refTableClass' => "Model_User",
      'refColumns' => array("id")
    )
  );

  public function insertOrder($productId, $userId, $transId) {
    $product = new Model_Product();
    $productDetail = $product->getProductDetailsById($productId);
    // Orders
    $orderData = array(
      'user_id' => $userId,
      'order_date' => new Zend_Db_Expr('NOW()'),
      'merchant_id' => 'nexva_web',
      'transaction_id' => $transId
    );
    $orderId = $this->insert($orderData);
    // Orders details
    $orderDetails = new Model_OrderDetail();
    $orderDetailsData = array(
      'order_id' => $orderId,
      'product_id' => $productDetail['id'],
      'price' => $productDetail['cost']
    );
    $orderDetails->insert($orderDetailsData);
    return $orderId;
  }

  /**
   * Finds an order by its $id
   * 
   * @param int $id The order ID
   * @return Zend_Db_Table_Row_Abstract || null
   */
  public function findOrder($id) {
      return $this->fetchRow('id = '. $id);

  }

  public function userOrders() {
    $userId = Zend_Auth::getInstance()->getIdentity()->id;
    $orders = $this->select()
            ->setIntegrityCheck(false)
            ->join('products', 'orders.product_id = products.id')
            ->join('order_details', 'order_details.product_id = products.id')
            ->where('orders.user_id = ?', $userId)
            ->where("products.status = ? ", 'APPROVED')
            ->query()
            ->fetchAll();
    return $orders;
  }

  public function getUserOrder($userId, $productId) {

    $order = Zend_Registry::get('db')->select()
            ->from('orders', 'id')
            ->join('order_details', 'order_details.order_id = orders.id')
            ->where('orders.user_id = ?', $userId)
            ->where('order_details.product_id = ?', $productId)
            ->query()
            ->fetchAll();
    return $order[0];

  }

  public function isPruchsed($productId) {
    $userId = isset(Zend_Auth::getInstance()->getIdentity()->id) ? Zend_Auth::getInstance()->getIdentity()->id : 0;
    if (empty($userId))
      return false;
    $select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
    $select->setIntegrityCheck(false)
        ->where('orders.user_id = ?', $userId)
        ->join('order_details', 'order_details.order_id = orders.id')
        ->where('order_details.product_id = ?', $productId);
    $rows = $this->fetchAll($select);
    if ($rows->count() == 0)
      return false;
    else
      return true;
  }

  public function chkTransAction($key) {
    $check_transaction = $this->fetchRow($this->select()->where('transaction_id = ?', $key));
    if (is_null($check_transaction)) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Send the invoice for the purchase
   * @todo: Need to refactor this function so that $data is not needed. $data passes in the order info..it should be handled internally so this method is test-friendly.
   *        Ideally, sendInvoice() should only take in an order Id and should handle the logic internally based on the order id
   */
  public function sendInvoice($productId, $data = array()) {
    // ---------
    //ge the user model
    $config = Zend_Registry::get('config');
    $userModel = new Model_User();
    $product = new Model_Product();
    $productDetail = $product->getProductDetailsById($productId);
    $recommendationsObj = new Nexva_Recommendation_Recommendation();
    $apps = $recommendationsObj->getRecommendationsForProduct($productId, NULL, 5);
    $count = NULL;
    $recommendedApps    = array();
    foreach ($apps as $app) {
      ++$count;
      $recommendedApps[$count]['id'] = $app['id'];
      $recommendedApps[$count]['uid'] = $app['uid'];
      $recommendedApps[$count]['name'] = $app['name'];
      $recommendedApps[$count]['nameForLinks'] = rawurlencode(str_replace(" ", "-", $app['name']));
      $recommendedApps[$count]['thumb'] = $app['thumb'];
    }

    $registrationModel = '';
    if (isset($productDetail['registration_model']))
      $registrationModel = $productDetail['registration_model'];
    
    $key    = null;
    switch ($registrationModel) {
        case 'POOL':
        case 'STATIC':
            $productKeys = new Model_ProductKey();
            $key = $productKeys->licenceKeyForProduct($productId);
            if ($registrationModel == 'POOL')//delete key from pool
                $productKeys->daleteData($key, $productId); //@todo: fix typo. deleteData() makes no sense anyway. should be deleteKey()

            break;
    }
   
    //send invoice to user
    $userId = Zend_Auth::getInstance()->getIdentity()->id; //CR: this is BAD! THIS METHOD IS NOW UNTESTABLE SINCE IT RELIES ON SESSION. @todo: grab this from orders table.
    $userRow = $userModel->getUserDetailsById($userId);
    $userMeta = new Model_UserMeta();
    $userMeta->setEntityId(Zend_Auth::getInstance()->getIdentity()->id);
    $mailer = new Nexva_Util_Mailer_Mailer();
    $mailer->setSubject('neXva - Receipt for your app purchase : ' . $productDetail['name']);
    $mailer->addTo($userRow['email'], $userRow['email'])
        ->setLayout("generic_mail_template")
        ->setMailVar("email", Zend_Auth::getInstance()->getIdentity()->email)
        ->setMailVar("name", $userMeta->FIRST_NAME . ' ' . $userMeta->LAST_NAME)
        ->setMailVar("address", $userMeta->ADDRESS)
        ->setMailVar("city", $userMeta->CITY)
        ->setMailVar("country", $userMeta->COUNTRY)
        ->setMailVar("mobile", $userMeta->MOBILE)
        ->setMailVar("transid", $data['transaction_id'])
        ->setMailVar("orderid", $data['order_id'])
        ->setMailVar("paymentgateway", $data['payment_gateway'])
        ->setMailVar("productkey", $key)
        ->setMailVar("app_name", $productDetail['name'])
        ->setMailVar("cost", '$' . $productDetail['cost'])
        ->setMailVar("pid", $productDetail['id'])
        ->setMailVar("hash", md5($userId . $config->nexva->application->salt))
        ->setMailVar("userId", $userId)
        ->setMailVar("postTo", $config->nexva->application->base->url . "/review/add")
        ->setMailVar("recomondations", $recommendedApps)
        ->setMailVar("baseUrl", $config->nexva->application->base->url)
        ->setMailVar("is_registration_dynamic", $registrationModel == 'DYNAMIC') //see ticket #351
        ->sendHTMLMail('invoice.phtml');
  }

    /**
     * Sends a notification email to CP when an app is purchased
     * Notifications are sent to email specified at NOTIFY_EMAIL in product_meta
     *
     * @param int $orderId
     */
    public function sendNotificationOfPurchase($orderId) {

        $order = $this->fetchRow('id = '. $orderId);

        $userModel = new Cpbo_Model_User();
        $user = $userModel->fetchRow('id = '.$order->user_id);

        $orderDetails = $this->getOrderDetails($orderId);

        $productModel = new Model_Product();
        $product = $productModel->getProductDetailsById($orderDetails->product_id, true);

        $productMeta = new Model_ProductMeta();
        $productMeta->setEntityId($orderDetails->product_id);

        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('neXva - Notification of purchase: ' . $product['name'])
        ->setLayout("generic_mail_template");

        if( 'staging' == APPLICATION_ENV || 'development' == APPLICATION_ENV ) //in staging||dev send this email back to the developer who is testing (assumption: purchaser's email belongs to developer since only developers have access to staging)
            $mailer->addTo($user->email);
                   
        if( 'production' == APPLICATION_ENV ) { //only send this to the CP in production mode + to jahufar (remove this once we have orders coming in)
            $mailer->addTo($productMeta->NOTIFY_EMAIL);
            $mailer->addBcc('jahufar@nexva.com');            
        }
         
        
        $mailer->setMailVar('product_name', $product['name'])
        ->setMailVar('product_id', $orderDetails->product_id)
        ->setMailVar('order', $order)
        ->setMailVar('order_details', $orderDetails)
        ->setMailVar('user', $user);
                       
        $mailer->sendHTMLMail('cp_notification_of_purchase.phtml');                    
  }


    /**
     * Gets order detail for order $id
     *
     * @param <type> $id
     * @return Zend_Db_Table_Row_Abstract
     */
    public function getOrderDetails($id) {
      $orderDetailsModel = new Model_OrderDetail();
      return $orderDetailsModel->fetchRow('order_id = '. $id);
    }

}

?>