<?php
class Admin_ProductUserRuleController extends Nexva_Controller_Action_Admin_MasterController {
    
    function indexAction() {
        $chapId     =   $this->_getParam('chapId', false);
        $ruleModel  = new Admin_Model_ProductUserRules();
        $rules      = $ruleModel->getRules($chapId);
        
        if (!$chapId) {
            $this->__addMessage('Invalid chap ID');
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
        }
        
        if ($this->_request->isPost()) {
            $rule   = $ruleModel->getPopulatedObject($this->_getAllParams());
            
            $ruleArray  = (array) $rule;
            if ($rule->id) {
                $ruleModel->update($ruleArray, 'id = ' . $rule->id);
            } else {
            	$ruleArray['enabled']	= 0;
            	$ruleArray['id']		= null;
                $rule->id	= $ruleModel->insert($ruleArray);
            }
            $this->__addMessage('App rule saved, make sure to add filters to this rule');
            
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'filter-' . strtolower($rule->filter) . '/save/ruleId/' . $rule->id);
        } else {
            $ruleId = $this->_getParam('ruleId', false);
            if (!$ruleId) {
                $rule       = $ruleModel->getPopulatedObject();
            } else {
                $rule       = $ruleModel->fetchRow('id = ' . $ruleId);
            }
        }
        
        $this->view->chapId = $chapId;
        $this->view->rule   = $rule;
        $this->view->rules  = $rules;
    }

    
    function deleteAction() {
   		$chapId     = $this->_getParam('chapId', false);
        $ruleModel  = new Admin_Model_ProductUserRules();
        $ruleId 	= $this->_getParam('ruleId', false);
        
        $ruleModel->delete('id = ' .  $ruleId);
        
        if (!$chapId) {
            $this->_redirect(ADMIN_PROJECT_BASEPATH.'user/list/tab/tab-chaps');
        } else {
        	$this->_redirect(ADMIN_PROJECT_BASEPATH.'product-user-rule/index/chapId/' . $chapId);
        }
    }
}