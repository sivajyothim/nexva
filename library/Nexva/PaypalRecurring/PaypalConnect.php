<?php

include_once("PaypalApi/DPayPal.php");

/**
 *
 */
class Nexva_PaypalRecurring_PaypalConnect {

    public function sendPaymentRequest(array $param) {

        $paypal = new DPayPal();

        $requestParams = array(
            'RETURNURL' => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->return,
            'CANCELURL' => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->cancel
        );
        
        $orderParams = array(
            'LOGOIMG' => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->logo, //You can paste here your logo image URL
            "MAXAMT" => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->max_amount, //Set max transaction amount
            "NOSHIPPING" => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->no_shopping, //I do not want shipping
            "ALLOWNOTE" => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->allow_note, //I do not want to allow notes
            "BRANDNAME" => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->brand,
            "GIFTRECEIPTENABLE" => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->gift_receptenable,
            "GIFTMESSAGEENABLE" => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->gift_message_enable,
        );
        
        $item = array(
            'PAYMENTREQUEST_0_AMT' => 0,
            'PAYMENTREQUEST_0_CURRENCYCODE' =>'AUD',
            'L_BILLINGTYPE0' => 'RecurringPayments',
            'L_BILLINGAGREEMENTDESCRIPTION0' => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->description,
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
            "PROFILESTARTDATE" => gmdate("Y-m-d\TH:i:s\Z"),
            "DESC" => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->description,
            "BILLINGPERIOD" => 'Month',//zend_Registry::get('config')->paypal->mobile->recurring->sandbox->billingperiod,
            "BILLINGFREQUENCY" => 1,//Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->billingfrequency,
            "TOTALBILLINGCYCLES" => 12,
            "AMT" => $paypalPayment->amt,
            "CURRENCYCODE" => "AUD",
            "COUNTRYCODE" => "US",
            "MAXFAILEDPAYMENTS" => Zend_Registry::get('config')->paypal->mobile->recurring->sandbox->failpaymentattemps,
            "AUTOBILLOUTAMT" => "AddToNextBilling",
            "PROFILEREFERENCE" => "15"//This value is for example ID of a user 
        );
        
        
        
        $recurringProfileResponse = $paypal->CreateRecurringPaymentsProfile($profileParams);  
        $res['PROFILEID']=$recurringProfileResponse['PROFILEID'];
        $res['METHOD'] = 'GetRecurringPaymentsProfileDetails';
        
        $recurringProfileDetails = $paypal->GetRecurringPaymentsProfileDetails($res);
        $date=date_create($recurringProfileDetails['NEXTBILLINGDATE']);
        
        $time = strtotime(date_format($date,"Y-m-d"));
        $nextBillingDate = date("Y-m-d", strtotime("+1 month", $time));

        $responce =array(
            'profile_id' =>$res['PROFILEID'],
            'profile_status' =>$recurringProfileDetails['STATUS'],
            'email' => $paypalPayment->email,
            'final_payment_date' =>$nextBillingDate,
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
    
        
    public function getProfileData($profileIs){
        $paypal = new DPayPal();
        $profileData['PROFILEID'] = $profileIs;
        $profileData['METHOD'] = 'GetRecurringPaymentsProfileDetails';
        return  $paypal->GetRecurringPaymentsProfileDetails($profileData);
        
    }
   

}

?>