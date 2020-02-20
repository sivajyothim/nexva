<?php

include_once("PaypalApi/DPayPal.php");

/**
 *
 */
class Nexva_Paypal_PaypalConnect {

    public function sendPaymentRequest(array $param) {

        $paypal = new DPayPal();

        $requestParams = array(
            'RETURNURL' => Zend_Registry::get('config')->paypal->mobile->sandbox->return,
            'CANCELURL' => Zend_Registry::get('config')->paypal->mobile->sandbox->cancel
        );
        
        $orderParams = array(
            'LOGOIMG' => Zend_Registry::get('config')->paypal->mobile->sandbox->logo, //You can paste here your logo image URL
            "MAXAMT" => Zend_Registry::get('config')->paypal->mobile->sandbox->max_amount, //Set max transaction amount
            "NOSHIPPING" => Zend_Registry::get('config')->paypal->mobile->sandbox->no_shopping, //I do not want shipping
            "ALLOWNOTE" => Zend_Registry::get('config')->paypal->mobile->sandbox->allow_note, //I do not want to allow notes
            "BRANDNAME" => Zend_Registry::get('config')->paypal->mobile->sandbox->brand,
            "GIFTRECEIPTENABLE" => Zend_Registry::get('config')->paypal->mobile->sandbox->gift_receptenable,
            "GIFTMESSAGEENABLE" => Zend_Registry::get('config')->paypal->mobile->sandbox->gift_message_enable,
        );
        
        $item = array(
            'PAYMENTREQUEST_0_AMT' => $param['product_price'],
            'PAYMENTREQUEST_0_CURRENCYCODE' =>$param['currency_code'],
            'PAYMENTREQUEST_0_ITEMAMT' => $param['product_price'],
            'L_PAYMENTREQUEST_0_NAME0' => $param['product_name'],
     //       'L_PAYMENTREQUEST_0_DESC0' => $this->_request->product_description,//'Buy me a beer :)',
            'L_PAYMENTREQUEST_0_AMT0' => $param['product_price'],
            'L_PAYMENTREQUEST_0_QTY0' => '1',
             "PAYMENTREQUEST_0_INVNUM" => time().'-'.$param['product_id'],
        );

        $response = $paypal->SetExpressCheckout($requestParams + $orderParams + $item);
        sleep(5);
        $status=Array();
       
        if (is_array($response) && $response['ACK'] == 'Success') {          
            //Now we have to redirect user to the PayPal 
            $status['token']=$response['TOKEN'];
            $status['status']='Success';
            return $status;
        } else if (is_array($response) && $response['ACK'] == 'Failure') {
            $status['status']='Failure';
            return $status;
        }else{
            $status['status']='Other';
            return $status;
        }
        
    }

    public function conformPayment() {
        $token = $_GET["token"]; 
        $paypal = new DPayPal();
        $requestParams = array('TOKEN' => $token);

        $response = $paypal->GetExpressCheckoutDetails($requestParams);
        
        $payerId = $response["PAYERID"];
        //Create request for DoExpressCheckoutPayment
        $requestParams = array(
            "TOKEN" => $token,
            "PAYERID" => $payerId,
            "PAYMENTREQUEST_0_AMT" => $response['PAYMENTREQUEST_0_AMT'],
            "PAYMENTREQUEST_0_CURRENCYCODE" => $response['PAYMENTREQUEST_0_CURRENCYCODE'], 
            "PAYMENTREQUEST_0_ITEMAMT" => $response['PAYMENTREQUEST_0_ITEMAMT'],
            "PAYMENTREQUEST_0_INVNUM" => $response['PAYMENTREQUEST_0_INVNUM']
        );
        $transactionResponse = $paypal->DoExpressCheckoutPayment($requestParams); 
        $status=array();
        if (is_array($transactionResponse) && $transactionResponse["ACK"] == "Success") {
            
            $paypalModel=new Api_Model_PaypalResponce();
            $paypalModel->insertResponce($transactionResponse,$response['PAYMENTREQUEST_0_INVNUM']);
            
            $url =Zend_Registry::get('config')->paypal->mobile->sandbox->success.'/status/'.$transactionResponse['ACK'].'/currency/'.$transactionResponse['PAYMENTINFO_0_CURRENCYCODE'].'/amount/'.$transactionResponse['PAYMENTINFO_0_AMT'].'/seller/'.$transactionResponse['PAYMENTINFO_0_SELLERPAYPALACCOUNTID'];
            
            $status[1]='Success';
            $status[2]=$url;
            return $status;
        } else {
            $status[1]='error';
            return $status;
        }
    }

}

?>