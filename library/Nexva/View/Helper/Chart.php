<?php
/**
 * A view helper to generate/display QR codes
 *
 * @author jahufar
 * @version $id$
 *
 */

class Nexva_View_Helper_Chart extends Zend_View_Helper_Abstract {


    public function chart(array $params)
    {

        $chartparam = NULL;
        foreach($params as $key=>$value){

            $chartparam .= $key."=".rawurlencode($value)."&amp;";
        }
        //url encoded params will be popultated after charts?
        return "http://chart.apis.google.com/chart?$chartparam";
    }

}



?>