<?php

include_once("PaypalApi/DPayPal.php");

/**
 *
 */
class Nexva_TestPaypalRecurring_PaypalConnect {

    public function sendPaymentRequest(array $param) {

        $paypal = new DPayPal();

        $requestParams = array(
            'RETURNURL' => 'http://api.nexva.com/test-paypal/conform-recurring-payment',
            'CANCELURL' => 'http://ecarrot.nexva.com/app/top-premium'
        );
        
        $orderParams = array(
            'LOGOIMG' => 'http://www.ecarrot.com.au/img/logo.png', //You can paste here your logo image URL
            "MAXAMT" => 100, //Set max transaction amount
            "NOSHIPPING" => 1, //I do not want shipping
            "ALLOWNOTE" => 0, //I do not want to allow notes
            "BRANDNAME" => 'E-Caret',
            "GIFTRECEIPTENABLE" => 0,
            "GIFTMESSAGEENABLE" => 0,
        );
        
        $item = array(
            'PAYMENTREQUEST_0_AMT' => 0,
            'PAYMENTREQUEST_0_CURRENCYCODE' =>$param['currency_code'],
            'L_BILLINGTYPE0' => 'RecurringPayments',
            'L_BILLINGAGREEMENTDESCRIPTION0' => 'test',
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
        $requestParams = array(
            'TOKEN' => $token
        );

        $response = $paypal->GetExpressCheckoutDetails($requestParams);
        
        $payerId = $response["PAYERID"];
        
        $paypalPayment = new Zend_Session_Namespace('paypal');
        
        $profileParams = array(
            'TOKEN' => $token,
            "PAYERID" => $payerId,
            "PROFILESTARTDATE" => date("Y-m-d\TH:i:s\Z", strtotime('+1 year')),
            "DESC" => 'test',
            "BILLINGPERIOD" => 'Month',
            "BILLINGFREQUENCY" => 1,
            "AMT" => $paypalPayment->amt,
            "CURRENCYCODE" => "USD",
            "COUNTRYCODE" => "US",
            "MAXFAILEDPAYMENTS" => 3,
            "AUTOBILLOUTAMT" => "AddToNextBilling",
            "PROFILEREFERENCE" => "15"//This value is for example ID of a user 
        );
        
        
        
        $recurringProfileResponse = $paypal->CreateRecurringPaymentsProfile($profileParams);  
        $res['PROFILEID']=$recurringProfileResponse['PROFILEID'];

        $recurringProfileDetails = $paypal->GetRecurringPaymentsProfileDetails($res);
        echo "<pre>";
        print_r($recurringProfileDetails);die();
        $date=date_create($recurringProfileDetails['NEXTBILLINGDATE']);
        $responce =array(
            'profile_id' =>$res['PROFILEID'],
            'profile_status' =>$recurringProfileDetails['STATUS'],
            'email' => $paypalPayment->email,
            'final_payment_date' =>date_format($date,"Y-m-d"),
            'created_at' => date('Y-m-d'),
            'user_id' =>  $paypalPayment->user_id 
           
        );
       
        $paypalPayment->unsetAll();
        
        $status=array();
        if (is_array($recurringProfileResponse) && $recurringProfileResponse["PROFILESTATUS"] == "ActiveProfile") {
            
            $paypalModel=new Api_Model_EcarrotRecurringPayment();
            $paypalModel->insertResponce($responce);  
            $status[1] = 'Success';
            $status[2] = $recurringProfileDetails['FINALPAYMENTDUEDATE'];
           
            return $status;
            
        } else {
            $status[1]='error';
            return $status;
        }
    }

}

?>