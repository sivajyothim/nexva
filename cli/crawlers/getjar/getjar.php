<?php

    include_once('../simplehtmldom/simple_html_dom.php');
    include_once('../phpQuery.php');
    include_once("../../../application/BootstrapCli.php");

    $html = new simple_html_dom();

    define("MAX_NO_APPS_PER_RUN", 30);

     if( !file_exists('last_stopped_page.txt'))
        $page = 0;
     else
        $page = file_get_contents('last_stopped_page.txt');

     echo "Starting crawler from page: $page \n \n"; sleep(2);

     $numberOfAppsDownloaded = 1;

     $message = "";

    
     while($numberOfAppsDownloaded <= MAX_NO_APPS_PER_RUN ) {



        $client = new Zend_Http_Client("http://m.getjar.com/mobile-all-software-for-blackberry-9000-bold/?ref=0&lvt=1291795614&sid=51uVO21CO2&lang=en&o=new&p=$page", array(
            'maxredirects' => 0,
            'timeout'      => 30,
            'useragent'     => 'BlackBerry9000/4.6.0.167 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/102'
            )

        );

        $response = $client->request();
        

        $html->load($response->getBody());

        

        /**
        $jadFileUrl = $html->getElementById('form_product_page')->action;
        downloadBlackberryApplication($jadFileUrl);
        die();
         *
         */
   
        //-----------------------------THIS CODE EXTRACTS THE STUFF FROM THE LISTS OF APP - homepage and category page ------------------------------------

        foreach($html->find('div[class=content] div[class=aps]') as $apps) {
            //echo $apps->innertext."\n\n-----------------\n\n";

            $appInfo = array();

            $appType = strtolower(strip_tags($apps->find('span[class=green]',0)->innertext)); //tells us what the app type is (free/demo/commercial/shareware etc)
            

            if( $appType == 'free' || $appType == "demo" || $appType = "shareware") { //we will only download these

                $appInfo['title'] = strip_tags($apps->find('a[class=p]',0)->innertext);
                //echo strip_tags($apps->find('a[class=p]',0)->innertext). "\r\n";

                $appInfo['description'] = strip_html_tags($apps->plaintext);
                //echo strip_html_tags($apps->plaintext) . "\r\n";

                
                //echo $apps->find('a[class=p]',0)->href. "\r\n";

                //echo $apps->find('img[class=thumb]',0)->src.'\r\n';
                $appInfo['thumbnail'] = $apps->find('img[class=thumb]',0)->src;
                

                $appUrl = "http://m.getjar.com".$apps->find('a[class=p]',0)->href;
                $appInfo['url'] = $appUrl;

                
                if( downloadBlackberryApplication($appUrl, $appInfo) ) {  //successfully downloaded.. increment counter                    
                    $message .= $numberOfAppsDownloaded. ". ".$appInfo['title']." (".$appInfo['url'].") \n\n";
                    $numberOfAppsDownloaded++;
                }
                
            }
        }

        echo "----> Number of apps downloaded: $numberOfAppsDownloaded.. Going to next page \n \n";

        $page +=3;
        
     }

     $message .= "\n \n Total number of applications downloaded: ".$numberOfAppsDownloaded;
     $message .= "\n Next run will begin at page: ".$page;

     file_put_contents('last_stopped_page.txt', $page);

     mail('jahufar@nexva.com', 'Crawl complete ('.date('Y-m-d h:i:s').")", $message);

     file_put_contents('crawl_log_'.date('Y-m-d_h-i-s').".log", $message);
    
    die();
    //-------------------------------------------------------------------------------------------------------------------------



function strip_html_tags( $text )
{
    $text = preg_replace(
        array(
          // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
          // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
        ),
        $text );
    return strip_tags( $text );
}


    function parseJadFile($file) {

        $jadData = file_get_contents ($file);
        $jadLines = explode("\n", $jadData );

        $jadArray = array();

        for( $x=0; $x < count($jadLines); $x++) {

            $data = explode(": ", $jadLines[$x]);
            if( isset($data[0]) && $data[0]!= "") $jadArray[$data[0]] = $data[1];
        }

        return $jadArray;
    }

    function downloadBlackberryApplication($url, $appData = array(), $dryRun=false) {

        //check if this app has already been crawled or not
        $getjarUrl = getGetJarAppIdFromURL($url);
        if( isAppAlreadyCrawled($getjarUrl)) {
                echo "----> App $getjarUrl already crawled.. exiting.. \n";
                return false;
        }
        

        /* ********** First, go into the app url ($url) and parse the page for the jad file ********** */
        $html = new simple_html_dom();

        $client = new Zend_Http_Client($url, array(
            'maxredirects' => 0,
            'timeout'      => 30,
            'useragent'     => 'BlackBerry9000/4.6.0.167 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/102'
            )

        );

        $response = $client->request();
        $html->load($response->getBody());
        
        $jadFileUrl = $html->getElementById('form_product_page')->action;

        /**************************************************************************************************/





        $baseDataDir = "data/";
        $dataDir = date('Y-m-d')."/";

        $tmpDir = realpath(Zend_Registry::get('config')->nexva->application->tempDirectory)."/";

        $log = "";
        

        if( !file_exists($baseDataDir.$dataDir )) {
            mkdir($baseDataDir.$dataDir, 0664);
        }


        
        

        /********** Download and parse jad file **********/

        echo "--> Downloading JAD file : ".$jadFileUrl." \n";
        $jadFile = basename($jadFileUrl);    
        copy ( $jadFileUrl, $tmpDir.$jadFile);

        /***************************************************/



        $jadData = parseJadFile($tmpDir.$jadFile);
        
        if( !isset($jadData['MIDlet-Name'])) return false;
        $appDir = $baseDataDir.$dataDir.trim($jadData['MIDlet-Name'])."/";

        if( !file_exists($appDir)) {
            mkdir($appDir, 0664);
        }
        else
            return false; //App already exist? @todo: write to DB when app is downloaded to avoid this

        /********** save thumbnail **********/

        echo "--> Downloading thumbnail file : ".$appData['thumbnail']." \n";
        copy ( $appData['thumbnail'], $appDir.basename($appData['thumbnail']));

        /***************************************************/


        /********** download jar file **********/

        if( isset($jadData['MIDlet-Jar-URL']) ) {
            echo "--> Downloading: ".$jadData['MIDlet-Jar-URL']."\n";
            if( !$dryRun ) copy( $jadData['MIDlet-Jar-URL'], $appDir.basename($jadData['MIDlet-Jar-URL']) );
        }

        if( isset($jadData['RIM-COD-URL'])) {
            echo "--> Downloading: ".$jadData['RIM-COD-URL']."\n";
            if( !$dryRun ) copy( $jadData['RIM-COD-URL'], $appDir.basename($jadData['RIM-COD-URL']) );

        }
                                
        $codFileCounter = 1;
        while(1==1) {  //infinite loop! bet you never seen this before huh?
            
            if( !isset($jadData['RIM-COD-URL-'.$codFileCounter])) break; //no more cod-x files to process... bail out.

            echo "--> Downloading: ".$jadData['RIM-COD-URL-'.$codFileCounter]." \n";

            if( !$dryRun ) copy($jadData['RIM-COD-URL-'.$codFileCounter], $appDir.basename($jadData['RIM-COD-URL-'.$codFileCounter]) );
            
            $codFileCounter++;           
        }

        /***************************************************/


        /********** move the jad file from the temp directory to the right app directory: **********/

        echo "--> Moving jad file \n";
        rename($tmpDir.$jadFile, realpath($appDir).'/'.$jadFile);

        /***************************************************/

        cleanupJadFile(realpath($appDir).'/'.$jadFile);

        $info = "";
        foreach($appData as $key => $value)
            $info .= $key.": ". $value. " \n";
        
        file_put_contents(realpath($appDir).'/info.txt', $info);

        recordAppAsCrawled($getjarUrl);

        



        return true;
            
        
    }

    /**
     * Removes getjar specific stuff from the jad file.
     * WARNING: This will overwrite $jadFile with clean data - no warnings
     *
     * @param string $jadFile
     */
    function cleanupJadFile($jadFile) {

        $jadData = parseJadFile($jadFile);

        $removeKey = false;

        $data = "";
        
        foreach($jadData as $key => $value) {

            switch($key) {
                case 'MIDlet-Install-Notify': //remove these getjar specific crap
                case 'GetJarCampaignID':
                case 'DOWNLOADED_WITH_USER_AGENT':
                case 'DOWNLOAD_SOURCE':
                case 'DOWNLOAD_UID':
                case 'MIDlet-Vendor':
                case 'MIDlet-Info-URL':
                case 'channel':
                    $removeKey = true;
                    break;

                case 'MIDlet-Jar-URL': //these values are abs URLs to their server. We will remove the abs url
                case preg_match('/^RIM-COD-URL/', $key) == 1:
                    $value = trim(basename($value));
                    break;
            } //switch

            if( !$removeKey ) $data .= $key.": ".$value." \n";
            $removeKey = false;
        }


        //echo "Cleaned up data: \n \n ". $data;

        echo "--> Cleaned up JAD file: $jadFile \n \n";


        file_put_contents($jadFile, $data);
    }


    function getGetJarAppIdFromURL($url) {
       //$url = 'm.getjar.com/mobile/3334/opera-mini-web-browser-for-blackberry-9000-bold/?ref=0&amp;lvt=1291794524&amp;sid=NeQSEOFs3&amp;c=85ycg4koea04&amp;lang=en';

       //echo $url ."\n\n";
       $parts = parse_url($url);

       //print_r(path);
       //die();
       

       return $parts['path'];
    }

    function isAppAlreadyCrawled($appUrl) {

       $db  = Zend_Registry::get('db_crawler');

       $sql = "SELECT id FROM getjar_crawled_links WHERE LOWER(url) = '". strtolower($appUrl)."'";

       $row = $db->fetchRow($sql);

       if( $row ) return true; else return false;
       
    }

    function recordAppAsCrawled($appUrl) {

        $db  = Zend_Registry::get('db_crawler');
        
        $sql = "INSERT INTO getjar_crawled_links (url) VALUES ('". strtolower($appUrl)."')";

        try {
            $db->query($sql);
        }
        catch ( Exception $e) {
            return false;
        }

        return true;

        

        
    }

?>
