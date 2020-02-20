<?php
/**
 * Displays useful information on downloads specific to a chap
 *
 * @copyright neXva.com
 * @author John Pereira
 * @date Feb 28, 2011
 */
class Admin_ChapStatisticsController extends Nexva_Controller_Action_Admin_MasterController {
    
    public function indexAction() {
        if ($this->_request->getParam('from', false) && $this->_request->getParam('to', false)) {
            $startDate  = date('Y-m-d', strtotime($this->_request->getParam('from', date('Y-m-d'))));
            $endDate    = date('Y-m-d', strtotime($this->_request->getParam('to', date('Y-m-d'))));
        }       
        
        $chapId     = $this->_request->getParam('id', false);
        
        $chapModel      = new Model_User();
        $chap           = $chapModel->find($chapId)->toArray(); 
        
        if (!$chapId || empty($chap)) {
            $this->_helper->FlashMessenger->addMessage('Invalid CHAP ID');
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
        }
        
        $statModel  = new Model_StatisticDownload();
        
        $select  =   $statModel->select()
                    ->from('statistics_downloads')
                    ->setIntegrityCheck(false)
                    ->join('products', 'products.id = statistics_downloads.product_id', array('products.name', 'products.id AS proId'))
                    ->join('product_builds', 'product_builds.id = statistics_downloads.build_id', array('product_builds.name AS build_name'))
                    ->where("chap_id = {$chapId}")
                    ->order('statistics_downloads.date DESC');
        if (isset($startDate) && isset($endDate)) {
            $select->where('statistics_downloads.date >= "' . $startDate . '"');
            $select->where('statistics_downloads.date <= "' . $endDate . '"');
        }
        
        $page           = $this->_request->getParam('page', 1);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage(50);
        $paginator->setCurrentPageNumber($page);      

        $downloadIds    = array();
        foreach ($paginator as $row) {
            $downloadIds[]  = $row->id;     
        }
        $downloadStats  = $statModel->getFileSizesForDownloads($downloadIds);
        
        $this->view->paginator      = $paginator;
        $this->view->fileSizes      = $downloadStats;          
        $this->view->chapId         = $chapId;
        $this->view->from           = isset($startDate) ? $startDate : '';
        $this->view->to             = isset($endDate) ? $endDate : '';
    }
    
    public function getTotalDataAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        
        $chapId         = $this->_request->getParam('chapId', false);
        $startDate      = date('Y-m-d', strtotime($this->_request->getParam('from', date('Y-m-d'))));
        $endDate        = date('Y-m-d', strtotime($this->_request->getParam('to', date('Y-m-d'))));
        
        if (!$chapId) return '';

        $themeMeta      = new Model_ThemeMeta();
        $themeMeta->setEntityId($chapId);
        $chap           = $themeMeta->getAll();
        
        $statDownloadModel  = new Model_StatisticDownload();
        /**
         * @todo move this code to some kind of statistics model
         */        
        $select  =   $statDownloadModel->select()
                    ->from('statistics_downloads', 'id')
                    ->setIntegrityCheck(false)
                    ->where("chap_id = {$chapId}")
                    ->where('statistics_downloads.date >= "' . $startDate . '"')
                    ->where('statistics_downloads.date <= "' . $endDate . '"')
                    ->where("source = '{$chap->WHITELABLE_ACCESS_TYPE}'");
        $rows   =  $statDownloadModel->fetchAll($select)->toArray();
        $ids    = array();
        foreach ($rows as $row) {
            $ids[]  = $row['id'];
        }
        $size       = $statDownloadModel->getFileSize($ids);
        echo 'Total usage for this period is ' . number_format($size / 1024, 2) . ' MB (' . number_format($size, 2)  . ' KB)';
    }
    
    public function getReportAction() {
        //$this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->getHelper('layout')->disableLayout();
        
        $chapId         = $this->_request->getParam('chapId', false);
        $startDate      = date('Y-m-d', strtotime($this->_request->getParam('from', date('Y-m-d'))));
        $endDate        = date('Y-m-d', strtotime($this->_request->getParam('to', date('Y-m-d'))));
        
        if (!$chapId) return '';

        $themeMeta      = new Model_ThemeMeta();
        $themeMeta->setEntityId($chapId);
        $chap           = $themeMeta->getAll();
        
        $statDownloadModel  = new Model_StatisticDownload();
        
        

        
        /**
         * @todo move this code to some kind of statistics model
         */
        $selectCount    = $statDownloadModel->select()
                    ->from('statistics_downloads', array('COUNT(statistics_downloads.id) AS count'))
                    ->setIntegrityCheck(false)
                    ->join('products', 'products.id = statistics_downloads.product_id', array())
                    ->join('product_builds', 'product_builds.id = statistics_downloads.build_id', array())
                    ->where("chap_id = {$chapId}")
                    ->where('statistics_downloads.date >= "' . $startDate . '"')
                    ->where('statistics_downloads.date <= "' . $endDate . '"')
                    ->where("source = '{$chap->WHITELABLE_ACCESS_TYPE}'");
        $count      = $statDownloadModel->fetchAll($selectCount)->toArray();
        
        
        
        
        $count      = $count[0]['count'];               
        $step       = 1000;
        $start      = 0;
        $totalKb    = 0;
        $totalApps  = 0;
        
        $header     = 'Date, AppID, App Name, Build, Size (KB) ,IP'; 
        $file       = Zend_Registry::get('config')->nexva->application->tempDirectory . '/' . md5($selectCount) . '.csv';
        $fp         = fopen($file, 'w');
        fputcsv($fp, split(',', $header));
        
        while ($start < $count) {
            
            $select  =   $statDownloadModel->select()
                    ->from('statistics_downloads')
                    ->setIntegrityCheck(false)
                    ->join('products', 'products.id = statistics_downloads.product_id', array('products.name', 'products.id AS proId'))
                    ->join('product_builds', 'product_builds.id = statistics_downloads.build_id', array('product_builds.name AS build_name'))
                    ->where("chap_id = {$chapId}")
                    ->where('statistics_downloads.date >= "' . $startDate . '"')
                    ->where('statistics_downloads.date <= "' . $endDate . '"')
                    ->where("source = '{$chap->WHITELABLE_ACCESS_TYPE}'")
                    ->limit($step, $start)
                    ->order('statistics_downloads.date ASC');   

            $rows   =  $statDownloadModel->fetchAll($select)->toArray();
            
            $downloadIds        = array();
            foreach ($rows as $row) {
                $downloadIds[]  = $row['id'];
                $totalApps++;    
            }
            $downloadStats  = $statDownloadModel->getFileSizesForDownloads($downloadIds);
            
            foreach ($rows as $row) {   
                $size       =  isset($downloadStats[$row['id']]) ? $downloadStats[$row['id']] : 0;
                $size       = ($size > 0) ? round(($size) / 1024, 2) : 0;
                $totalKb  += $size;
                $data   = array($row['date'], $row['proId'], $row['name'], $row['build_name'], $size ,$row['ip']);
                fputcsv($fp, $data);
            }
            
            $start  += $step;
        }
        
        $data   = array();
        fputcsv($fp, $data); 
        $data   = array('', '', '', '', 'Total Data Downloaded ', $totalKb . 'KB');
        fputcsv($fp, $data); //putting the total file size
        
        $data   = array('', '', '', '', 'Total Apps Downloaded', $totalApps);
        fputcsv($fp, $data); //putting the total number of apps downloaded
        
        $fname  = "Download_Report_{$startDate}_{$endDate}.csv";
        header('Content-type: text/csv');
        header("Content-Disposition: inline; filename=".$fname);
        readfile($file);
                
        die();
    }
}