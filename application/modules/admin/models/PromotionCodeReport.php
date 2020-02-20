<?php
class Admin_Model_PromotionCodeReport  {
    
  /**
     * 
     * This is a very ad hoc method I ha to put in here.
     * It's for a report and the spec isn't fully clear yet and I don't have much time to fix it 
     * Update once the spec is clear
     * 
     * @todo make this better
     */
    public function getPromotionsUsageGroupedByUser() {
        $db     = Zend_Registry::get('db');
        $sql    = "SELECT U.id AS user_id, U.email, COUNT(P.id) AS num_codes, 
                        SUM(IF (
                            debit_amount = 0, 
                            (SELECT PR.price FROM products PR LEFT JOIN product_promocodes PP ON PP.product_id = PR.id WHERE PP.promo_id = P.id LIMIT 1), 
                            debit_amount)
                        ) AS total_amount,
                        (SELECT PR.name FROM products PR LEFT JOIN product_promocodes PP ON PP.product_id = PR.id WHERE PP.promo_id = P.id LIMIT 1) AS product_name,
                        (SELECT PP.product_id FROM product_promocodes PP WHERE PP.promo_id = P.id LIMIT 1) AS product_id,
                        (SELECT meta_value FROM user_meta UM WHERE UM.user_id = P.user_id AND meta_name = 'COMPANY_NAME') AS company_name
                    FROM promocodes P LEFT JOIN users U ON P.user_id = U.id 
                    GROUP BY P.user_id";
        
        $createdCodes   = $db->query($sql)->fetchAll();
        
        $sql    = "SELECT U.id AS user_id, U.email, COUNT(P.id) AS num_codes, 
                        SUM(IF (
                            debit_amount = 0, 
                            (SELECT PR.price FROM products PR LEFT JOIN product_promocodes PP ON PP.product_id = PR.id WHERE PP.promo_id = P.id LIMIT 1), 
                            debit_amount)
                        ) AS total_amount,
                        (SELECT PR.name FROM products PR LEFT JOIN product_promocodes PP ON PP.product_id = PR.id WHERE PP.promo_id = P.id LIMIT 1) AS product_name,
                        (SELECT PP.product_id FROM product_promocodes PP WHERE PP.promo_id = P.id LIMIT 1) AS product_id
                    FROM promocodes P LEFT JOIN users U ON P.user_id = U.id
                    WHERE P.enabled = 0 
                    GROUP BY P.user_id";
        
        $usedCodes  = $db->query($sql)->fetchAll();
        
        //loop the 'created codes' list
        $reportData     = array();
        foreach ($createdCodes as $row) {
            $data['company_name']       = $row->company_name;
            $data['user_email']         = $row->email;
            $data['user_id']            = $row->user_id;
            $data['num_codes']          = $row->num_codes;
            $data['total_amount']       = $row->total_amount;
            $data['product_name']       = $row->product_name;
            $data['product_id']         = $row->product_id;
            $data['num_codes_used']     = 0;
            $data['total_amount_used']  = 0;
            $reportData[$row->email]    = $data;
        }
        
        
        //loop the 'used codes' list and merge
        foreach ($usedCodes as $row) {
            $data       = $reportData[$row->email];
            $data['num_codes_used']     = $row->num_codes;
            $data['total_amount_used']  = $row->total_amount;
            $reportData[$row->email]    = $data;
        }
        return $reportData;
        
    }
}