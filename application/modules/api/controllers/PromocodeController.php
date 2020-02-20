<?php
class Api_PromocodeController extends Zend_Controller_Action {


    public function init() {
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender (true);
    }

    public function redeemAction() {

        $productId = $this->_request->id;
        $promoCode = $this->_request->code;
        $simulate = $this->_request->simulate;
        
        if( '' == trim($promoCode) ) {
            $this->echoJson(
                array (
                    "status" => -1,
                    "message" => "Error: No code was specified"
                )
            );
                       
        }

        if( '' == trim($productId) ) {
            $this->echoJson(
                array (
                    "status" => -1,
                    "message" => "Error: No app id was specified"
                )
            );

        }

        switch (strtoupper($simulate))  {
            case 'SUCCESS':
               $this->echoJson(
                    array (
                        "status" => 0,
                        'message' => "Code ". $promoCode." was redeemed successfully"
                    )
                );

                break;

            case 'FAIL':
                $this->echoJson(
                    array (
                        "status" => -1,
                        'message' => "Unable to redeem code". $promoCode
                    )
                );

                break;
        }


        try {
            $promoCodeType = Nexva_PromotionCode_Factory::getPromotionCodeType($promoCode);
        } catch (Exception $e) {
            $this->echoJson(
                array (
                    "status" => -1,
                    'message' => "Unable to redeem code ". $promoCode. ". The promotion code does not exist"
                )
            );
        }


               
        $promoCodeLib     = new Nexva_PromotionCode_PromotionCode();

        $promoCodeLib->applyCode($promoCode);

        $valid  = $promoCodeLib->checkPromotiocodeValidityForProduct($productId, $promoCodeLib->getAppliedCode());

        if( !$valid ) {
            $this->echoJson(
                array (
                    "status" => -1,
                    'message' => "Unable to redeem code ". $promoCode. ". The promo code is not valid for the app ID specified."
                )
            );
        }

        $promoCodeType->doPostProcess($productId);

       $this->echoJson(
            array (
                "status" => 0,
                'message' => "Code ". $promoCode." was redeemed successfully"
            )
        );


    }

    public function indexAction () {
        $this->_request->setActionName('docs');
        $this->_helper->viewRenderer->setNoRender (false);
    }

    protected function echoJson($json, $halt=1) {
        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ;

        echo json_encode($json);
        if( $halt ) die();

    }

}
?>
