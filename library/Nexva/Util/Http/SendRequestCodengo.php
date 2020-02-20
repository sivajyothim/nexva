<?php

class Nexva_Util_Http_SendRequestCodengo { 
    
       
    private $_condengoUserId = 287151;
    
    public function send($appId){
        
        $apps = new Model_Product();
        $appInfo = $apps->getProductDetailsById($appId);
        
        if($appId == 36383 ) {
        	 
        	$ch = curl_init();
        	 
        
        	// curl_setopt($ch, CURLOPT_URL,'http://qareporting.codengo.com/Server/Recorder/Click?v=1&ch=Nexva&pk='.$appInfo['google_id'].'&adi=');
        	 
        	curl_setopt($ch, CURLOPT_URL,'http://reporting.codengo.com/Featured/Ping/www.nexva.com/com.jumia.android/?source=client');
        	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        	//curl_setopt($ch, CURLOPT_HTTPHEADER, "X_FORWARDED_FOR: ".$_SERVER['REMOTE_ADDR']);
        	 
        	$clientIp = $_SERVER['REMOTE_ADDR'];
        	curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: ".$clientIp,
        			"X_FORWARDED_FOR: ".$clientIp,
        			"remote-addr: ".$clientIp,
        			"x-forwarded-for: ".$clientIp));
        	 
        	// receive server response ...
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	 
        	$server_output = curl_exec ($ch);
        	 
        	curl_close ($ch);
        	 
        	/*
        	  
        	try {
        	$config = array(
        			'adapter'   => 'Zend_Http_Client_Adapter_Curl',
        			'curloptions' => array(CURLOPT_FOLLOWLOCATION => true,
        					CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        					CURLOPT_HTTPHEADER  => "X_FORWARDED_FOR: ".$_SERVER['REMOTE_ADDR'])
        	);
        	 
        	 
        	$client = new Zend_Http_Client('http://qareporting.codengo.com/Server/Recorder/Click?v=1&ch=Nexva&pk='.$value->google_id.'&adi=', $config);
        	$response = $client->request(Zend_Http_Client::GET);
        	//Zend_Debug::dump($response->getStatus()); //if this is 200 then this is ok
        	//Zend_Debug::dump($response->getBody());
        	} catch (Zend_Http_Client_Adapter_Exception $e) {
        	 
        	} catch (Exception $e) {
        
        	}
        	 
        	*/
        
        
        }
        
        
        if($appInfo['uid'] == 287151) {
           if(!empty($appInfo['google_id'])) {
               
               $ch = curl_init();
               
                    
              // curl_setopt($ch, CURLOPT_URL,'http://qareporting.codengo.com/Server/Recorder/Click?v=1&ch=Nexva&pk='.$appInfo['google_id'].'&adi=');
               
               curl_setopt($ch, CURLOPT_URL,'http://reporting.codengo.com/Featured/Ping/www.nexva.com/com.jumia.android/?source=client');
               curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
               //curl_setopt($ch, CURLOPT_HTTPHEADER, "X_FORWARDED_FOR: ".$_SERVER['REMOTE_ADDR']);
               
               $clientIp = $_SERVER['REMOTE_ADDR'];
               curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: ".$clientIp,
               		"X_FORWARDED_FOR: ".$clientIp,
               		"remote-addr: ".$clientIp,
               		"x-forwarded-for: ".$clientIp));
               
               // receive server response ...
               curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
               
               $server_output = curl_exec ($ch);
               
               curl_close ($ch);
               
               /*
               
               try {
                   $config = array(
                   		'adapter'   => 'Zend_Http_Client_Adapter_Curl',
                   		'curloptions' => array(CURLOPT_FOLLOWLOCATION => true,
                   		                       CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
                   		                       CURLOPT_HTTPHEADER  => "X_FORWARDED_FOR: ".$_SERVER['REMOTE_ADDR'])
                   );
                   
                   
               	   $client = new Zend_Http_Client('http://qareporting.codengo.com/Server/Recorder/Click?v=1&ch=Nexva&pk='.$value->google_id.'&adi=', $config);
                   $response = $client->request(Zend_Http_Client::GET);
                   //Zend_Debug::dump($response->getStatus()); //if this is 200 then this is ok
                   //Zend_Debug::dump($response->getBody());     
               } catch (Zend_Http_Client_Adapter_Exception $e) {
                   
               } catch (Exception $e) {

               }
               
               */
            
    
           }
        
        }
        
        
        
        if($appInfo['uid'] == 320888) {    
               		 
        		$ch = curl_init();
        		curl_setopt($ch, CURLOPT_URL,'http://app.naij.com/statistic/set-install');
        		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        		//curl_setopt($ch, CURLOPT_HTTPHEADER, "X_FORWARDED_FOR: ".$_SERVER['REMOTE_ADDR']);
        		 
        		$clientIp = $_SERVER['REMOTE_ADDR'];
        		curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: ".$clientIp,
        				"X_FORWARDED_FOR: ".$clientIp,
        				"remote-addr: ".$clientIp,
        				"x-forwarded-for: ".$clientIp));
        		 
        		// receive server response ...
        		
        		$data = array(
        				'client_ip' => $clientIp,
        				'user_agent' => $_SERVER['HTTP_USER_AGENT']
        		);
        		
        		curl_setopt($ch, CURLOPT_POST, 1);
        		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        		 
        		$server_output = curl_exec ($ch);
        		 
        		curl_close ($ch);
        		 
        		/*
        		  
        		try {
        		$config = array(
        				'adapter'   => 'Zend_Http_Client_Adapter_Curl',
        				'curloptions' => array(CURLOPT_FOLLOWLOCATION => true,
        						CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        						CURLOPT_HTTPHEADER  => "X_FORWARDED_FOR: ".$_SERVER['REMOTE_ADDR'])
        		);
        		 
        		 
        		$client = new Zend_Http_Client('http://qareporting.codengo.com/Server/Recorder/Click?v=1&ch=Nexva&pk='.$value->google_id.'&adi=', $config);
        		$response = $client->request(Zend_Http_Client::GET);
        		//Zend_Debug::dump($response->getStatus()); //if this is 200 then this is ok
        		//Zend_Debug::dump($response->getBody());
        		} catch (Zend_Http_Client_Adapter_Exception $e) {
        		 
        		} catch (Exception $e) {
        
        		}
        		 
        		*/
        
        
        }
            
        }

}