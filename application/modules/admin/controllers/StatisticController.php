<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
class Admin_StatisticController extends Zend_Controller_Action {

    function predispatch() {
        if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/login');
        }

        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'admin/assets/js/admin_stats.js');
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'admin/assets/css/core_admin.css');

        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'admin/assets/css/blue.css');
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'common/facebox/facebox.css');
+
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/facebox/facebox.js');
        Zend_Controller_Action_HelperBroker::addHelper(new Nexva_Controller_Action_Helper_CsvGenerator());
    }

    function viewAction() {

        $deviceStatsModel      =    new Model_StatisticDevice();
        $device     =   $deviceStatsModel->select();

        $device->setIntegrityCheck(false)
                ->from('statistics_devices', array('id','hits','date'))
                ->joinLeft("devices","devices.id = statistics_devices.device_id",array('id as device_id','brand','model'))
                ->order('hits desc')
                ->group("device_id")
                ->limit(10,0)
                ->query();
        $devices = $deviceStatsModel->fetchAll($device)->toArray();
        $this->view->devices = $devices;


        $modelStatistics   =   new Model_StatisticProduct();
        $product     =   $modelStatistics->select();
        $product ->setIntegrityCheck(false)
                ->from('statistics_products', array('id','hits','date'))
                ->join("products","products.id = statistics_products.product_id",array('id','name'))
                ->where('price = 0.0')
                ->order('hits desc')
                ->order('date desc')
                ->group('name')
                ->limit(10,0)
                ->query();
        $contents = $deviceStatsModel->fetchAll($product)->toArray();
        $this->view->contents_freemium = $contents;


        $modelStatistics   =   new Model_StatisticProduct();
        $product =   $modelStatistics->select();
        $product->setIntegrityCheck(false)
                ->from('statistics_products', array('id','hits','date'))
                ->join("products","products.id = statistics_products.product_id",array('id','name'))
                ->where('price <> 0.0')
                ->order('hits desc')
                ->order('date desc')
                ->group('name')
                ->limit(10,0)
                ->query();
        
        $contents = $deviceStatsModel->fetchAll($product)->toArray();
        $this->view->contents_premium = $contents;
    }

    function fetchstatsAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $period =   $this->_request->period;

        switch($period) {

            case 'day':
                $period = date('Y-m-d');
                $period = "date = '$period'";
                break;

            case 'month':
                $this_month =   date('m');

                $period = $this_month-01;
                $period = "date like  '".date('Y')."-0".$period."%'";
                break;

            case 'year':
                $period = date('Y');
                $period = "date like '$period%%'";
                break;

            case 'week':
                $date  =   date('Y-m-d',strtotime("-1 week"));

                $period = "date > '$date'";
                break;

            default:
                $period = date('Y-m-d');
                $period = "date = '$period'";
        }

        $type = $this->_request->type;

        if(!isset($type) or $type == '') {

            throw new Exception("Type should be provided");
            exit;
        }


        if($type == 'device') {

            echo  $this->getDeviceStats($period, '', true);
        }

        if($type == 'freeContent') {

            echo  $this->getFreeContentStats($period, '', true);
        }

        if($type == 'premiumContent') {

            echo $this->getPremiumContentStats($period, '', true);
        }

        if($type == 'actualdownloads') {

            echo $this->getActualDownloadStats($period, '', true);
        }

    }

    function graphAction() {

       
        $this->_helper->layout()->disableLayout();
        $period = $this->_request->period;
        $device = $this->_request->device;
        
        switch($period) {

//            case 'day':
//                $period = date('Y-m-d');
//                $period = "date = '$period'";
//                $chl = "1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24";
//                break; //Cant draw graph for day cause there are no data available

            case 'month':
                $this_month =   date('m');
                $period = $this_month;
                $period = "date like  '".date('Y')."-".$period."%'";                
                $num = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
                //if this month data is requested then get data by day
                $getDataBy ='d';
                break;

            case 'year':
                $period = date('Y');
                $period = "date like '$period%%'";
                //if this year data is requested then get data by month
                $getDataBy ='m';
                break;

            case 'week':
                $date  =   date('Y-m-d',strtotime("-1 week"));
                $period = "date > '$date'";
                //if this week data is requested then get data by day
                $getDataBy ='d';
                break;

            default:
                $period = date('Y-m-d');
                $period = "date = '$period'";
                $getDataBy ='m';
        }

        
        $hits    =   $this->getDeviceStatsById($device,$period);

            $graphData  =   array();
            foreach($hits as $hit){
                
                $key    =   date($getDataBy,strtotime($hit['date']));
             
                if(array_key_exists($key, $hit)){

                    if($graphData[$key]<$hit['hits']){
                        $graphData[$key] = $hit['hits'];
                    }                   

                }else{

                        $graphData[$key] = $hit['hits'];
                }
               
                $name               =   $hit['name'];
                $graphData[date($getDataBy,strtotime($hit['date']))] = $hit['hits'];
                
                
            }

        ksort($graphData);
        if(!empty($graphData)){

        $chl = implode("|",array_keys($graphData));
        $devicehit = array();        
        
        $max        =    max($graphData);
        $min        =    min($graphData);
        $deviceHits =    $this->deleteCommaOnEnd(implode(",",array_values($graphData)));
        
        
      

      //echo $deviceHits;
      //echo "<br />";
        //Asign values and generate chart
        $params['chxt']  = 'x,y';
        $params['chxr']  = "1,0,$max,".$this->calculateChartXisGap($max);
        $params['chtt']  = $name;
        $params['chs']   = '300x300';
        $params['chd']   = 't:'.$deviceHits;
        $params['cht']   = 'lc';
        $params['chl']   = $chl;
        $params['chco']  = '006699';
        $params['chm']   = "o,0066FF,0,-1,6";
        $params['chds']  = "$min,$max";
      
        $this->view->chart = $params;

    }
    }
    function calculateChartXisGap($max){

        switch($max){

            case $max>100000:
                $gap = 5000;
            break;

            case $max>50000:
                $gap  = 2500;
            break;

            case $max>10000:
                $gap = 500;
            break;

            case $max>1000:
                $gap = 100;
            break;

            case $max>100:
                $gap = 50;
            break;
            case $max<100:
                $gap = 10;
            break;
        

        }
        return $gap;
    }
    
    function deleteCommaOnEnd($stringx) {

        if($stringx[strlen($stringx)-1] == ',') {
            $stringx  =  substr($stringx, 0,strlen($stringx)-1);
            return $stringx;
        }else {
            return $stringx;
        }

    }

    function getDeviceStatsById($deviceId,$period='') {

        $deviceModel    =   new Model_Device();
        $device = $deviceModel->select();
        $device->from('statistics_devices', array('hits','date'))
                ->setIntegrityCheck(false)
                ->join("devices","devices.id = statistics_devices.device_id",array('CONCAT(brand,"-",model) as name'))
                ->where($period)
                ->where("devices.id= $deviceId")               
                ->query();       
        return $deviceModel->fetchAll($device);

    }

    function getDeviceStats($period,$limit=10,$enableJson = false) {



        if(!isset($period) or $period == '') {
            throw new Exception("Period should be provided");
        }

        if(!isset($limit) or $limit == '') {
            $limit = 10;
        }

        $deviceStatsModel      =    new Model_StatisticDevice();
        $device     =   $deviceStatsModel->select();


        $device->setIntegrityCheck(false)
                ->from('statistics_devices', array('hits'))
                ->join("devices","devices.id = statistics_devices.device_id",array('CONCAT(brand,"-",model) as name','id as device_id'))
                ->where("$period")
                ->order('hits desc')
                ->order('date desc')
                ->limit($limit,0)
                ->group('name')
                ->query();

        $devices = $deviceStatsModel->fetchAll($device);

        if($enableJson) {
            return  Zend_Json_Encoder::encode($devices->toArray());
        }else {
            return $devices;
        }

    }

    function getFreeContentStats($period,$limit=10,$enableJson = false) {

        if(!isset($period) or $period == '') {
            throw new Exception("Period should be provided");
        }

        if(!isset($limit) or $limit == '') {
            $limit = 10;
        }

        $deviceStatsModel      =    new Model_StatisticProduct();
        $device     =   $deviceStatsModel->select();

        $productStatsModel      =    new Model_StatisticProduct();

        $product    =   $productStatsModel->select();

        $product->setIntegrityCheck(false)
                ->from('statistics_products', array('hits'))
                ->joinLeft("products","product_id = products.id",array('name'))
                ->where("$period and price = 0.0")
                ->order('hits desc')
                ->order('date desc')
                ->limit($limit,0)
                ->group('name')
                ->query();

        $products = $productStatsModel->fetchAll($product);

        if($enableJson) {
            return  Zend_Json_Encoder::encode($products->toArray());
        }else {
            return $products;
        }

    }

    function getActualDownloadStats($period,$limit=10,$enableJson = false) {

        if(!isset($period) or $period == '') {
            throw new Exception("Period should be provided");
        }



        if(!isset($limit) or $limit == '') {
            $limit = 10;
        }



        $actualDownloadsStatsModel      =    new Model_StatisticDownload();
        $actualDownloads    =   $actualDownloadsStatsModel ->select();
        $actualDownloads->setIntegrityCheck(false)
                ->from('statistics_downloads', array('count as hits'))
                ->joinLeft("products","product_id = products.id",array('name'))
                ->where("$period ")
                ->order('count desc')
                ->order('date desc')
                ->limit($limit,0)
                ->group('name')
                ->query();
        $actualDownloads = $actualDownloadsStatsModel->fetchAll($actualDownloads);



        if($enableJson) {
            return  Zend_Json_Encoder::encode($actualDownloads->toArray());
        }else {
            return $actualDownloads;
        }

    }

    function getPremiumContentStats($period,$limit=10,$enableJson = false) {

        if(!isset($period) or $period == '') {
            throw new Exception("Period should be provided");
        }

        if(!isset($limit) or $limit == '') {
            $limit = 10;
        }


        $productStatsModel      =    new Model_StatisticProduct();
        $product    =   $productStatsModel->select();
        $product->setIntegrityCheck(false)
                ->from('statistics_products', array('hits'))
                ->joinLeft("products","product_id = products.id",array('name'))
                ->where("$period and price <> 0.0")
                ->order('hits desc')
                ->order('date desc')
                ->limit($limit,0)
                ->group('name')
                ->query();
        $products = $productStatsModel->fetchAll($product);



        if($enableJson) {
            return  Zend_Json_Encoder::encode($products->toArray());
        }else {
            return $products;
        }

    }

    function moreAction() {

        $this->view->period = $this->_request->period;
        $config =   Zend_Registry::get('config');
        $tmp_dir    =   $config->nexva->application->tempDirectory;

        switch($this->_request->period) {

            case 'day':
                $period = date('Y-m-d');
                $period = "date = '$period'";
                break;

            case 'month':
                $this_month =   date('m');

                $period = $this_month-01;
                $period = "date like  '".date('Y')."-0".$period."%'";
                break;

            case 'year':
                $period = date('Y');
                $period = "date like '$period%%'";
                break;

            case 'week':
                $date  =   date('Y-m-d',strtotime("-1 week"));
                $period = "date > '$date'";
                break;

            default:
                $period = date('Y-m-d');
                $period = "date = '$period'";
        }

        if($this->_request->type == 'device') {
            $stats  =   $this->getDeviceStats($period, 1000);
            $this->view->stats = $stats;

            $this->view->stats_title = "Device";
            $this->view->csv_title  =   'device';

            if(isset($this->_request->export)) {

                $this->_helper->viewRenderer->setNeverRender();
                $this->_helper->layout()->disableLayout();
                $name = $this->_request->type.'_'.$this->_request->period;


                $path = "$tmp_dir/stats_{$name}.csv";

                $file   =   $this->_helper->CsvGenerator->gnerate($path,$stats,',',array('Hits','Name'));

                $this->_customHeader($file);
            }

        }

        if($this->_request->type == 'freemium') {
            $stats  =   $this->getFreeContentStats($period, 1000);
            $this->view->stats = $stats;
            $this->view->stats_title = "Free Content";
            $this->view->csv_title  =   'freemium';


            if(isset($this->_request->export)) {
                $this->_helper->viewRenderer->setNeverRender();
                $this->_helper->layout()->disableLayout();
                $name = $this->_request->type.'_'.$this->_request->period;
                $path = "$tmp_dir/stats_{$name}.csv";
                $file   =   $this->_helper->CsvGenerator->gnerate($path,$stats,',',array('Hits','Name'));

                $this->_customHeader($file);
            }
        }

        if($this->_request->type == 'premium') {

            $stats   = $this->getPremiumContentStats($period, 1000);

            $this->view->stats = $stats;
            $this->view->stats_title = "Premium Content";
            $this->view->csv_title  =   'premium';


            if(isset($this->_request->export)) {
                $this->_helper->viewRenderer->setNeverRender();
                $this->_helper->layout()->disableLayout();
                $name = $this->_request->type.'_'.$this->_request->period;
                $path = "$tmp_dir/stats_{$name}.csv";
                $file   =   $this->_helper->CsvGenerator->gnerate($path,$stats,',',array('Hits','Name'));

                $this->_customHeader($file);
            }



            $this->view->stats_title = "Premium Content";
        }





        if($this->_request->type == 'actualdownloads') {

            $stats   = $this->getActualDownloadStats($period, 1000);

            $this->view->stats = $stats;
            $this->view->stats_title =  'Actual downloads';
            $this->view->csv_title  =   'actualdownloads';


            if(isset($this->_request->export)) {
                $this->_helper->viewRenderer->setNeverRender();
                $this->_helper->layout()->disableLayout();
                $name = $this->_request->type.'_'.$this->_request->period;
                $path = "$tmp_dir/stats_{$name}.csv";
                $file   =   $this->_helper->CsvGenerator->gnerate($path,$stats,',',array('Downloads','Name'));
                $this->_customHeader($file);
            }

            $this->view->stats_title = "Actual downloads";
        }



    }

    function _customHeader($file) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;

    }

    function indexAction() {

        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/datepicker/css/ui.daterangepicker.css');
        $this->view->headLink()->appendStylesheet(PROJECT_BASEPATH.'/common/datepicker/css/redmond/jquery-ui-1.7.1.custom.css');
        // $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/datepicker/js/jquery-1.3.2.min.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/datepicker/js/jquery-ui-1.7.1.custom.min.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/datepicker/js/daterangepicker.jQuery.js');
        $this->view->headScript()->appendFile(PROJECT_BASEPATH.'/common/datepicker/js/startdate.js');


        $order              = "statistics_devices.date desc";

        if(isset($this->_request->device_stats_order)) {

            switch ($this->_request->device_stats_order) {

                case 'hits':
                    $order  =   "hits ".$this->_request->device_hits_order_type;
                    break;

                case 'date':
                    $order  =   "statistics_devices.date ".$this->_request->device_date_order_type;
                    break;

                default:
                    $order  =   "statistics_devices.date desc";
            }
        }

        $this->view->device_hits_order_type  =   ($this->_request->device_hits_order_type == 'asc')?'desc':'asc';
        $this->view->device_date_order_type  =   ($this->_request->device_date_order_type == 'asc')?'desc':'asc';


        if(isset($this->_request->search) and $this->_request->type == 'device') {
            $modelStatistics   =   new Model_StatisticDevice();
            $selectDevices      =   $modelStatistics->select();
            $search =   rawurldecode($this->_request->search);
            //die(var_dump($search));
            if(false != strpos($search, "-")) {
                $dates =   split("-", $search);
                $fromDate    = $dates[0];
                $toDate      = $dates[1];
                $where  =   "date> '$fromDate' and date< '$toDate'";
            }else {



                $where  =   "date = '$search'";

            }


            $selectDevices->setIntegrityCheck(false)
                    ->from('statistics_devices', array('id','hits','date'))
                    ->join("devices","devices.id = statistics_devices.device_id")
                    // ->where("devices.name LIKE '%".trim($this->_request->search)."%'")
                    ->where($where)
                    ->order($order)
                    ->query();

        }else {
            $modelStatistics   =   new Model_StatisticProduct();


            $modelStatistics   =   new Model_StatisticProduct();
            $selectDevices      =   $modelStatistics->select();
            $selectDevices->setIntegrityCheck(false)
                    ->from('statistics_devices', array('id','hits','date'))
                    ->join("devices","devices.id = statistics_devices.device_id")
                    ->order($order)
                    ->limit('100')
                    ->query();

        }

        $deviceStats =   $modelStatistics->fetchAll($selectDevices);
        $name ='';
        if(isset($search)) {
            $name = "_".preg_replace(array('/-/','/\//','/\s/'), array("","-","_"), $search);
        }

        $path = "admin/static/stats/device/stats_{$name}.csv";
        $this->_helper->CsvGenerator->gnerate($path,$deviceStats,',');
        $device_pagination     =     Zend_Paginator::factory($deviceStats);
        if(0==count($device_pagination)) {
            $this->view->is_device_empty   =   true;
        }
        $device_pagination->setCurrentPageNumber($this->_request->getParam('page_device', '0'));
        $device_pagination->setItemCountPerPage(10);
        $this->view->active_type    =   $this->_request->type;
        $this->view->tab   =   ($this->_request->tab != '')?$this->_request->tab:$this->view->active_type;
        $this->view->device_statistics =   $device_pagination;
        $this->view->device_stats_csv  =  $path;
        $path   =   null;
        $modelStatistics = null;









        //Products stats

        $order              = "statistics_products.date desc";

        if(isset($this->_request->product_stats_order)) {

            switch ($this->_request->product_stats_order) {

                case 'hits':
                    $order  =   "hits ".$this->_request->product_hits_order_type;
                    break;

                case 'date':
                    $order  =   "statistics_products.date ".$this->_request->product_date_order_type;
                    break;

                default:
                    $order  =   "statistics_products.date desc";
            }
        }

        $this->view->product_hits_order_type  =   ($this->_request->product_hits_order_type == 'asc')?'desc':'asc';
        $this->view->product_date_order_type  =   ($this->_request->product_date_order_type == 'asc')?'desc':'asc';





        if(isset($this->_request->search) and $this->_request->type == 'product') {
            $search =   rawurldecode($this->_request->search);

            if(false != strpos($search, "-")) {
                $dates =   split("-", $search);
                $fromDate    = $dates[0];
                $toDate      = $dates[1];
                $where  =   "date> '$fromDate' and date< '$toDate'";

            }else {


                $where  =   "date = '$search'";

            }

            $modelStatistics   =   new Model_StatisticProduct();
            $selectProduct     =   $modelStatistics->select();
            $selectProduct->setIntegrityCheck(false)
                    ->from('statistics_products', array('id','hits','date'))
                    ->join("products","products.id = statistics_products.product_id",array('id','name'))
                    ->where($where)
                    ->order($order)
                    ->query();

        }else {
            $modelStatistics   =   new Model_StatisticProduct();
            $selectProduct     =   $modelStatistics->select();
            $selectProduct->setIntegrityCheck(false)
                    ->from('statistics_products', array('id','hits','date'))

                    ->join("products","products.id = statistics_products.product_id",array('id','name'))
                    ->order($order)
                    ->query();


        }

        $deviceStats =   $modelStatistics->fetchAll($selectProduct);
        $name ='';
        if(isset($search)) {
            $name = "_".preg_replace(array('/-/','/\//','/\s/'), array("","-","_"), $search);
        }

        $path=  "admin/static/stats/products/stats_{$name}.csv";
        $this->_helper->CsvGenerator->gnerate($path,$deviceStats,',');
        $pagination     =     Zend_Paginator::factory($deviceStats);
        if(0==count($pagination )) {
            $this->view->is_product_empty   =   true;
        }
        $pagination->setCurrentPageNumber($this->_request->getParam('page_product', '0'));
        $pagination->setItemCountPerPage(10);
        $this->view->active_type    =   $this->_request->type;

        $this->view->tab    =   ($this->_request->tab != '')?$this->_request->tab:$this->view->active_type;
        $this->view->search =   urldecode($this->_request->search);
        $this->view->search_val_for_paginator   =   (isset($search))?rawurlencode($search):'';
        $this->view->product_statistics =   $pagination;
        $this->view->product_stats_csv  =  $path;
        $holder =  Zend_Registry::get('config');
        $this->view->form_submit        =   'http://'.$holder->nexva->application->admin->url."/statistic/index";

    }

}
?>
