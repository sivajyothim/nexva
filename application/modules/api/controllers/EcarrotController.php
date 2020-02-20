<?php

class Api_EcarrotController extends Zend_Controller_Action {

    private $email;
    private $ecarrotUserId;
    private $verificationStatus;
    private $mobileNumber;
    private $varificationCode;
    private $userId;

//    public function init() {
//        parent::init();
//        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
//        $this->view->flashMessages = $this->_flashMessenger->getMessages();
//    }

    public function createUserAction() {

        if ($this->_request->isPost()) {

            if (isset($this->_request->email) && !empty($this->_request->email)) {
                $this->email = $this->_request->email;
            } else {
                return json_encode(array(
                    "message" => "The Email doesn't exsist."
                ));
            }

            if (isset($this->_request->ecarrot_user_id) && !empty($this->_request->ecarrot_user_id)) {
                if (is_numeric($this->_request->ecarrot_user_id)) {
                    $this->ecarrotUserId = $this->_request->ecarrot_user_id;
                } else {
                    return json_encode(array(
                        "message" => "The ecarrot_user_id should be integer."
                    ));
                }
            } else {
                return json_encode(array(
                    "message" => "The ecarrot_user_id doesn't exsist."
                ));
            }

            if (isset($this->_request->verification_status)) {
                $this->verificationStatus = $this->_request->verification_status;
            } else {
                return json_encode(array(
                    "message" => "The verification_status doesn't exsist."
                ));
            }

            if (isset($this->_request->mobile_number) && !empty($this->_request->mobile_number)) {

                $this->mobileNumber = $this->_request->mobile_number;
            } else {
                return json_encode(array(
                    "message" => "The mobile_number doesn't exsist."
                ));
            }

            if (isset($this->_request->varification_code) && !empty($this->_request->varification_code)) {

                $this->varificationCode = $this->_request->varification_code;
            } else {
                return json_encode(array(
                    "message" => "The varification_code doesn't exsist."
                ));
            }

            $data = array(
                'email' => $this->email,
                'ecarrot_user_id' => $this->ecarrotUserId,
                'verification_status' => $this->verificationStatus,
                'mobile_number' => $this->mobileNumber,
                'varification_code' => $this->varificationCode,
                'started_date' => NULL,
                'varification_date' => date('Y-m-d')
            );

            try {
                $ecarrotUserModel = new Api_Model_EcarrotUser();
                $result = $ecarrotUserModel->insert($data);
            } catch (Exception $e) {
                var_dump($e->getMessage());
                die();
            }
            if ($result) {
                die(json_encode(array('massage' => 'User created successfully.')));
            } else {
                die(json_encode(array('error' => 'Error occured.')));
            }
        } else {
            die(json_encode(array('error' => 'Cannot access.')));
        }
    }

    public function checkverificationStatusAction() {

        if ($this->_request->isPost()) {



            if (isset($this->_request->ecarrot_user_id) && !empty($this->_request->ecarrot_user_id)) {
                if (is_numeric($this->_request->ecarrot_user_id)) {
                    $this->ecarrotUserId = $this->_request->ecarrot_user_id;
                } else {
                    return json_encode(array(
                        "message" => "The ecarrot_user_id should be integer."
                    ));
                }
            } else {
                return json_encode(array(
                    "message" => "The ecarrot_user_id doesn't exsist."
                ));
            }

            $ecarrotUserModel = new Api_Model_EcarrotUser();
            $userInfo = $ecarrotUserModel->getUserstatus($this->ecarrotUserId);

            if ($userInfo) {

                $response = array(
                    'user' => $userInfo->ecarrot_user_id,
                    'verification' => $userInfo->verification_status,
                    'mobile_no' => $userInfo->mobile_no,
                    'varification_date' => $userInfo->varification_dat
                );

                echo json_encode($response);
            } else {

                json_encode(array(
                    "message" => "The ecarrot_user_id doesn't exsist."
                ));
            }
        }
    }

    public function smsAction() {

        if ($this->_request->isPost()) {

            if (isset($this->_request->mobile_number) && !empty($this->_request->mobile_number)) {

                $this->mobileNumber = $this->_request->mobile_number;
            } else {
                return json_encode(array(
                    "message" => "The mobile_number doesn't exsist."
                ));
            }



            $rand_number = rand(1000, 9999);
            $cSession = curl_init();
            curl_setopt($cSession, CURLOPT_URL, "https://rest.messagebird.com/messages?access_key=iO5Ys42xXLnWx48Ev1bXxZeNx");
            curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cSession, CURLOPT_POST, 1);
            curl_setopt($cSession, CURLOPT_POSTFIELDS, "recipients=" . $this->mobileNumber . "&originator=eCarrot&body=" . $rand_number);
            curl_setopt($cSession, CURLOPT_HEADER, false);
            curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($cSession);
            curl_close($cSession);
            $resultArray = json_decode($result);
            $item = $resultArray->recipients->items;

            if (($item[0]->status == 'sent') && (isset($item[0]->status))) {
                die(json_encode(array('status' => 'success', 'token' => $rand_number)));
            } else {
                die(json_encode(array('status' => 'fail', 'token' => '')));
            }
        } else {
            die(json_encode(array('error' => 'Cannot access.')));
        }
    }

    public function updateUserAction() {

        if ($this->_request->isPost()) {

            if (isset($this->_request->user_id) && !empty($this->_request->user_id)) {
                $this->userId = $this->_request->user_id;
            } else {
                return json_encode(array(
                    "message" => "The user_id doesn't exsist."
                ));
            }


            try {

                $ecarrotUserModel = new Api_Model_EcarrotUser();
                $ecarrotUserModel->updateEcarrotUser($this->userId);
                die(json_encode(array('massage' => 'User updated successfully.')));
            } catch (Exception $e) {
                die(json_encode(array('error' => $e->getMessage())));
            }
        } else {
            die(json_encode(array('error' => 'Cannot access.')));
        }
    }

    public function getUserStatusAction() {



        if (isset($this->_request->user_id) && !empty($this->_request->user_id)) {
            $this->userId = $this->_request->user_id;
        } else {
            return json_encode(array(
                "message" => "The user_id doesn't exsist."
            ));
        }


        try {

            $ecarrotUserModel = new Api_Model_EcarrotUser();
            $result = $ecarrotUserModel->getUserstatus($this->userId);

            die(json_encode($result));
        } catch (Exception $e) {
            die(json_encode(array('error' => $e->getMessage())));
        }
    }

    public function getPaymentStatusAction() {


        if (isset($this->_request->user_id) && !empty($this->_request->user_id)) {
            $this->userId = $this->_request->user_id;
        } else {
            return json_encode(array(
                "message" => "The user_id doesn't exsist."
            ));
        }


        try {

            $ecarrotUserModel = new Api_Model_EcarrotRecurringPayment();
            $result = $ecarrotUserModel->getPaymentDate($this->userId);

            if ($result) {
                die(json_encode($result));
            } else {
                $ecarrotUserModel = new Api_Model_EcarrotUser();
                $userData = $ecarrotUserModel->getUserstatus($this->userId);
                die(json_encode(array("final_payment_date" => $userData['varification_date'])));
            }
        } catch (Exception $e) {
            die(json_encode(array('error' => $e->getMessage())));
        }
    }

    public function successAction() {
        die();
    }

    public function cancelAction() {
        die();
    }

    public function sendNotificationsAction() {
        try {
            /* Get all unpaid varified users */
            $ecarrotUser = new Api_Model_EcarrotUser();
            $unpaidVarifiedUsers = $ecarrotUser->getUnpaidVarifiedUsers();

            foreach ($unpaidVarifiedUsers as $unpaidVarifiedUser) {
                $startedDate = $unpaidVarifiedUser->varification_date;
                $dateObj=date_create($startedDate);
                date_add($dateObj, date_interval_create_from_date_string("14 days"));
                $finalDate = date_format($dateObj, "Y-m-d");                
                $finalActiveDate = date_create($finalDate);
                $currentDate = date_create(date('Y-m-d'));
                $diff = date_diff($currentDate,$finalActiveDate);
                $date_Diff = $diff->format("%R%a");
                $email = $unpaidVarifiedUser->email;
                $remaingDate = $date_Diff;
              
                $this->sendNotification($remaingDate, 'TRAIL', $email);
            }
            /* End */

            /* Get all paid paid users */
            $ecrrotRecuObj = new Api_Model_EcarrotRecurringPayment();
            $paidUsers = $ecrrotRecuObj->getPaidUsers();

            foreach ($paidUsers as $paidUser) {
                
                $finalpaymentdatee = $paidUser->final_payment_date;
                $currentDate = date_create(date('Y-m-d'));
                $diff = date_diff($currentDate,date_create($finalpaymentdatee));
                $date_Diff = $diff->format("%R%a");
                $email = $paidUser->email;                
                $this->sendNotification($date_Diff, 'PAIDUSER', $email);
            }
            die(json_encode(array("message" => "Send notifications successfully..", "status" => "SUCCESS")));
            /* End */
        } catch (Exception $e) {
            die(json_encode(array("message" => $e->getMessage(), "status" => "FAILD")));
        }
    }

    private function sendNotification($date_Diff, $mode, $email) {


        if (($date_Diff <= 3) && ($date_Diff >= 0)) {
            
            /* BEFORE EXPIRE */
            if ($mode == 'PAIDUSER') {
                $this->sendEmail($email,$mode,  $this->removeDateSign($date_Diff));
            } else {               
                $this->sendEmail($email,$mode,  $this->removeDateSign($date_Diff));
            }
        } elseif ($date_Diff == 0) {
            /* EXPIRE TODAY */
            if ($mode == 'PAIDUSER') {
                $this->sendEmail($email,$mode,  $this->removeDateSign($date_Diff));
            } else {
                $this->sendEmail($email,$mode,  $this->removeDateSign($date_Diff));
            }
        } elseif ($date_Diff == -1) {
            /* AFTER EXPIRED */
            if ($mode == 'PAIDUSER') {
                $this->sendEmail($email,$mode,  $this->removeDateSign($date_Diff));
            } else {
                $this->sendEmail($email,$mode,  $this->removeDateSign($date_Diff));
            }
        }
    }

  
    private function sendEmail($email,$mode, $dateDiff) {
        
        $template = 'ecarrot_trail_user_notification.phtml';
        if ($mode == 'PAIDUSER') {
            $template = 'ecarrot_paid_user_notification.phtml';
        }
        $mailer = new Nexva_Util_Mailer_Mailer();
        $mailer->setSubject('Ecarrot - Subcription notification');
        $mailer->addTo($email)
                ->setLayout("generic_mail_template")              
                ->setMailVar("email", $email)
                ->setMailVar("date_Diff", $dateDiff)
                ->sendHTMLMail($template);
    }
    
    private function removeDateSign($date){
        $day = 0;
        switch($date){
            case '+3':
                $day = 3;
              break;
            case '+2':
                $day = 2;
              break;
            case '+1':
                $day = 1;
              break;
            case '+0':
                $day = 0;
              break;
            case '-1':
                $day = -1;
              break;
            
        }
            return $day;
    }

}
