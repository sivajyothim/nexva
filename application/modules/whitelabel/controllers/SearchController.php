<?php
class Whitelabel_SearchController extends Nexva_Controller_Action_Whitelabel_MasterController {
    
    public function indexAction() {
        
        $q     = $this->_getParam('keyword', null);       
        
        /**
         * @var Whitelabel_Model_Category
         */
        $productModel   = Nexva_Factory_Whitelabel_Model::getModel('Product', $this->_getEnvironmentOptions());
         
        if (!$q) {
            $this->_redirect('/');
            exit();
        }
        
        $page       = $this->_getParam('page', 1);
        
        $this->view->products       = $productModel->searchByTerm($q, false, $page, 20);
        $this->view->term           = $q;
        $this->view->TERM           = $q;
        
        
        $paging = array(
            'count'     => $productModel->getCountForSearchByTerm($q),
            'page'      => $page,
            'limit'     => 20,
            'baseUrl'   => PROJECT_BASEPATH."search/index/q/{$q}"
        );
        
        $this->view->paging = $paging;
    }
}
    