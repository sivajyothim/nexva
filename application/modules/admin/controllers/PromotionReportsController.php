<?php
class Admin_PromotionReportsController extends Nexva_Controller_Action_Admin_MasterController {
    
    public function usageAction() {
        $reportsModel       = new Admin_Model_PromotionCodeReport();
        $data               = $reportsModel->getPromotionsUsageGroupedByUser();
        $this->view->data   = $data;
    }
}