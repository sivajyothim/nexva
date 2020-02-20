<?php
class Admin_PromotionCampaignController extends Nexva_Controller_Action_Admin_MasterController {
    
    function indexAction() {
        $campaignModel          = new Admin_Model_PromotionCampaign();
        $cId        = $this->_getParam('id', false);
        
        if ($this->_request->isPost()) {
            $params     = $this->_getAllParams();
            $data       = $campaignModel->getPopulatedArray($params);
            if ($cId) {
                $campaignModel->update($data, 'id = ' . $cId);
                $this->__addMessage('Campaign updated');
            } else {
                $data['created_date'] = date('Y-m-d H:i:s');
                $campaignModel->insert($data);
                $this->__addMessage('Campaign added');
            }
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'promotion-campaign');
        } else {
            $campaign   = $campaignModel->getPopulatedObject();
            if ($cId) {
                $campaign   = $campaignModel->fetchRow('id = ' . $cId);
                if (!$campaign) {
                    $this->__addErrorMessage("Sorry, that campaign does not exist");
                    $this->_redirect(ADMIN_PROJECT_BASEPATH.'promotion-campaign');
                }
                
                $userMeta  = new Model_UserMeta();
                $userMeta->setEntityId($campaign->user_id);
                $this->view->savedUser  = $userMeta->COMPANY_NAME;
            }   
        }
        $campaigns      = $campaignModel->fetchAll();
        
        $this->view->campaigns  = $campaigns;
        $this->view->campaign   = $campaign;
    }
    
    function deleteAction(){ 
        $campaignModel          = new Admin_Model_PromotionCampaign();
        $id     = $this->_getParam('id');
        
        try {
            $campaignModel->delete('id = ' . $id);
            $this->__addMessage("Campaign deleted");
        } catch (Exception $ex) {
            $this->__addErrorMessage("Campaign could not be deleted. This campaign may have promotion codes attached to it");
        }
        $this->_redirect(ADMIN_PROJECT_BASEPATH.'promotion-campaign/index');
    }
}