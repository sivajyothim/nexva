<?php

class Cpbo_Model_DeveloperVisits extends Zend_Db_Table_Abstract {

    protected $_name = 'developer_visits';
    protected $_id = 'id';

    public function saveStatics($ipAddress, $referer) {
        $geoData = Nexva_GeoData_Ip2Country_Factory::getProvider();
        $country = $geoData->getCountry($ipAddress);
        $data = array(
            'visited_date' => new Zend_Db_Expr('NOW()'),
            'ip' => $ipAddress,
            'referer' => $referer,
            'country' => $country['name']
        );
        $this->insert($data);
    }

    public function developerVisits($from, $to) {
        $sql = $this->select();
        $sql->from($this->_name, array("developerVisits" => "count(id)"))
                ->where("visited_date between '" . $from . "' and '" . $to . "'");
        //Zend_Debug::dump($sql->assemble());die();
        $userCount = $this->fetchRow($sql);

        return $userCount->developerVisits;
    }

}
