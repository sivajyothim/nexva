<?php
class Reseller_AnalyticsController extends Zend_Controller_Action {

    /**
     * A whitelist of allowed parameters. Used by the methods in this controller 
     * to strip out modified URLs
     * @var array
     */
    private $__validParameters    = array('from_view', 'to_view', 'pro_id');
    
    public function postDispatch() {
        parent::postDispatch();
        $this->view->queryString            = $this->__getQueryString();
    }

    public function appliesAction() {
        $this->__postToGet();
        
        $startDateView  = $endDateView  = '';//defaults supplied in method
        $this->__getValidDates($startDateView, $endDateView);         
        
        $promotionAnalyticsLib  = new Nexva_Analytics_PromotionCode();
        $purchaseAnalyticsLib   = new Nexva_Analytics_ProductPurchase(); 
        
        $gap    = $promotionAnalyticsLib->getChartGroupGap($endDateView - $startDateView);
        
        $appliesByDate      = $promotionAnalyticsLib->getCodeAppliesByDate(Zend_Auth::getInstance()->getIdentity()->id, $startDateView, $endDateView, $gap);
        $purchasesByDate    = $purchaseAnalyticsLib->getCodePurchasesByDate(Zend_Auth::getInstance()->getIdentity()->id, $startDateView, $endDateView, $gap);
        
        $this->view->appliesByDate      = json_encode($appliesByDate);
        $this->view->purchasesByDate    = json_encode($purchasesByDate);
        
        $this->view->startTimeView  = date('Y-m-d', $startDateView);
        $this->view->endTimeView    = date('Y-m-d', $endDateView);
        
    }
    
   /**
     * 
     * Simply looks through the POST array and makes it to a GET url for bookmarking
     * This uses a whitelist, so remember to add your parameters to the $params
     * variable 
     */
    private function __postToGet() {
        if ($this->getRequest()->isPost()) {
            $params = $this->__validParameters;
            $requestParams  = array_merge($this->_getAllParams(), $this->_request->getPost()); 
            $url    = "/{$requestParams['controller']}/{$requestParams['action']}/";
            
            foreach ($params as $param) {
                $value      = $this->_request->getPost($param, $this->_getParam($param, null)); //getting from POST, else GET
                if ($value) {
                    $url        .= "{$param}/{$value}/";    
                }
            }
            $this->_redirect($url);
        }
    }
    
    private function __getQueryString() {
            $params         = $this->__validParameters;
            $requestParams  = $this->_getAllParams(); 
            $qs         = '';
            foreach ($params as $param) {
                $value      = $this->_getParam($param, null);
                if ($value) {
                    $qs        .= "{$param}/{$value}/";    
                }
            }
            return $qs;
    }
 
    
    /**
     * 
     * Commonly used code. Sets defaults and checks for existing date range
     * @param timestamp $startDate
     * @param timestamp $endDate
     */
    private function __getValidDates(&$startDate, &$endDate) { //for the weekend, you know ;)
        $startDate  = strtotime("-1 week");
        $endDate    = strtotime("+1 day");
        
        
        if ($from   = $this->_request->getParam('from_view', null)) {
            $from       = strtotime($from);
            $startDate  = ($from) ? $from : null; 
        }
        
        if ($to   = $this->_request->getParam('to_view', null)) {
            $to         = strtotime($to) + 86400 * 2; //add 1 more day
            $endDate    = ($to) ? $to : null;
        }
    }
}
