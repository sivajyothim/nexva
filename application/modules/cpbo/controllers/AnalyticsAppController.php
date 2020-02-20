<?php
class Cpbo_AnalyticsAppController extends Nexva_Controller_Action_Cp_MasterController {
    
    function indexAction() {
        
        if ($this->getRequest()->isPost()) {
            $params = array('from_view', 'to_view', 'pro_id');
            $url    = '/analytics-app/index/';
            foreach ($params as $param) {
                $value      = $this->_request->getParam($param, null);
                $url        .= "{$param}/{$value}/";
            }
            $this->_redirect($url);
        }
        
        $productId  = $this->_request->getParam('pro_id', false);
        if (!$productId) {
            $this->_redirect('/analytics');
        }
        $productModel   = new Model_Product();
        $product        = $productModel->getProductDetailsById($productId, true);
        if ($product['uid'] != $this->_getCpId()) {
            $this->_redirect('/analytics');
        }  
        $this->view->product    = $product;
        
        $startDateView  = strtotime("-1 month");
        $endDateView    = strtotime("+2 hour");

        if ($from   = $this->_request->getParam('from_view', null)) {
            $from           = strtotime($from);
            $startDateView  = ($from) ? $from : $startDateView; 
        }
        
        if ($to   = $this->_request->getParam('to_view', null)) {
            $to             = strtotime($to);
            $endDateView    = ($to) ? $to : $endDateView;
        }

        $views          = new Nexva_Analytics_ProductView(); 
        $downloads      = new Nexva_Analytics_ProductDownload();
        
        $views->validateDates($startDateView, $endDateView);
        $gap    = $views->getChartGroupGap($endDateView - $startDateView);
        
        $viewByDate     = $views->getAppViewsByDate($this->_getCpId(), $startDateView, $endDateView, $gap, $productId);
        $viewByDevice   = $views->getAppViewsByDevice($startDateView, $endDateView, $this->_getCpId(), 8, $productId);
        $downloadsByDate    = $downloads->getAppDownloadsByDate($this->_getCpId(), $startDateView, $endDateView, $gap, $productId);
        $downloadsByBuild   = $downloads->getAppDownloadsByBuild($this->_getCpId(), $startDateView, $endDateView, 8, $productId);
        
        
        $this->view->startTimeView  = date('Y-m-d', $startDateView);
        $this->view->endTimeView    = date('Y-m-d', $endDateView);
        
        $this->view->appView            = $viewByDate;
        $this->view->appDownloads       = $downloadsByDate;
        
        $this->view->appViewDateJson        = json_encode($viewByDate);
        $this->view->appVisitsDeviceJson    = json_encode($viewByDevice);       
        $this->view->appDownloadDateJson    = json_encode($downloadsByDate);
        $this->view->appDownloadBuildJson   = json_encode($downloadsByBuild);    
    }
    
    function allAppsPopupAction() {
        $this->_helper->layout->setLayout('cp/popup');
        
        $appViews   = new Nexva_Analytics_ProductView();
        
    }
    
    function searchJsonAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        
        $q      = $this->_request->getParam('q', false);
        if ($q === false) {
            echo json_encode(array());
            return;
        }
        
        $productModel   = new Model_Product();
        $select     = $productModel->select();
        $select->from('products', array('id', 'name'))
                ->where('user_id = ?', $this->_getCpId())
                ->where('name LIKE ?', "%{$q}%")
                ->limit(10);
        $results    = $productModel->fetchAll($select);
        if($results) {
            $dataArr    = array();
            foreach ($results as $product) {
                $dataArr[]    = array ('id' => $product->id, 'label' => $product->name, 'value' => $product->name);
            }
            echo json_encode($dataArr);
        } else {
            echo json_encode(array());    
        }
    }
    

    
}