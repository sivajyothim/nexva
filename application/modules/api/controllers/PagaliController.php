<?php
/**
 * Created by PhpStorm.
 * User: viraj
 * Date: 8/1/14
 * Time: 4:57 PM
 */

class Api_PagaliController extends Zend_Controller_Action {

    public function init() {
        /* Initialize actionNexpayer controller here */
        $this->_helper->layout ()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

    }

    public function redirectAction(){

        if(!empty($_REQUEST)){
            $chapId = 136079;
            $sessionId = Zend_Session::getId();

            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $pgType = $pgDetails->gateway_id;
            $paymentGatewayId = $pgDetails->payment_gateway_id;
            $pgClassName = $pgType;

            //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
            $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);

            $postBackResponse = $_REQUEST;
            $returnDetails = $pgClass->handleResponse($postBackResponse, $chapId);

            header("location: pagali://view?status=".$returnDetails['status']."&build_url=".$returnDetails['build_url']."&api_response=".$returnDetails['status_message']);

            //$this->getResponse()->setHeader('Content-type', 'application/json');
            //$returnDetails = str_replace('\/', '/', json_encode($returnDetails));
            //echo $returnDetails;
        } else {
            /*$returnDetails['status_message'] = 'Empty response';
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $returnDetails = str_replace('\/', '/', json_encode($returnDetails));
            echo $returnDetails;*/

            $returnDetails['status_message'] = 'Empty response';
            $returnDetails['status'] = 'fail';
            $returnDetails['build_url'] = null;

            header("location: pagali://view?status=".$returnDetails['status']."&build_url=".$returnDetails['build_url']."&api_response=".$returnDetails['status_message']);
        }


    }

    public function pagaliRedirectionAction(){

            //$token = $this->_getParam('token', null);
            $chapId = $this->_getParam('chap_id', null);
            $sessionId = $this->_getParam('session_id', null);
            $appId = $this->_getParam('app_id', null);
            $build_id = $this->_getParam('build_id', null);
            $price = $this->_getParam('price', null);
            $appName = $this->_getParam('app_name', null);
            $mobileNo = $this->_getParam('mobile_number', null);
            $userId = $this->_getParam('user_id', null);

            //Get payment gateway Id of the CHAP
            $pgUsersModel = new Api_Model_PaymentGatewayUsers();
            $pgDetails = $pgUsersModel->getGatewayDetailsByChap($chapId);
            $pgType = $pgDetails->gateway_id;
            $paymentGatewayId = $pgDetails->payment_gateway_id;
            $pgClassName = $pgType;

            //Call Nexva_MobileBilling_Factory and create relevant instance. Since this is a redirection payment, this factory doesn't contain the payment codes
            $pgClass = Nexva_PaymentGateway_Factory::factory($pgType,$pgClassName);

            //Save Initals transaction record in the DB (This is to track if the payment was made successfully or not)
            $interopPaymentId = $pgClass->addMobilePayment($chapId, $appId, $build_id, $mobileNo, $price, $paymentGatewayId);

            //Select the relevent payment records
            //$purchasedAppDetails = $pgClass->selectInteropPayment($sessionId, $paymentId);
            //$price = $purchasedAppDetails->price;

            //Convert the price to the local price
            $currencyUserModel = new Api_Model_CurrencyUsers();
            $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
            $currencyRate = $currencyDetails['rate'];
            $currencyCode = $currencyDetails['code'];
            $price = ceil($currencyRate * $price);

            $pgData = array(
                'id_ent' => 'E46ECE05-2971-31F3-39BD-819B8D503772',
                'id_temp' => 'DA624A65-C6C9-EECE-C2FD-1DED96531A40',
                'order_id' => $interopPaymentId.'nexva'.$sessionId,
                //'order_id' => 'nexva'.$sessionId,
                'currency_code' => $currencyCode,
                'return' => 'http://api.nexva.com/pagali/redirect',
                'notify' => 'http://api.nexva.com/pagali/redirect',
                'total' => $price,
                'item_name' => $appName,
                'quantity' => '1',
                'item_number' => $appId,
                'amount' => $price,
                'total_item' => $price,
            );

            $caboAppsSession = new Zend_Session_Namespace('caboapps');
            $caboAppsSession->userId = $userId;

            ?>
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <script type="text/javascript">
                        function closethisasap() {
                            document.forms["redirectpost"].submit();
                        }
                    </script>
                </head>
                <body onload="closethisasap();">
                <form name="redirectpost" method="post" action="https://www.pagali.cv/pagali/index.php?r=pgPaymentInterface/ecommercePayment">
                    <?
                    if (!is_null($pgData)) {
                        foreach ($pgData as $k => $v) {
                            echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
                        }
                    }
                    ?>
                </form>
                </body>
                </html>
        <?php

        }

}