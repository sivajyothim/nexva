<?php

/**
 * A general purpose CIDR supported IP range filter library
 *
 * IP ranges will be loaded from IPFILTER_DIR/[whitelabel].ipf
 * If this file does not exist, no filtering will take place.
 *
 * @author jahufar
 * @package Nexva
 *
 */

define("IPFILTER_DIR",  APPLICATION_PATH."/modules/mobile/whitelabels/ipfilters");

class Nexva_Util_NetworkFilter_IpFilter {

    protected $whitelabel = null;

    /**     
     * @param string $whitelabel The name of the whitelabel (from system configuration)
     * @return void
     */
    public function  __construct($whitelabel) {
        $this->whitelabel = $whitelabel;        
    }   

    /**
     * Check if $ip is allowed in or not.
     *
     * @param string $ip IP address (IPv4, dotted)
     * @return int 1 if allowed, 0 if not.
     */
    function ipFilter($ip) {

        $match = 0;

        $ipFilterFile = IPFILTER_DIR."/{$this->whitelabel}.ipf";

        if (file_exists($ipFilterFile)) {

            $source = file($ipFilterFile);
            foreach ($source as $line) {
                $line = trim($line);

                if( $this->ipCIDRCheck($ip, $line)) {$match = 1; break;}
            }
        }
       else {
            $match = 1; //if the ip filter file does not exist, we allow allow everyone in
       }

       return $match;
    }

    
    function isFilterDefined() {
        $ipFilterFile = IPFILTER_DIR."/{$this->whitelabel}.ipf";
        if (file_exists($ipFilterFile)) {
            return true;
        } 
        return false;
    }


     function ipCIDRCheck ($IP, $CIDR) {
        list ($net, $mask) = split ("/", $CIDR);

        $ip_net = ip2long ($net);
        $ip_mask = ~((1 << (32 - $mask)) - 1);

        $ip_ip = ip2long ($IP);

        $ip_ip_net = $ip_ip & $ip_mask;

        return ($ip_ip_net == $ip_net);
      }

}
?>
