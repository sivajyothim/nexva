<?php

include_once APPLICATION_PATH."/../library/Nexva/Util/Sphinx/sphinxapi.php";

class Default_SearchsphinxController extends Nexva_Controller_Action_Web_MasterController {
	
	public function init() {
	   parent::init();
	}
	
	public function indexAction() {
		$tmpDeviceId = '';
		$searchQry = '';
		if (! empty ( $this->_request->q )) {
			$this->view->headTitle ()->append ( 'Search - ' . $this->view->escape ( $this->_request->q ) );
			$this->view->headMeta ()->appendName ( 'keywords', 'neXva, nexva.com, ' . $this->view->escape ( $this->_request->q ) );
		} else {
			$this->view->headTitle ()->append ( 'Search' );
			$this->view->headMeta ()->appendName ( 'keywords', 'neXva, nexva.com' );
		}
		
        $tmpDevices     = $this->getSelectedDeviceIds();
        $tmpDeviceId    = NULL;
        if ($tmpDevices != NULL) {
            foreach ( $tmpDevices as $tmpVal ) {
                $tmpDeviceId [] = $tmpVal;
            }
        } 
        $keywords   = trim ($this->_request->q);
        $products   = array();
        if (!empty($keywords)) {
            $productsModel  = new Model_Product();
            
            //if the devices are selected use the normal DB search else use sphinx
            if ($tmpDeviceId)
				$searchQry = $productsModel->getSearchQuery ( $keywords, $tmpDeviceId );
			else {
				$sphinx = new Nexva_Util_Sphinx_Sphinx ( );
				$res = $sphinx->search ( $keywords );
				
				if ($res === false) {
					// for debug
					//print "Query failed: " . $cl->GetLastError () . ".\n";
				

				} else {
					
					if ($res ['total'] != 0) {
						
						if (is_array ( $res ["matches"] )) {
							foreach ( $res ["matches"] as $docinfo ) {
								// for debug 
								//print "$n. doc_id=$docinfo[id], weight=$docinfo[weight]";
								

								//fetch each and every matched content 
								$searchQry [] = $docinfo ['id'];
							
							}
						}
					
					}
				}
			
			}
			
			if ($searchQry) {
				
				$page = $this->_request->getParam ( 'page', 1 );
				$paginator = Zend_Paginator::factory ( $searchQry );
				$paginator->setItemCountPerPage ( 10 );
				$paginator->setCurrentPageNumber ( $page );
				
				if (! is_null ( $paginator )) {
					foreach ( $paginator as $row ) {
						
						if ($tmpDeviceId) {
							$id = (isset ( $row->id )) ? $row->id : $row->product_id;
							$products [] = $productsModel->getProductDetailsById ( $id );
						} else {
							$products [] = $productsModel->getProductDetailsById ( $row );
						
						}
					}
				}
				$this->view->products = $products;
				$this->view->paginator = $paginator;
			}
		
		}
        if (empty($products)) {
            $this->view->noResuls = " We're sorry, the keyword/s you searched for (" . $this->_request->q . ") did not return any results .";
        }
        $this->view->keywords   = $keywords;
	}
	
 
	
	/**
	 * Search suggestions
	 */
    public function suggestAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
    
        $tmpDevices     = $this->getSelectedDeviceIds();
        $tmpDeviceId    = NULL;
        if ($tmpDevices != NULL) {
            foreach ( $tmpDevices as $tmpVal ) {
                $tmpDeviceId [] = $tmpVal;
            }
        } 
        $keywords   = trim ($this->_request->q);
        $products   = array();
        if (!empty($keywords)) {
            $productsModel  = new Model_Product();
           
            
         //if the devices are selected use the normal DB search else use sphinx
            if ($tmpDeviceId)
                $searchQry = $productsModel->getSearchQuery ( $keywords, $tmpDeviceId, $simpleSearch = TRUE );
            else {
                $sphinx = new Nexva_Util_Sphinx_Sphinx ( );
                $res = $sphinx->search ( $keywords );
                
                if ($res === false) {
                    // for debug
                    //print "Query failed: " . $cl->GetLastError () . ".\n";
                

                } else {
                    
                    if ($res ['total'] != 0) {
                        
                        if (is_array ( $res ["matches"] )) {
                            foreach ( $res ["matches"] as $docinfo ) {
                                // for debug 
                                //print "$n. doc_id=$docinfo[id], weight=$docinfo[weight]";
                                

                                //fetch each and every matched content 
                                $searchQry [] = $docinfo ['id'];
                            
                            }
                        }
                    
                    }
                }
            
            }
            
            if ($searchQry) {
            
            
            $adaptor        = new Zend_Paginator_Adapter_DbSelect($querySelect);
            $page           = $this->_request->getParam ('page', 1);

            $paginator = Zend_Paginator::factory($querySelect);
            $paginator->setItemCountPerPage(10);
            $paginator->setCurrentPageNumber($page);
            
            if (! is_null ( $paginator )) {
                foreach ( $paginator as $row ) { 
                    $id         = (isset($row->id)) ? $row->id : $row->product_id; //can't change the device selector code now 
                    $products[] = $productsModel->getProductValById($id);
                }
            }
        }
        
        $results    = array();
        if (!empty($products)) {
            foreach ($products as $product) {
                $results[]  = array('id' => $product['id'], 'label' => $product['name'], 'value' => $product['name']);
            }
        }
        $results[]  = array('id' => 0, 'label' => '[See all results for ' . $keywords . ']', 'value' => $keywords);
        
        }
        
        echo json_encode($results);        
    }

}

