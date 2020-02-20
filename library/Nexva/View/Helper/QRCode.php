<?php
/**
 * A view helper to generate/display QR codes
 *
 * @author jahufar
 * @version $id$
 *
 */

class Nexva_View_Helper_QRCode extends Zend_View_Helper_Abstract {
    
    public function qrcode($url, $height='125', $width='125')
    {
        return "http://chart.apis.google.com/chart?cht=qr&chs={$height}x{$width}&chl=$url";      
    }

}

?>