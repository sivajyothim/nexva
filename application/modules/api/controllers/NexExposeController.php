<?php

class Api_NexExposeController extends Zend_Controller_Action
{
    
    public function init()
    {
        //Disabling the layput and views
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }
    
    /**
     * 
     * Returns the set of applications based on Device and Chap with a page limit   
     * 
     * @param User-Agent (HTTP request headers) User Agent of the client device
     * @param Chap-Id Chap ID (HTTP request headers)
     * @param page Page number (GET) Optional
     * @param limit App limit (GET) Optional
     * returns JSON encoded $downloadLink
     */
    public function allAppsAction()
    {
        //Get all HTTP request headers, this is an associative array
        $headersParams = apache_request_headers();
        
        //We need only User-Agent, Chap-Id
        $userAgent =  !empty($headersParams['User-Agent'])? empty($headersParams['User-Agent']) : '' ;
        $chapId =  !empty($headersParams['Chap-Id']) ? empty($headersParams['Chap-Id']) : '' ;
        
        //Get the parameters
        //$userAgent = trim($this->_getParam('ua', "HTC_Touch_HD_T8282 Opera/9.50 (Windows NT 5.1; U; en)"));         
        //$chapId = trim($this->_getParam('chap',5981));
        $offset = trim($this->_getParam('page', 0));
        $limit = trim($this->_getParam('limit', 10));
        //$chapId = 5981;
        
        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
        
        if($chapId === null || empty($chapId))
        {
            echo json_encode(array("message" => "Chap Id not found" , "error_code" => "01"));
            exit;
        }
        
        //Check if the device was detected or not, if not retrun a message as below
        if($deviceId === null || empty($deviceId))
        {
            echo json_encode(array("message" => "Device not found" , "error_code" => "02"));
            exit;
        }
        else //Get the Apps based on Chap and the Device
        {
            //Insttiate the Api_Model_ChapProducts model
            $chapProductModel = new Api_Model_ChapProducts();

            //get the app details
            $apps = $chapProductModel->getChapProductsAll($chapId, $deviceId, $limit, $offset);

            $appsArray = array();
            
            
            //This is to add the thumbnail path to the value
            foreach($apps as $key => $value)
            {
                if(!empty($value["thumbnail"]))
                {
                    $value["thumbnail"] = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/".$value["thumbnail"]."&w=50&h=70&aoe=0&fltr[]=ric|0|0&q=100&f=png";
                }
                
                $appsArray[$key] = $value;                
            }
                        
            //enocde using JSON
            if (count($apps) > 0)
            {
                //$apps = $this->_helper->json($apps); 
                $apps = str_replace('\/', '/', json_encode($appsArray));                
                echo $apps;
                //var_dump(json_decode($apps, true));
            }
            else
            {
                echo json_encode(array("message" => "Data Not found", "error_code" => "03"));
            }     
        }                
               
    }       
    
    
    /**
     * 
     * Returns the details of a particular app     
     * 
     * @param $userAgent User Agent (GET)
     * @param $chapID Chap ID (GET)
     * @param $appId App ID (GET)
     * returns JSON encoded $downloadLink
     */
    public function detailsAppAction()
    {
        $userAgent = $this->_getParam('ua', "HTC_Touch_HD_T8282 Opera/9.50 (Windows NT 5.1; U; en)");         
        $chapId = $this->_getParam('chap',5981);
        $appId = $this->_getParam('app_id',13850);
        
        //ToDO
        //Check if the app exist for CHAP
        //
        
        
        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
        
        //Check if the device was detected or not, if not retrun a message as below
        if($deviceId === null || empty($deviceId))
        {
            echo json_encode(array("message" => "No device found"));
            exit;
        }
        else
        {
            //Instantiate the Default Product model
            $productModel = new Model_Product ();
            
            $lightMode = true;
             
            //Get the prdocut details of the app
            $product = $productModel->getProductDetailsById($appId, $lightMode);
            
            //Zend_Debug::dump($product);die();
   
            unset($product['device_selection_type'],$product['thumb_name'],$product['uid'],
                    $product['user_meta'],$product['registration_model'],$product['status'],
                    $product['platform_id'],$product['platform_name'],$product['supported_platforms'],
                    $product['created'],$product['changed'],$product['categories'],$product['deleted']);
                      
            
            $apps = json_encode($product);
            //Zend_Debug::dump(json_decode($apps, true));

        }
       
    }
    
    
    
    /**
     * 
     * Returns the download url of the app. This is a direct link to the app file on S3 server wrapped by the 
     * relevent parameters (AWSAccessKeyId,Expires,Signature).
     * 
     * @param $userAgent User Agent (GET)
     * @param $chapID Chap ID (GET)
     * @param $appId App ID (GET)
     * returns JSON encoded $downloadLink
     */
    public function downloadAppAction()
    {
        $userAgent = $this->_getParam('ua', "HTC_Touch_HD_T8282 Opera/9.50 (Windows NT 5.1; U; en)");         
        $chapId = $this->_getParam('chap',5981);
        $appId = $this->_getParam('app_id',14107);
        
        //ToDO
        //Check if the app exist for CHAP
        //
        
        
        //Detect the device id from thd db according to the given user agent
        $deviceId = $this->deviceAction($userAgent);
        
        //Check if the device was detected or not, if not retrun a message as below
        if($deviceId === null || empty($deviceId))
        {
            echo json_encode(array("message" => "No device found"));
            exit;
        }
        else
        {
            //Instantiate the Default Product model
            $productModel = new Model_Product ();

            //Fetch the S3 app URL to be downloaded
            //$url = $productModel->downloadProduct($appId);

            $url = "http://s3.amazonaws.com/staging.applications.nexva.com/productfile/14140/kahramanlarasm.apk?AWSAccessKeyId=AKIAIB7MH7NAQK55BKOQ&Expires=1338204385&Signature=DEefS7HplZa1vW2sVrtlwiXY6VY%3D";
               
            $downloadLink = array();
            $downloadLink['download_app'] = $url;       
            
            
            //Here str_replace has been used to prevent of adding '/' when ecnoded by JSON
            //thre is a solution but ,JSON_UNESCAPED_UNICODE is only works in PHP 5.4+, http://php.net/manual/en/function.json-encode.php
            $encodedDownloadLink =  str_replace('\/', '/', json_encode($downloadLink));
            
            echo $encodedDownloadLink;

        }
       
    }
    
    
     /**
     * 
     * Returns the deviced id from the DB based on the User Agent.
     * @param $userAgent User Agent
     * returns $deviceId
     */
    public function deviceAction($userAgent)
    {
            
        //Iniate device detection using Device detection adapter
        $deviceDetector = Nexva_DeviceDetection_Adapter_TeraWurfl::getInstance();           
        
        //Detect the device
        $exactMatch     = $deviceDetector->detectDeviceByUserAgent($userAgent);       
        
        //Device barand name
        $brandName = $deviceDetector->getDeviceAttribute('brand_name', 'product_info'); 
        
        //Get the Device ID of nexva db
        $deviceId = $deviceDetector->getNexvaDeviceId();
        
        return $deviceId;
    }
    
    
    
}

