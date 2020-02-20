<?php

class Default_SearchController extends Nexva_Controller_Action_Web_MasterController {
	
	public function init() {
	   parent::init();
	   $this->setLastRequestedUrl();
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
            
            if (Zend_Registry::get ( 'config' )->nexva->application->search->sphinx->enable) {
                
                //if the devices are selected use the normal DB search else use sphinx
                if ($tmpDeviceId)
                    $searchQry = $productsModel->getSearchQuery ( $keywords, $tmpDeviceId );
                
                
                else {
                    $sphinx = new Nexva_Util_Sphinx_Sphinx ( );
                    $results = $sphinx->searchProducts( $keywords, SPH_MATCH_PHRASE );
                
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
        
                        }else {
             
                            $results2 = $sphinx->searchProducts ( $keywords, SPH_MATCH_ANY );
                                 
               
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
                 $searchQry  = $productsModel->getSearchQuery ( $keywords, $tmpDeviceId );
            }
                
            $page           = $this->_request->getParam ('page', 1);
            
            if ($searchQry) {
                $paginator = Zend_Paginator::factory($searchQry);
                $paginator->setItemCountPerPage(20);
                $paginator->setCurrentPageNumber($page);
                if (! is_null($paginator)) {
                    foreach ($paginator as $row) {
                        if (Zend_Registry::get('config')->nexva->application->search->sphinx->enable and
                         empty($tmpDeviceId)) {
                            $products[] = $productsModel->getProductDetailsById(
                            $row);
                        } else {
                            $id = (isset($row->id)) ? $row->id : $row->product_id; //can't change the device selector code now 
                            $products[] = $productsModel->getProductDetailsById(
                            $id);
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
    
        $searchQry       = '';
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
			
			if (Zend_Registry::get('config')->nexva->application->search->sphinx->enable) {
                          
				//if the devices are selected use the normal DB search else use sphinx
				if ($tmpDeviceId) {
                                    
					$searchQry = $productsModel->getSearchQuery ( $keywords, $tmpDeviceId );
				} else {
                                    
                                    
					$sphinx = new Nexva_Util_Sphinx_Sphinx ( );
					$res = $sphinx->searchProducts( $keywords, SPH_MATCH_PHRASE );
					
					if ($res !== false && $res['total'] != 0 && is_array($res ["matches"])) {
						foreach ( $res ["matches"] as $docinfo ) {
							$searchQry [] = $docinfo ['id'];
						}
					}
				}
			} else {
                                
				$searchQry = $productsModel->getSearchQuery ( $keywords, $tmpDeviceId, $simpleSearch = TRUE );
			}
            

            $page           = $this->_request->getParam ('page', 1);
            
            //Zend_Debug::dump($searchQry->__toString());die();
            
            if ($searchQry) {
				$paginator = Zend_Paginator::factory ( $searchQry );
				$paginator->setItemCountPerPage ( 10 );
				$paginator->setCurrentPageNumber ( $page );
				
				if (! is_null ( $paginator )) {
					foreach ( $paginator as $row ) {
						if (Zend_Registry::get ( 'config' )->nexva->application->search->sphinx->enable and empty ( $tmpDeviceId )) {
							$products [] = $productsModel->getProductDetailsById ( $row );
						} else {
							$id = (isset ( $row->id )) ? $row->id : $row->product_id; //can't change the device selector code now 
							$products [] = $productsModel->getProductDetailsById ( $id );
						}
					}
				}
			}
			$results = array ();
			if (! empty ( $products )) {
				foreach ( $products as $product ) {
					$results [] = array ('id' => $product ['id'], 'label' => $product ['name'], 'value' => $product ['name'] );
				}
			}
			$results [] = array ('id' => 0, 'label' => '[See all results for ' . $keywords . ']', 'value' => $keywords );
			
			echo json_encode ( $results );
		
		}
        
    }

}

