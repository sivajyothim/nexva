<?php
/**
 * A view helper to generate/display QR codes
 *
 * @author jahufar
 * @version $id$
 *
 */

class Nexva_View_Helper_Qr extends Zend_View_Helper_Abstract {

    public function qr($url, $height='125', $width='125')
    {
        return "http://chart.apis.google.com/chart?cht=qr&chs={$height}x{$width}&chl=$url";
    }

}

?>