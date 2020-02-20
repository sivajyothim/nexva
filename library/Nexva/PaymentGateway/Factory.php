<?php

/**
 * return the instance of the payment method
 * @param  string $option {PayThru,PayThruSandBox,PayThruRepeat,Paypal}
 * @return string $paymentMethod {'Paythru' ,'Paypal'}
 * Chathura Jayasekara
 */
class Nexva_PaymentGateway_Factory {

  public static function factory($paymentMethod, $option, $sandbox=0) {


    $serviceAdapter = 'Nexva_PaymentGateway_Adapter_' . $paymentMethod . '_' . $option;


    return call_user_func(array($serviceAdapter, 'getInstance'), $sandbox);
  }

}