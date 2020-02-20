<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 10/30/13
 * Time: 6:39 PM
 * To change this template use File | Settings | File Templates.
 */

class Partnermobile_ApkController extends  Nexva_Controller_Action_Partnermobile_MasterController {


    public function init() 
    {      
        parent::init(); 

            
    }
    
    public function indexAction(){
    

    
        $latestBuildUrl = null;
        $themeMeta   = new Model_ThemeMeta();
        $themeMeta->setEntityId($this->_chapId);
        

    
        //Get the S3 URL of the Relevant build
        $productDownloadCls = new Nexva_Api_ProductDownload();
    
        $buildUrl = null;
    
        if($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID && $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID){
            $buildUrl = $productDownloadCls->getBuildFileUrl($themeMeta->WHITELABLE_SITE_APPSTORE_APP_ID, $themeMeta->WHITELABLE_SITE_APPSTORE_BUILD_ID);
        }
    
        //echo $buildUrl; die();
        if($buildUrl){
            $this->_redirect($buildUrl);
        }
    }

    
    
}