<?php
class Admin_FilterCustomController extends Nexva_Controller_Action_Admin_MasterController {
	
	function saveAction() {
		$ruleId	= $this->_getParam('ruleId', false);
		if (!$ruleId) {
			$this->_redirect(ADMIN_PROJECT_BASEPATH.'/user/list/tab/tab-chaps');
		}
		
		$ruleModel	= new Admin_Model_ProductUserRules();
		$rule		= $ruleModel->fetchRow('id = ' . $ruleId);
		if (!$rule) {
			$this->_redirect(ADMIN_PROJECT_BASEPATH.'/user/list/tab/tab-chaps');
		}
		$this->view->rule	= $rule;
	}
}