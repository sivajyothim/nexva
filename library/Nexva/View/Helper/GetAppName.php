<?php
/**
 * A view helper to generate/display QR codes
 *
 * @author jahufar
 * @version $id$
 *
 */

class Nexva_View_Helper_GetAppName extends Zend_View_Helper_Abstract {


    public function getAppName($appId)
    {
        $product= new Admin_Model_Product();
        $result=$product->getAppName($appId);
        $productName="";
        if($result->name){
            $productName=$result->name;
        }
        return $productName;
    }

}



?>