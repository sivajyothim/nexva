<?php
class Model_UserVirtualHost extends Nexva_Db_Model_MasterModel {

	protected  $_id     = 'id';
	protected  $_name   = 'user_virtualhosts';

	public function  __construct() {
		parent::__construct();
	}
	
	public function getVirtualHostFromHostname($hostname, $moduleName) {
		return $this->fetchRow("hostname = '".$hostname."' AND module = '$moduleName'" );			
	}
	
	
}
