<?php

	
class Partner_SearchController extends Nexva_Controller_Action_Partner_MasterController 
{

    public function init()
    {
    	parent::init();

        
    }
	
   public function indexAction()
   {

        $searchQry = '';
        if (! empty ( $this->_request->q )) {
            $this->view->headTitle()->append ( 'Search - ' . $this->view->escape ( $this->_request->q ) );
            $this->view->headMeta()->appendName ( 'keywords', 'neXva, nexva.com, ' . $this->view->escape ( $this->_request->q ) );
        } else {
            $this->view->headTitle()->append ( 'Search' );
            $this->view->headMeta()->appendName ( 'keywords', 'neXva, nexva.com' );
        }

        $this->view->pageName =   $this->view->escape($this->_request->q);

       $deviceId = $this->getDeviceId();

       //echo Zend_Registry::get('config')->nexva->application->search->sphinx->enable;die();

        $keywords   = trim ($this->_request->q);
        $products   = array();
        if (!empty($keywords)) {
            $productsModel  = new Model_Product();

            if ((Zend_Registry::get('config')->nexva->application->search->sphinx->enable)) {

                //if the devices are selected use the normal DB search else use sphinx
                if ($deviceId){
                    //$searchQry = $productsModel->getSearchQueryChap($keywords, $tmpDeviceId, false, $this->_chapId );
                    //$searchQry = $productsModel->getSearchQueryPartnerWithDevice($keywords,$deviceId, false, $this->_chapId);
                    //$products = $productsModel->getSearchQueryPartnerWithDevice( $keywords,$deviceId, false, $this->_chapId, 10, true );
                    $searchQry = array();
                    //Get the records from product_language_meta 
                    if($this->_chapLanguageId != 1):
                        //echo 'inside other lang';
                        $searchQry = $productsModel->getSearchQueryPartnerWithDeviceProductLangMeta( $keywords,$deviceId, false, $this->_chapId, NULL, NULL, $this->_chapLanguageId );
                    endif;

                    //If 
                    if(count($searchQry) < 1):
                        //echo 'inside english';
                        $searchQry = $productsModel->getSearchQueryPartnerWithDevice( $keywords,$deviceId, false, $this->_chapId, NULL, NULL );
                    endif;

                
                } else {
                    $sphinx = new Nexva_Util_Sphinx_Sphinx ( );
                    $results  = $sphinx->searchChapProducts( $keywords, SPH_MATCH_PHRASE, $this->_chapId);
                    //$results = $productsModel->getSearchQueryPartner( $keywords, SPH_MATCH_PHRASE, $this->_chapId);
                    //Zend::debug($results);die();
                    if ($results === false) {
                        // for debug
                        //print "Query failed: " . $cl->GetLastError () . ".\n";

                    } else {
                        
                        if ($results ['total'] != 0) {
    
                            if (is_array ( $results ["matches"] )) {
                                foreach ( $results ["matches"] as $docinfo ) {
                                    // for debug 
                                    //print "$n. doc_id=$docinfo[id], weight=$docinfo[weight]";
                                    //fetch each and every matched content 
                                    $searchQry [] = $docinfo ['id'];
                                
                                }
                            }
        
                        } else {
             
                            $results2 = $sphinx->searchChapProducts( $keywords, SPH_MATCH_ANY, $this->_chapId);
                            //$results = $productsModel->getSearchQueryPartner( $keywords, SPH_MATCH_PHRASE, $this->_chapId);
               
                            if ($results2 === false) {
                            

                            } else {
                                
                                if ($results2 ['total'] != 0) {
                            
                                    
                                    if (is_array ( $results2 ["matches"] )) {
                                        foreach ( $results2 ["matches"] as $docinfo2 ) {
                                            // for debug 
                                            //print "$n. doc_id=$docinfo[id], weight=$docinfo[weight]";
                                            

                                            //fetch each and every matched content 
                                            $searchQry [] = $docinfo2 ['id'];
                                        
                                        }
                                    }
                                
                                }
                            }
                            
                        }
                    }

                
                }
            
            } else {
                 //$searchQry  = $productsModel->getSearchQueryChap($keywords, $deviceId, false, $this->_chapId );
                 //$searchQry  = $productsModel->getSearchQueryPartner($keywords,  false, $this->_chapId );
                //$products = $productsModel->getSearchQueryPartner( $keywords, true, $this->_chapId, 10, true);
                 
                
                /* removed by chathura to support Orange 
                $searchQry = array();
                //Get the records from product_language_meta 
                if($this->_chapLanguageId != 1):
                    //echo 'inside other lang';
                    $searchQry = $productsModel->getSearchQueryPartnerProductLangMeta($keywords, false, $this->_chapId, NULL, NULL, $this->_chapLanguageId);
                endif;
                
              
                //If 
                if(count($searchQry) < 1):
                    //echo 'inside english';
                   
                endif;
                
                  */
                $searchQry = $productsModel->getSearchQueryPartner( $keywords, false, $this->_chapId);
            }
                
            $page           = $this->_request->getParam ('page', 1);

            
            //Zend_Debug::dump($searchQry);
            //die();
            
            
            if ($searchQry) {
                $paginator = Zend_Paginator::factory($searchQry);
                $paginator->setItemCountPerPage(10);
                $paginator->setCurrentPageNumber($page);
                if (! is_null($paginator)) {
                    foreach ($paginator as $row) {
                    if (Zend_Registry::get('config')->nexva->application->search->sphinx->enable and empty($deviceId)) {
                            $products[] = $productsModel->getProductDetailsById($row,FALSE,$this->_chapLanguageId);
                        } else {
                            $id = (isset($row->id)) ? $row->id : $row->product_id; //can't change the device selector code now 
                            $products[] = $productsModel->getProductDetailsById($id,FALSE,$this->_chapLanguageId);
                        }
                    }
                }
                
              //Zend_Debug::dump($products);
              //die();

                $this->view->products = $products;
                $this->view->paginator = $paginator;
            }
        
        }
        

        
        if (empty($products)) {
        	
            $this->view->noResuls = " We're sorry, the keyword/s you searched for (" . $this->view->escape($this->_request->q) . ") did not return any results .";
        }
        $this->view->keywords   = $keywords;
        
       
	    if ($this->_request->device_id) {
	      $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/search/index/q/'.$keywords .'page/' . $categoryId. $this->_request->device_id;
	    } else {
	      $this->view->baseUrl = $_SERVER ['SERVER_NAME'] . '/search/index/q/'.$keywords.'/page/';
	    }

    }
 
	
	/**
	 * Search suggestions
	 */
    public function suggestAction()
    {
        
        //Retrieve translate object
        $translate = Zend_Registry::get('Zend_Translate');
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();

        $keywords   = trim($this->_request->q);

        $productsModel  = new Model_Product();
        $deviceId = $this->getDeviceId();
        //$deviceId = '8820';
        //$deviceId = '8820';

        //Differnt methods will be called when Device id is given
        if ($deviceId !== null && !empty($deviceId))
        {
          
            
            //$products = $productsModel->getSearchQueryPartnerWithDevice( $keywords,$deviceId, false, $this->_chapId, 10, true );
            $products = array();
            //Get the records from product_language_meta 
            if($this->_chapLanguageId != 1):
                //echo 'inside other lang';
                $products = $productsModel->getSearchQueryPartnerWithDeviceProductLangMeta( $keywords,$deviceId, false, $this->_chapId, 10, true, $this->_chapLanguageId );
            endif;
            
            //If 
            if(count($products) < 1):
                //echo 'inside english';
                $products = $productsModel->getSearchQueryPartnerWithDevice( $keywords,$deviceId, false, $this->_chapId, 10, true );
            endif;
            
            
            
        }
        else
        {
            // added by chathura to spport orange
            $products = $productsModel->getSearchQueryPartner( $keywords, false, $this->_chapId, 10, true);
            /*
            $products = array();
            //Get the records from product_language_meta 
            if($this->_chapLanguageId != 1):
                //echo 'inside other lang';
                $products = $productsModel->getSearchQueryPartnerProductLangMeta($keywords, false, $this->_chapId, 10, true, $this->_chapLanguageId);
            endif;
            
            //If 
            if(count($products) < 1):
                //echo 'inside english';
                $products = $productsModel->getSearchQueryPartner( $keywords, false, $this->_chapId, 10, true);
            endif;
            
            */
            
        }

        //Zend_debug::dump($products);die();

		$results = array ();
		if (count($products) > 0) {
			foreach ( $products as $product ) {
				$results [] = array ('id' => $product ['product_id'], 'label' => $product ['name'], 'value' => $product ['name'] );
			}
		}
                
                
                if($this->_chapId == 23045 && count($products) == 0){
                    $allResultText = $translate->translate("There are no apps matching your query");
                    $results [] = array ('id' => 1, 'label' => $allResultText, 'value' => $keywords );
                }
                else{
                    $allResultText = $translate->translate("See all results for");
                    $results [] = array ('id' => 0, 'label' => '[' .$allResultText. ' ' . $keywords . ']', 'value' => $keywords );
                }

		echo json_encode ( $results );

    }
    
    
    protected function __getSelectedDeviceIds(){

        $session = new Zend_Session_Namespace("devices");
        if (count($session->selectedDevices)== 0)
        return null;
        else
        return $session->selectedDevices;

    }
    
 	

}

