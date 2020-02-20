<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Interface
 *
 * @author jahufar
 */
interface Nexva_DeviceDetection_Interface_DetectionInterface {
   
    public function detectDeviceByUserAgent($userAgent = null);

    public function getDeviceImage($userAgent=null);

    public function getDeviceAttribute($attributeName, $groupName=null);

    public function getAdapterName();

    public function getNexvaDeviceId();
   
}
?>
