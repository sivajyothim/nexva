<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
class Cpbo_StatisticController extends Nexva_Controller_Action_Cp_MasterController {

    function predispatch() {

       //  $this->view->headScript()->appendFile('/vendors/FusionCharts/FusionCharts.debug.js');
         $this->view->headScript()->appendFile('/vendors/FusionCharts/JSClass/FusionCharts.debug.js');
        // $this->view->headScript()->appendFile('/vendors/FusionCharts/JSClass/Legacy/FusionChartsExportComponent.js');
        if( !Zend_Auth::getInstance()->hasIdentity() ) {

            if($this->_request->getActionName() != "login") {
                $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
                $session->lock();

            }
            if( $this->_request->getActionName() != "login" )
                $this->_redirect('/user/login');
        }

    }

    function indexAction() {


        ##Display top ten devices accessing your site
        $userId =   Zend_Auth::getInstance()->getIdentity()->id;

        //Get hot devices since last month
        $lastMonth = date('Y-m-d h:i:s',mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")));
        $statisticModel =   new Model_StatisticDevice();
        $topTenDevices    =   $statisticModel ->select()
                ->setIntegrityCheck(false)
                ->from('statistics_devices')
                ->columns("count(statistics_devices.id) as hits")
                ->join('devices',"devices.id = statistics_devices.device_id",array("model","brand"))               
                
                ->order('hits desc')
                ->where("date > '$lastMonth'")                 
                ->group('device_id')
              ->limit(10);

      $statisticModel->fetchAll($topTenDevices)->toArray();
        $this->view->topTenDevices   =   $statisticModel->fetchAll($topTenDevices)->toArray();
        ##Display top ten devices accessing your site

 

        #Display the top ten downloads/views
        $products = new Model_Product();


        $stats  =   $products->select()
                ->setIntegrityCheck(false)
                ->from("products",array("id","name"))
               // ->joinLeft("statistics_products", "statistics_products.product_id = products.id",array('date as hitsdate','hits'))
                ->joinLeft("statistics_downloads", "statistics_downloads.product_id = products.id",array('date as downloaddate','count(statistics_downloads.id) as downloads'))
                ->where("user_id=?", 157)
                ->group('products.id')
                 ->limit(10)
                ->order("downloads desc");





        $this->view->stats = $products->fetchAll($stats)->toArray();


        //

        $downloadsModel =   new Model_DownloadProduct();



    }

    function viewstatsbycountryAction(){

        $lastMonth = date('Y-m-d h:i:s',mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")));
        $modelStatisticsProducts    =   new Model_StatisticProduct();
        $visits =   $modelStatisticsProducts->select()
                ->setIntegrityCheck(false)
                ->from("statistics_products", array("count(statistics_products.id) as visit","ip"))
                ->join("products","products.id = statistics_products.product_id",array())
                ->join("users","users.id = products.user_id",array())
                ->where("users.id = ".Zend_Auth::getInstance()->getIdentity()->id)
                ->group("statistics_products.ip");

        // var_dump($modelStatisticsProducts->fetchAll($visits)->toArray());die();
         $this->view->visits = $modelStatisticsProducts->fetchAll($visits)->toArray();

    }
    
    function deleteCommaOnEnd($stringx) {

        if($stringx[strlen($stringx)-1] == ',') {
            $stringx  =  substr($stringx, 0,strlen($stringx)-1);
            return $stringx;
        }else {
            return $stringx;
        }
    }
}
?>
