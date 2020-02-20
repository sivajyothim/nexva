<?php
/**
 *
 * @copyright   neXva.com
 * @author      Heshan <heshan at nexva dot com>
 * @package     Admin
 * @version     $Id$
 */

class Model_ProductKey extends Zend_Db_Table_Abstract {


    protected $_name = 'product_keys';
    protected $_id   = 'id';


    function  __construct() {
        parent::__construct();
    }

    public function saveData($productId, $key) {
        $data = array(
                'product_id' => $productId,
                'key' => $key
        );
        $this->insert($data);
        //return $this->getAdapter()->lastInsertId();
    }
    
     public function daleteData($key, $productId) {
        $key = $this->_db->quoteColumnAs('key', true);
     	$this->delete($key.' =  '. $key . ' and product_id = ' . $productId);
    }

    public function getKeysByProductId($productId) {
        $rows = $this->fetchAll(
                $this->select()
                ->where('product_id = ?', $productId)
        );
        $key = null;
        foreach($rows as $row) {
            $key .= $row->key . ',';
        }
        if($key)
            return substr($key, 0, -1);
        else
            return false;
    }

    public function licenceKeyForProduct($productId) {
        $rows = $this->fetchAll(
                $this->select()
                ->where('product_id = ?', $productId)
        );
        $count = $rows->count();
        $key = null;
        foreach($rows as $row) {
            if(isset ($row->key)) {
                return $row->key;
            }
            else
                return false;
        }
    }
    
    /**
     * get the product id(s) which has product keys less then 10  
     * @param 
     * @return array product ids 
     */
    
    public function productKeysLessthanTen($mailTemlateContentsStr) {
    	
    	      $quiry =  $this->select()
                             ->setIntegrityCheck(false)
                             ->from($this, array('count(*) as no_of_produc_keys', 'product_keys.product_id',
                             					 'product_keys.id',
                             					 'products.name',
                                                 'products.id as products_id',
                                                 'users.email',
                                                 'users.id' ))
                             ->join('products', 'product_keys.product_id = products.id')
                             ->join('users', 'products.user_id = users.id')
                             ->group('product_id')
                             ->having('count(product_id) < 10');
               	          if(_DEBUG_){
		                     //   echo  $quiry->__toString()."\n\n\n\n";
		                        //$results = $quiry->__toString()."\n\n\n\n";
               			    }
               			    
               			   // echo  $quiry->__toString()."\n\n\n\n";
               			  //  die();
               			                  

             $rows = $this->fetchAll($quiry);
             

            
    $results ='';     
    
        if(_DEBUG_) {
    $logger = new Zend_Log();
    $path_to_log_file = '../logs/product_key_reminders.log';
    $writer = new Zend_Log_Writer_Stream($path_to_log_file);
    $logger->addWriter($writer);
    
    }

           foreach($rows as $row) {
               if(isset($row)) {
            	           
               		
               	
		                $userMeta    = new Model_UserMeta();
		                $productMeta = new Model_ProductMeta ( );
				        $cdkeyWarninCountStr  =  $productMeta->getAttributeValue($row->product_id,'KEY_WARNING_COUNT');
				        $cdkeyWarninDateDate  =  $productMeta->getAttributeValue($row->product_id,'KEY_WARNING_DATE');
				        
				        $fisrtName  =  $userMeta->getAttributeValue($row->user_id,'FIRST_NAME');
		                $lastName   =  $userMeta->getAttributeValue($row->user_id,'LAST_NAME');
		     		    
					    if(empty($fisrtName)) 
					    {
					    	$name = $row->email;
					    
					    }
					    else
					    {
					        $name =  $fisrtName . ' ' .$lastName;
					    }
				        
				
				        $dateDiffInt = $this->datediff('d', $cdkeyWarninDateDate, date("Y-m-d"), false);
				   			
               			if(_DEBUG_){
		                      echo ":::::::User - ". $row->user_id." Product- ".$row->product_id." Warning Count - ".$cdkeyWarninCountStr."Warning Date - ".$cdkeyWarninDateDate."\n";
		                      //$results .= ":::::::User - ". $row->user_id." Product- ".$row->product_id." Warning Count - ".$cdkeyWarninCountStr."Warning Date - ".$cdkeyWarninDateDate."\n";
               			}
			
				        if($cdkeyWarninCountStr == 0){
				        	
               			    
				        	 $productMeta->setAttributeValue($row->product_id, 'KEY_WARNING_COUNT', '1');
				        	 $productMeta->setAttributeValue($row->product_id, 'KEY_WARNING_DATE', date("Y-m-d"));
				        	 $mailer = new Nexva_Util_Mailer_Mailer();
				            	 
                                    
				             if(_DEBUG_){
				             	 $mailer->addTo(_TESTEMAIL_, _TESTNAME_)
                                        ->setSubject('Reminder from neXva !')
                                        ->setBodyHtml( $this->getMailBdy($name, $row->name,'1',$mailTemlateContentsStr) )
                                        ->send();
				             	
		                         echo "::::Fisrt remainder - User -". $row->user_id." Product- ".$row->product_id."\n\n\n\n\n";
		                         $results .= "::::Fisrt remainder - User -". $row->user_id." Product- ".$row->product_id." Sent ! \n\n";
               			     }
               			     else {
               			     	 $results .= "::::Fisrt remainder - User -". $row->user_id." Product- ".$row->product_id." Sent ! \n\n";
               			     	 $mailer->addTo($row->email, $row->name)
               			     	        ->addBcc(_BCCEMAIL_,_BCCNAME_)
               	                        ->setSubject('Reminder from neXva !')
                                        ->setBodyHtml( $this->getMailBdy($name, $row->name,'1',$mailTemlateContentsStr) )
                                        ->send();
               			     }
               			     
				        	 
				          }
                          else
				        	{
				        	 if(_DEBUG_){
		                          echo "\n";
		                          //$results .="\n";
               			      }
				        	}
				          
				          
				        if($cdkeyWarninCountStr == 1)    {
				        	
				        	$dateDiffInt = $this->datediff('d', $cdkeyWarninDateDate, date("Y-m-d"), false);
				   
				        	if ($dateDiffInt > 3)
				        	{
				       
               			    
				        	 $productMeta->setAttributeValue($row->product_id, 'KEY_WARNING_COUNT', '2');
				        	 $productMeta->setAttributeValue($row->product_id, 'KEY_WARNING_DATE', date("Y-m-d"));
				        	 $mailer = new Nexva_Util_Mailer_Mailer();
				       
                                    
                                    
				        	 	    if(_DEBUG_){
		                                echo "::::Second remainder - User -". $row->user_id." Product- ".$row->product_id."\n\n\n\n\n";
		                                $results .="::::Second remainder - User -". $row->user_id." Product- ".$row->product_id."Sent ! \n\n";
		                            
		                                $mailer->addTo(_TESTEMAIL_, _TESTNAME_)
                                               ->setSubject('Reminder from neXva !')
                                               ->setBodyHtml( $this->getMailBdy($name, $row->name,'2',$mailTemlateContentsStr) )
                                               ->send();
               			            }
               			            else
               			            {   
               			            	$results .="::::Second remainder - User -". $row->user_id." Product- ".$row->product_id."Sent ! \n\n";
               			            	$mailer->addTo($row->email, $row->name)
               			            	       ->addBcc(_BCCEMAIL_,_BCCNAME_)
                                               ->setSubject('Reminder from neXva !')
                                               ->setBodyHtml( $this->getMailBdy($name, $row->name,'2',$mailTemlateContentsStr) )
                                               ->send();
               			            	
               			            }
                                    
                                    
				        	}
				            else
				        	{
				        	 if(_DEBUG_){
		                          echo "\n";
		                          //$results .="\n\n\n\n\n";
               			      }
				        	}
				        }
				   
		            	if($cdkeyWarninCountStr == 2)    {
				        	
				        	$dateDiffInt = $this->datediff('d', $cdkeyWarninDateDate, date("Y-m-d"), false);
				
				        	if ($dateDiffInt > 3)
				        	{
				        	  
               			     $productMeta->setAttributeValue($row->product_id, 'KEY_WARNING_COUNT', '3');
				        	 $productMeta->setAttributeValue($row->product_id, 'KEY_WARNING_DATE', date("Y-m-d"));
				        	 $mailer = new Nexva_Util_Mailer_Mailer();
	
                                    
				        	    if(_DEBUG_){
		                            echo "::::Third remainder - User -". $row->user_id." Product- ".$row->product_id."\n\n\n\n\n";
		                            $results .="::::Third remainder - User -". $row->user_id." Product- ".$row->product_id."Sent ! \n\n";
		                          	$mailer->addTo(_TESTEMAIL_, _TESTNAME_)
                                           ->setSubject('Reminder from neXva !')
                                           ->setBodyHtml( $this->getMailBdy($name, $row->name,'3',$mailTemlateContentsStr) )
                                           ->send();
               			      } else
               			      {     $results .="::::Third remainder - User -". $row->user_id." Product- ".$row->product_id."Sent ! \n\n";
		                          	$mailer->addTo($row->email, $row->name)
		                          	       ->addBcc(_BCCEMAIL_,_BCCNAME_)
                                           ->setSubject('Reminder from neXva !')
                                           ->setBodyHtml( $this->getMailBdy($name, $row->name,'3',$mailTemlateContentsStr) )
                                           ->send();
               			      	
               			      }

				        	}
				        	else
				        	{
				        	 if(_DEBUG_){
		                          echo "\n\n\n\n\n";
		                          //$results .= "\n\n\n\n\n";
               			      }
				        	}
				        }
	        
				      if(_DEBUG_) {
				        
				      $logger->log($results, Zend_Log::INFO);
				     
				      }
            }
            else
                return false;
        }
             
   

     
        
    }
    
    /**
     * 
     * @param $interval no of days weeks yers etc 
     * @param $datefrom start date e.g. 2003-10-10
     * @param $dateto end date e.g. 2003-10-20
     * @param $using_timestamps 
     * @return int 
     */
    
    private function datediff($interval, $datefrom, $dateto, $using_timestamps = false) {
    /*
    $interval can be:
    yyyy - Number of full years
    q - Number of full quarters
    m - Number of full months
    y - Difference between day numbers
        (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
    d - Number of full days
    w - Number of full weekdays
    ww - Number of full weeks
    h - Number of full hours
    n - Number of full minutes
    s - Number of full seconds (default)
    */
    
    if (!$using_timestamps) {
        $datefrom = strtotime($datefrom, 0);
        $dateto = strtotime($dateto, 0);
    }
    $difference = $dateto - $datefrom; // Difference in seconds
     
    switch($interval) {
     
    case 'yyyy': // Number of full years

        $years_difference = floor($difference / 31536000);
        if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
            $years_difference--;
        }
        if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
            $years_difference++;
        }
        $datediff = $years_difference;
        break;

    case "q": // Number of full quarters

        $quarters_difference = floor($difference / 8035200);
        while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
            $months_difference++;
        }
        $quarters_difference--;
        $datediff = $quarters_difference;
        break;

    case "m": // Number of full months

        $months_difference = floor($difference / 2678400);
        while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
            $months_difference++;
        }
        $months_difference--;
        $datediff = $months_difference;
        break;

    case 'y': // Difference between day numbers

        $datediff = date("z", $dateto) - date("z", $datefrom);
        break;

    case "d": // Number of full days

        $datediff = floor($difference / 86400);
        break;

    case "w": // Number of full weekdays

        $days_difference = floor($difference / 86400);
        $weeks_difference = floor($days_difference / 7); // Complete weeks
        $first_day = date("w", $datefrom);
        $days_remainder = floor($days_difference % 7);
        $odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
        if ($odd_days > 7) { // Sunday
            $days_remainder--;
        }
        if ($odd_days > 6) { // Saturday
            $days_remainder--;
        }
        $datediff = ($weeks_difference * 5) + $days_remainder;
        break;

    case "ww": // Number of full weeks

        $datediff = floor($difference / 604800);
        break;

    case "h": // Number of full hours

        $datediff = floor($difference / 3600);
        break;

    case "n": // Number of full minutes

        $datediff = floor($difference / 60);
        break;

    default: // Number of full seconds (default)

        $datediff = $difference;
        break;
    }    

    return $datediff;

}

private function getMailBdy($user, $productNameStr, $remainder,$mailTemlateContentsStr)
{
	if($remainder == 1 ){
		$remainder ='1st';
	}
	
	if($remainder == 2 ){
		$remainder ='2nd';
	}
	
	if($remainder == 3 ){
		$remainder ='3rd';
	}
	
	$key[] = "<?=Zend_Registry::get('config')->nexva->application->base->url?>";
    $key[] = '<?=$this->layout()->content ?>';
	$imageUrl   =   Zend_Registry::get('config')->nexva->application->base->url;

    $mailContents =<<<EOD
 <br />
    Dear $user,  <br /><br />
                            
                      		This is just automatically generated friendly reminder, <br /><br />
                      		
                      		Our system indicates that license keys for product "$productNameStr" is reaching to the minimum no of keys. You always need to maintain  at least 10 license is the pool.  
                            <br /> <br />
                                
                            This is your $remainder remainder. <br /><br />
                            
                            Kindly requesting to immediately add new license keys in to the pool to get away from implications causing. 
                            
                             <a href="http://cp.nexva.com" target="_blank">Please click here to Login to neXva Product Admnistration</a>
EOD;
    
$bodytag = str_replace($key, array($imageUrl, $mailContents), $mailTemlateContentsStr);

return $bodytag;
}

    /**
     * @param $productId
     * @return return product keys
     */
    function getProductKeys($productId){
        $sql = $this->select()
                    ->from(array('pk' => $this->_name))
                    ->where('pk.product_id = ?',$productId)
                    ;
        return $this->fetchAll($sql);
    }

}

?>
