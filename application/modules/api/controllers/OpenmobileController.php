<?php

class Api_OpenmobileController extends Zend_Controller_Action {

    protected $serverPathThumb = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=";
    protected $serverPath = "http://thor.nexva.com/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/";
    protected $loggerInstance;

    
    public function init() {
        
        $this->userId =  23142;
        return;
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $this->userId = $this->_request->user_id;
        $hash =  $this->_request->auth_hash;
       
        $secretKey = 'sDhsaf32h9';
        
        $data = json_encode(array('user_id' => (int) $this->userId));
        $userHash = hash_hmac('sha256', $data, $secretKey);
        
        if(empty($this->userId)) {
            
            $this->getResponse()->setHeader('Content-type', 'text/xml');
            echo "<?xml version=\"1.0\"?><error-message> In-valid Request!.  User Id is empty.</error-message>";
            die();
            
            
        }
        
        if(empty($hash)) {
        
        	$this->getResponse()->setHeader('Content-type', 'text/xml');
        	echo "<?xml version=\"1.0\"?><error-message> In-valid Request!.  Auth Hash is empty.</error-message>";
        	die();
        
        
        }
        

        if($hash == $userHash) {
            
        } else {
            $this->getResponse()->setHeader('Content-type', 'text/xml');
            echo "<?xml version=\"1.0\"?><error-message> In-valid Request!.  Authentication failed.</error-message>";
            die();
        }
        
  

    }

  
    public function indexAction() 
    {

        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    	
    	$limit = $this->_getParam('limit', 10);
        $offset = $this->_getParam('offset', 0);
        
        if($limit > 20)	{
        	$this->getResponse()->setHeader('Content-type', 'text/xml');
        	echo "<?xml version=\"1.0\"?><error-message> In-valid Request!.  Maximum allowed limit is 10.</error-message>";
        	die();
        }
    	
    	$xmlBody = '';
        
   
        
        $products = new Api_Model_Products();   
       // $productsAll =  $products->getCompatibleAndroidProducts();
        
        $chapProducts = new Api_Model_ChapProducts();
        $productsAll =  $chapProducts->myOpenMobileApps($this->userId);
        
         
        $productList = Zend_Paginator::factory($productsAll);
        $productList->setItemCountPerPage(20);
        $productList->setCurrentPageNumber($offset);
            
        $statisticDownload = new Model_StatisticDownload();
        $userMeta   = new Model_UserMeta();
        $productMeta = new Model_ProductMeta();
        $productImagesModel = new Model_ProductImages();
        $productDownloadCls = new Nexva_Api_ProductDownload();
        $productCategories = new Model_ProductCategories();
        
        $productImages = new Nexva_View_Helper_ProductImages();
        $serverPathImage = $productImages->productImages() . '/vendors/phpThumb/phpThumb.php?src=/product_visuals/production/';
         
        $imagethumbPostFix = htmlentities("&w=80&h=80&aoe=0&fltr[]=ric|0|0&q=100&f=png");
        
        //Zend_Debug::dump($productList);
        
        $xmlBody = "<?xml version=\"1.0\"?>";
        $xmlBody .=	"<products>";
        

    	
    	foreach($productList as $list)	{
    		$authorDetails = '';
    		$downloadCount = '';
    		$companyName = '';
    		$keywords = '';
    		$updateDate = '';
    		$catergory = '';
    		$productName = '';
    		
    		$downloadCount = $statisticDownload->getAllDownloadsCount($list->id);

    		$userMeta->setEntityId($list->user_id);


    		
    		
    		$companyName = $userMeta->COMPANY_NAME;
    		if(!isset($companyName))    {
    		    $companyOwnerFname = $userMeta->FIRST_NAME;
    		    $companyOwnerLname = $userMeta->LAST_NAME;
    		    
    		    $companyName = $companyOwnerFname . ' '.$companyOwnerLname;
    		}
    		
    		 $productMeta->setEntityId($list->id);
    		 
    		 $productImagesList = $productImagesModel->getAllImages($list->id);
    	
    		 $catergory = $productCategories->selectedParentCategoryName($list->id);
    		 
    	
    		if(empty($list->build_created_date))
    		    $updateDate = $list->created_date;
            else
                $updateDate = $list->build_created_date;
    	
    		$type = ($list->price > 0) ? 'Commercial' : 'Non-commercial' ;
    		$productName = preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '',$list->name); 
    		
    	$abc = 0;

            $xmlBody .=	"<product id='$list->id' code=''>
                             <cp_product_id>$list->id</cp_product_id>
                             <product_name>".$productName."</product_name>";
            
            if(isset($catergory->id))	{
                $xmlBody .="<category id='$catergory->id' code=''>".$this->_xmlentities($catergory->name)."</category>";
            }
            
                         $xmlBody .="<release_date>$list->created_date</release_date>
                             <downloads_count>$downloadCount->download_count</downloads_count>
    		                 <author>".preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '', $companyName)."</author>
		                     <author_email>$list->email</author_email>
		                     <version>$productMeta->PRODUCT_VERSION</version>
            	             <requirements>Android OS</requirements>";
            	             
            	             $string = '';
            	             $string = preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '', $productMeta->BRIEF_DESCRIPTION );
            	             
            	             $xmlBody .= "<short_description>".$string."</short_description>
            	             <price>$list->price</price>
		                     <currency>USD</currency>";
            	                     	            
            	             $string = '';
            	             
            	             	
    

            	             $string = preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '',$productMeta->FULL_DESCRIPTION);   
		                     $xmlBody .= "<long_description>".$string."</long_description>
		                     <type>$type</type>
		                     <update_date> $updateDate </update_date>
		                     <keywords>";
            				
                             
                             $keywords = explode(',', $list->keywords);
                             
                             if(is_array($keywords))	{
                             foreach($keywords as $keyword) 
                                $xmlBody .= "<keyword>".preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '',$keyword)."</keyword>";
                             }    else    {
                             	
                             	$xmlBody .= $xmlBody;
                             }
                             
                             $xmlBody .= "</keywords>";
                             
                             error_reporting(error_reporting() & ~E_STRICT);
                      
                             
                             $xmlBody .=  "<images> 
                             			       <thumb>".$serverPathImage.$list->thumbnail.$imagethumbPostFix."</thumb>";

                             if(is_object($productImagesList))	{
                             		foreach($productImagesList as $image)	
                                       $xmlBody .=  "<image>".$serverPathImage.$image->filename."</image>";
                             }
                                       
                             $xmlBody .=  "</images>"; 
                             
                             $xmlBody .= "<builds>";
                             $xmlBody .= "<build id='$list->build_id'>";
                             $xmlBody .= "<name>".preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '',$list->build_name)."</name>";
			                 $xmlBody .= "<platform>Android-all</platform>";
			              // $xmlBody .= "<installer_type>ota</installer_type>";
			              // $xmlBody .= "<package_name>com.mobisystems.editor.office_registered</package_name>";
                          // $xmlBody .= "<version_name></version_name>";
			              // $xmlBody .= "<version_code></version_code>";
			              

			             
			              // Get the S3 URL of the Relevant build
			          
                             $buildUrl = $productDownloadCls->getBuildFileUrl( $list->id, $list->build_id);
                              //   Zend_Debug::dump($buildUrl);
    			                 

			                 $xmlBody .= "<file>".htmlentities($buildUrl)."</file>";
			                 $xmlBody .= "<productpage>http://www.nexva.com/app/".$this->view->slug($list->name) . "." . $list->id."</productpage>";
			                 
                             $xmlBody .= "<languages>en</languages>";  
		                     $xmlBody .= "</build>";
                             $xmlBody .= "</builds>";
                       

		$abc++;
            $xmlBody .= "</product>";
		
    	}
    	
    	$xmlBody .=	"</products>";
    	

        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=utf-8');
        $productsCount = count($list);
        $productsAllResultsSet  = $chapProducts->myOpenMobileNoApps(23142);
     
        //if(count($productList) > 0)
        if((($limit * ($offset-1)) - $productsCount) <= $productsAllResultsSet) 
            echo $xmlBody;
        else 
          echo "<?xml version=\"1.0\"?><products></products>";
    	

    }
    
    private function _filterInputCharacters($string)	
    {
    	
    	return preg_replace('~[^A-Za-z0-9\+\- \'"\.]~', '', $string);
    }
    
    
    private function _htmlentities2unicodeentities ($input) {
  	
        $htmlEntities = array_values (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
        $entitiesDecoded = array_keys   (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
        $num = count ($entitiesDecoded);
        for ($u = 0; $u < $num; $u++) {
            $utf8Entities[$u] = '&#'.ord($entitiesDecoded[$u]).';';
        }
        
        return str_replace ($htmlEntities, $utf8Entities, $input);
  
    } 

    private function _xmlentities($string, $quote_style=ENT_QUOTES)
    {
       static $trans;
       if (!isset($trans)) {
           $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
           foreach ($trans as $key => $value)
           $trans[$key] = '&#'.ord($key).';';
          // dont translate the '&' in case it is part of &xxx;
          $trans[chr(38)] = '&';
       }
       // after the initial translation, _do_ map standalone '&' into '&#38;'
       return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,3};)/","&#38;" , strtr($string, $trans));
       
       
       ///this is just for referance 
          // $string = strtr($productMeta->FULL_DESCRIPTION, "����������������������������������������������������", "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn'");

                            // Remove all remaining other unknown characters
                            // $string = preg_replace('/[^a-zA-Z0-9\-]/', ' ', $string);
                            // $string = preg_replace('/^[\-]+/', '', $string);
                            // $string = preg_replace('/[\-]+$/', '', $string);
                            // $string = preg_replace('/[\-]{2,}/', ' ', $string);
       
    }
    
    

    
}
