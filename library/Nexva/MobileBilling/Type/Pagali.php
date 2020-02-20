<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 7/9/14
 * Time: 4:36 PM
 * To change this template use File | Settings | File Templates.
 */

class Nexva_MobileBilling_Type_Pagali extends Nexva_MobileBilling_Type_Abstract
{

    public function __construct()
    { 
        

        //include_once( APPLICATION_PATH . '/../public/vendors/Nusoap/lib/nusoap.php' );
    }

    public function doPayment($chapId, $buildId, $appId, $mobileNo, $appName, $price)
    {
        //Get currency rate and code relevant to the CHAP
        $currencyUserModel = new Api_Model_CurrencyUsers();
        $currencyDetails = $currencyUserModel->getCurrencyDetailsByChap($chapId);
        $currencyRate = $currencyDetails['rate'];
        $currencyCode = $currencyDetails['code'];
        $sessionId = Zend_Session::getId();
        $amount = ceil($currencyRate * $price);
        $paymentTimeStamp = date('d-m-Y');

        $url = 'https://www.pagali.cv/pagali/index.php?r=pgPaymentInterface/ecommercePayment';

        $data = array(
            'id_ent'    =>  'E46ECE05-2971-31F3-39BD-819B8D503772',
            'id_temp'   =>  'DA624A65-C6C9-EECE-C2FD-1DED96531A40',
            'order_id'  =>   $appId.'nexva'.$sessionId,
            'currency_code' => 'CVE',
            'return'    =>  'http://api.nexva.com/testone/cb',
            'notify'    =>  'http://api.nexva.com/testone/cb',
            'total'     =>  $amount,
            'item_name' =>  $appName,
            'quantity'  =>  '1',
            'item_number'   => $appId,              //optional parameter
            'amount'    =>  $amount,
            'total_item'    =>  '1'
        );
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
            <form name="redirectpost" method="post" action="<?php echo $url; ?>">
                <?php
                    if ( !is_null($data) ) {
                        foreach ($data as $k => $v) {
                            echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
                        }
                    }
                ?>
            </form>
            </body>
            </html>
        <?php

        $response = '';     //response from the pg
        $buildUrl = '';

        if($response)       //
        {
            $error = $response->getError();

            if(!$error)     //
            {
                //Get the S3 URL of the Relevant build
                $productDownloadCls = new Nexva_Api_ProductDownload();
                $buildUrl = $productDownloadCls->getBuildFileUrl($appId, $buildId);

                //todo, change message language
                //$message = 'Y\'ello, vous avez ete facture '. $amount.' '.$currencyCode. ' le '.$paymentTimeStamp. ' pour un telechargement depuis  MTN app-store. Merci.';
                $message = '';
                //$this->sendsms($mobileNo, $message, $chapId);

                $paymentResult = 'Success';
                $paymentTransId = strtotime($paymentTimeStamp);

                //Update the relevant Transaction record in the DB
                parent::UpdateMobilePayment($paymentTimeStamp, $paymentTransId, $paymentResult, $buildUrl);

                return $buildUrl;
            }
        }
    }
}