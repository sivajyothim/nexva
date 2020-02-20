<?php
/*
 * 
 * 
 *
*/

interface Nexva_PaymentGateway_Interface_Interface {
	public function getAdapterName();
    public function setServiceEndPoint($url);
    public function addVar($name, $value);
    public function prepare($vars = array());
    public function execute();
}

