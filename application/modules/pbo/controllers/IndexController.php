<?php

/*
 * 
 * this is for the paythru testing for this can be deleted 
 * 
 */

class Pbo_IndexController extends Zend_Controller_Action
{

    public function preDispatch() 
    {        
         if( !Zend_Auth::getInstance()->hasIdentity() ) 
         {

            $skip_action_names = array ('login', 'register', 'forgotpassword', 'resetpassword', 'claim', 'impersonate');
            
            if (! in_array ( $this->getRequest()->getActionName (), $skip_action_names)) 
            {            
                $requestUri = Zend_Controller_Front::getInstance ()->getRequest ()->getRequestUri ();
                $session = new Zend_Session_Namespace ( 'lastRequest' );
                $session->lastRequestUri = $requestUri;
                $session->lock ();
                $this->_redirect ( '/user/login' );
            } 
        }    
    
        $this->_helper->layout->setLayout('pbo/pbo'); 
    }
    

    public function indexAction()
    {
         $this->view->username = Zend_Auth::getInstance()->getIdentity()->username;
         
         $chapId = Zend_Auth::getInstance()->getIdentity()->id;          
         $todayDate = date('Y-m-d');
         $fromDate = date('Y-m-01');         
                   
         //User Stats         
         $userModel = new Pbo_Model_User();
         $this->view->totalAllUserCount = $userModel->getUserCount($chapId);
         $this->view->monthUserCount = $userModel->getUserCount($chapId, 'USER', $fromDate, $todayDate);
         $this->view->monthCPCount = $userModel->getUserCount($chapId, 'CP', $fromDate, $todayDate);
         $this->view->totalUserCount = $userModel->getUserCount($chapId, 'USER');
         $this->view->totalCPCount = $userModel->getUserCount($chapId, 'CP');
         
         //Download Stats
         $statDownloadsModel = new Pbo_Model_StatisticsDownloads();
         $this->view->totalAllDownloadCount = $statDownloadsModel->getDownloadCount($chapId); 
         $this->view->premiumDownloadCount = $statDownloadsModel->getDownloadCount($chapId, 'premium', $fromDate, $todayDate);
         $this->view->freeDownloadCount = $statDownloadsModel->getDownloadCount($chapId, 'free', $fromDate, $todayDate);
         $this->view->totalPremiumDownloadCount = $statDownloadsModel->getDownloadCount($chapId, 'premium');
         $this->view->totalFreeDownloadCount = $statDownloadsModel->getDownloadCount($chapId, 'free');
         
         //Upload Stats
         $productModel = new Pbo_Model_Products();         
         $this->view->totalAllUploadCount = $productModel->getUploadCount($chapId);
         $this->view->premiumUploadCount = $productModel->getUploadCount($chapId, 'premium', $fromDate, $todayDate); 
         $this->view->freeUploadCount = $productModel->getUploadCount($chapId, 'free', $fromDate, $todayDate); 
         $this->view->totalPremiumUploadCount = $productModel->getUploadCount($chapId, 'premium');
         $this->view->totalFreeUploadCount = $productModel->getUploadCount($chapId, 'free');         
                  
         //Sales Stats  
         $beginPreMonth = date('Y-m-01',strtotime("-1 month"));
         $endPreMonth = date('Y-m-t',strtotime("-1 month"));     
         
         $day = date('w');
         
         if($day ==1 )
         {
             $beginThisWeek = date('Y-m-d',strtotime('This Monday')).'<br/>';
         }
         else
         {
             $beginThisWeek = date('Y-m-d',strtotime('Last Monday')).'<br/>';
         }
        
         $endThisWeek = date('Y-m-d',strtotime('This Sunday'));
              
         $this->view->totalAllSales = $statDownloadsModel->getSalesValue($chapId); 
         $this->view->thisWeekSales = $statDownloadsModel->getSalesValue($chapId, $beginThisWeek, $endThisWeek);
         $this->view->preMonthSlaes = $statDownloadsModel->getSalesValue($chapId, $beginPreMonth, $endPreMonth);
         $this->view->thisMonthSlaes = $statDownloadsModel->getSalesValue($chapId, $fromDate, $todayDate);    

         
         $interopPayments = new Api_Model_InteropPayments();
         
         $this->view->totalAllSales = $interopPayments->getInteropPaymentSum($chapId);
         $this->view->thisWeekSales = $interopPayments->getInteropPaymentSum($chapId, $beginThisWeek, $endThisWeek);
         $this->view->preMonthSlaes = $interopPayments->getInteropPaymentSum($chapId, $beginPreMonth, $endPreMonth);
         $this->view->thisMonthSlaes = $interopPayments->getInteropPaymentSum($chapId, $fromDate, $todayDate);
         
         
    
         //Stats for the graph  
         
         $nextMonthFirstDay = date("Y-m-d",strtotime(date("Y-m-d", strtotime($fromDate)) . " +1 month"));
         
         $freeDownloadsInital = $statDownloadsModel->getDownloadCountsMonthly($chapId, $fromDate, $nextMonthFirstDay, 'free');
         $premiumDownloadsInital = $statDownloadsModel->getDownloadCountsMonthly($chapId, $fromDate, $nextMonthFirstDay, 'premium');
         $alllDownloadsInital = $statDownloadsModel->getDownloadCountsMonthly($chapId, $fromDate, $nextMonthFirstDay);
         
         $endOfMonth =  strtotime('this month');
                
         $this->view->freeAppsDownloadChart = $this->getFormattedDownloads($freeDownloadsInital,$fromDate);
         $this->view->premiumAppsDownloadChart = $this->getFormattedDownloads($premiumDownloadsInital,$fromDate);
         $this->view->allAppsDownloadChart = $this->getFormattedDownloads($alllDownloadsInital,$fromDate);
         $this->view->endOfMonth = $endOfMonth;
    }
    
    
	
    private function getFormattedDownloads($downloadsInital,$fromDate)
    {
         $lastday = date('t',strtotime($fromDate));         
         
         $downloadCountsProcessed = array();
         $downlodsByDate = array();
         
         foreach($downloadsInital as $downloads)
         {
             $downlodsByDate[strtotime($downloads->download_date)] = $downloads->download_count;
         }
         
         for($i = 1;$i <= $lastday; $i++)
         {
             $downloadCountsProcessed[strtotime(date('Y-m-'.$i)).'000'] = empty($downlodsByDate[strtotime(date('Y-m-'.$i))]) ? 0 : $downlodsByDate[strtotime(date('Y-m-'.$i))];
             //$freeDownloadProcessed[strtotime(date('Y-02-'.$i))] = $downlodsByDate[strtotime(date('Y-02-'.$i))];
         }  
         
         return $downloadCountsProcessed;         
    }
  
    
     /**
     * 
     * This is the new badge action. Leave it here till it's confired then remove the old badge action
     */
    public function badgeAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $productId  = $this->_request->id;
        $size       = ($this->_request->s) ? $this->_request->s : 'large';
        $template   = isset($this->_request->template) ? strtolower($this->_request->template) : 'nexva';
        $chapId     = $this->_getParam('chap', false);
        if (!$productId) {
            die();   
        }
        
        $badge      = new Nexva_Badge_BadgeImagick($productId, $chapId);
        $opts       = array(
            'show_logo' => ($template == 'nexva') ? true : false,
            'type'      => $size,
        );
        $badge->displayBadge($opts);
    }
}

