<?php



include_once( APPLICATION_PATH .'/../public/vendors/Tera-WURFL/TeraWurfl.php' );



class Nexva_DeviceDetection_TeraWurfl extends TeraWurfl {
    
	public function __construct ()
    {
        parent::__construct();
    }
    
/**
	 * Detects the capabilities of a device from a given user agent and optionally, the HTTP Accept Headers
	 * @param String HTTP User Agent
	 * @param String HTTP Accept Header
	 * @return Bool matching device was found
	 */
	public function getDeviceCapabilitiesFromAgent($userAgent=null,$httpAccept=null){
		$this->db->numQueries = 0;
		$this->matchData = array(
			"num_queries" => 0,
			"match_type" => '',
			"matcher" => '',
			"match"	=> false,
			"lookup_time" => 0,
		);
		$this->lookup_start = microtime(true);
		$this->foundInCache = false;
		$this->capabilities = array();
		// Define User Agent

		if($userAgent)
			$this->userAgent = $userAgent;
		else 
			die('error');
		
		if(strlen($this->userAgent) > 255) $this->userAgent = substr($this->userAgent,0,255);
		// Use the ultra high performance SimpleDesktopMatcher if enabled
		if(TeraWurflConfig::$SIMPLE_DESKTOP_ENGINE_ENABLE){
			require_once (APPLICATION_PATH . '/../public/vendors/Tera-WURFL/UserAgentMatchers/SimpleDesktopUserAgentMatcher.php');

			if(SimpleDesktopUserAgentMatcher::isDesktopBrowser($userAgent)) $this->userAgent = WurflConstants::$SIMPLE_DESKTOP_UA;
		}

		$this->userAgent = UserAgentUtils::cleanUserAgent($this->userAgent);
		// Check cache for device
		if(TeraWurflConfig::$CACHE_ENABLE){
			$cacheData = $this->db->getDeviceFromCache($this->userAgent);
			// Found in cache
			if($cacheData !== false){
				$this->capabilities = $cacheData;
				$this->foundInCache = true;
				$deviceID = $cacheData['id'];
			}
		}
		if(!$this->foundInCache){
			require_once (APPLICATION_PATH . '/../public/vendors/Tera-WURFL/UserAgentMatchers/SimpleDesktopUserAgentMatcher.php');
			// Find appropriate user agent matcher
			$this->userAgentMatcher = UserAgentFactory::createUserAgentMatcher($this,$this->userAgent);
			// Find the best matching WURFL ID
			$deviceID = $this->getDeviceIDFromUALoose($userAgent);
			// Get the capabilities of this device and all its ancestors
			$this->getFullCapabilities($deviceID);
			// Now add in the Tera-WURFL results array
			$this->lookup_end = microtime(true);
			$this->matchData['num_queries'] = $this->db->numQueries;
			$this->matchData['lookup_time'] = $this->lookup_end - $this->lookup_start;
			// Add the match data to the capabilities array so it gets cached
			$this->addCapabilities(array($this->matchDataKey => $this->matchData));
		}
		if(TeraWurflConfig::$CACHE_ENABLE==true && !$this->foundInCache){
			// Since this device was not cached, cache it now.
			$this->db->saveDeviceInCache($this->userAgent,$this->capabilities);
		}
		return $this->capabilities[$this->matchDataKey]['match'];
	}
	
	
}