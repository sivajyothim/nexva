<?php

class Nexva_View_Helper_ViewStats extends Zend_View_Helper_Abstract {


        public function ViewStats($productId){

            $statisticsProductsModel   =   new Model_StatisticProduct();
            $count  =  $statisticsProductsModel
                    ->select()
                    ->setIntegrityCheck(false)
                    ->from("statistics_products", "count(id) as hits")
                    ->where("product_id = '$productId'")
                    ->query()
                    ->fetch();

           return($count->hits);

        }

}


?>