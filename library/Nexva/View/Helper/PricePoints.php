<?php

/**
 * Helper to get correct price points for any chaps. If price point not available for the chap it will return the same price
 * @author Rooban
 */
class Nexva_View_Helper_PricePoints extends Zend_View_Helper_Abstract {

    /**
     * Find the input price and return most highest price points
     * @param float $price
     * @param int $chapId
     */
    public function PricePoints($price, $chapId = null) {

        $sessionChapDetailsMobile = new Zend_Session_Namespace('partnermobile');
        $sessionChapDetailsWeb = new Zend_Session_Namespace('partner');
        
        //Get the chap id from the session
        if($chapId == null){
            if($sessionChapDetailsWeb->id){
                $chapId = $sessionChapDetailsWeb->id;
            }
            else{
                $chapId = $sessionChapDetailsMobile->id;
            }
        }
        
        switch($chapId) {
            case 25022: //Airtel Sri Lanka
                $pricePointArray = array(5, 10, 15, 20, 30, 40, 50, 80, 100, 130, 150, 180, 200, 250, 300, 350, 400, 600, 800, 1000);
                return $this->getPricePoints($pricePointArray, $price);
                break;
            
            case 81449: //Airtel Nigeria
                $pricePointArray = array(5,10,15,20,25,50,100,150,160,200,250);
                return $this->getPricePoints($pricePointArray, $price);
                break;

            case 114306://Airtel Rwanda
                $pricePointArray = array(70,140,315,343,413,623,693,700,763,770,784,854,875,910,952,980,1043,1050,1120,1183,1190,1253,
                    1330,1344,1365,1393,1400,1596,1631,1743,1750,1757,1813,1890,1953,2030,2037,2093,2100,2121,2240,
                    2380,2450,2513,2730,2793,2800,3136,3150,3360,3465,3493,3500,3843,4165,4193,4200,4543,4550,4865,
                    4893,4900,5243,5523,5593,5600,6300,6860,6930,6965,6993 );
                return $this->getPricePoints($pricePointArray, $price);
                break;

            case 274515://Airtel Niger
                $pricePointArray = array(200,400,600,800,1000,1200,1400,1600,1800,2000,2200,2400,2600,2800,3000,3200,3400,3600,3800,4000,4200,4400,
                    4600,4800,5000,5200,5400,5600,5800,6000,6200,6400,6600,6800,7000,7200,7400,7600,7800,8000,8200,
                    8400,8600,8800,9000,9200,9400,9600,9800,10000);
                return $this->getPricePoints($pricePointArray, $price);
                break;

            case 110721://Airtel Gabon
                $pricePointArray = array(200,220,240,260,280,300,320,340,360,380,400,420,440,460,480,500,520,540,560,580,600,620,640,660,
                    680,700,720,740,760,780,800,820,840,860,880,900,920,940,960,980,1000,1020,1040,1060,1080,1100,
                    1120,1140,1160,1180,1200,1220,1240,1260,1280,1300,1320,1340,1360,1380,1400,1420,1440,1460,
                    1480,1500,1520,1540,1560,1580,1600,1620,1640,1660,1680,1700,1720,1740,1760,1780,1800,1820,
                    1840,1860,1880,1900,1920,1940,1960,1980,2000);
                return $this->getPricePoints($pricePointArray, $price);
                break;

            case 163302://Airtel Malawi
                $pricePointArray = array(100,200,300,400,500,600,700,800,900,1000,1100,1200,1300,1400,1500,1600,1700,1800,1900,2000,
                    2100,2200,2300,2400,2500,2600,2700,2800,2900,3000,3100,3200,3300,3400,3500,3600,3700,3800,3900,4000);
                return $this->getPricePoints($pricePointArray, $price);
                break;

            default:
                return $price;
                break;
        }
    }

    /*
     * Get price points logic.
     */
    public function getPricePoints($pricePointArray, $price) {

        $pricePoints = 0;
        $itemKey = 0;
        //Price points array
        $array = $pricePointArray;

        $minPricePoint = min($array);
        $maxPricePoint = max($array);

        if ($price >= $maxPricePoint) {
            $pricePoints = $maxPricePoint;
        } elseif ($price <= $minPricePoint) {
            $pricePoints = $minPricePoint;
        } else {
            foreach ($array as $key => $value) {

                if ($minPricePoint >= $price) {
                    break;
                }

                $minPricePoint = $value;
                $itemKey = $key;
            }

            $pricePoints = $array[$itemKey];
        }

        return $pricePoints;
    }

}

?>
