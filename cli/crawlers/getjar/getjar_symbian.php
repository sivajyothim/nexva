<?php

    include_once('../simplehtmldom/simple_html_dom.php');
    include_once('../phpQuery.php');
    include_once("../../../application/BootstrapCli.php");

    $html = new simple_html_dom();

    ini_set('memory_limit','128M');
    


    define("MAX_NO_APPS_PER_RUN", 50);
    define("USER_AGENT", 'NokiaN90-1/3.0545.5.1 Series60/2.8 Profile/MIDP-2.0 Configuration/CLDC-1.1');



     if( !file_exists('last_stopped_page_symbian.txt'))
        $page = 0;
     else
        $page = file_get_contents('last_stopped_page_symbian.txt');

     echo "Starting crawler from page: $page \n \n"; sleep(2);

     $numberOfAppsDownloaded = 1;

     $message = "";


     while($numberOfAppsDownloaded <= MAX_NO_APPS_PER_RUN ) {




        $client = new Zend_Http_Client("http://m.getjar.com/mobile-all-software-for-symbian/?ref=0&lvt=1294225461&sid=saggl3gxc7lr&lang=en&o=top&p=$page", array(
            'maxredirects' => 0,
            'timeout'      => 30,
            'useragent'     => USER_AGENT
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




                if( downloadApplication($appUrl, $appInfo) ) {  //successfully downloaded.. increment counter
                    $message .= $numberOfAppsDownloaded. ". ".$appInfo['title']." (".$appInfo['url'].") \n\n";
                    $numberOfAppsDownloaded++;
                }

            }
        }

        echo "----> Number of apps downloaded: $numberOfAppsDownloaded.. Going to next page \n \n";

        $client = null;
        $response =  null;
        $html = null;
        $html = new simple_html_dom();


        $page +=3;

     }

     $message .= "\n \n Total number of applications downloaded: ".$numberOfAppsDownloaded;
     $message .= "\n Next run will begin at page: ".$page;

     file_put_contents('last_stopped_page_symbian.txt', $page);

     mail('jahufar@nexva.com', 'Crawl [Symbian] complete ('.date('Y-m-d h:i:s').")", $message);

     file_put_contents('symbian_crawl_log_'.date('Y-m-d_h-i-s').".log", $message);

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


    function downloadApplication($url, $appData = array(), $dryRun=false) {

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
            'useragent'     => USER_AGENT
            )

        );

        $response = $client->request();
        $html->load($response->getBody());

        $fileUrl = $html->getElementById('form_product_page')->action;


        /**************************************************************************************************/





        $baseDataDir = "data/symbian/";
        $dataDir = date('Y-m-d')."/";

        $tmpDir = realpath(Zend_Registry::get('config')->nexva->application->tempDirectory)."/";

        $log = "";


        if( !file_exists($baseDataDir.$dataDir )) {
            mkdir($baseDataDir.$dataDir, 0664);
        }

        $slugHelper = new Nexva_View_Helper_Slug(); //hack to fix the dir name - makes sure no spaces or funny chars exist - else mkdir() fails

        $appDir = $baseDataDir.$dataDir.trim($slugHelper->slug($appData['title']))."/";

        if( !file_exists($appDir)) {
            mkdir($appDir, 0664);
        }
        else
            return false; //App already exist? @todo: write to DB when app is downloaded to avoid this


        if( ! isValidFile($fileUrl) ) {
            echo "----> Not a native symbian app? ($fileUrl). Ignoring.. \n\n ";
            return false;
        }

        
        /********** save thumbnail **********/

        echo "--> Downloading thumbnail file : ".$appData['thumbnail']." \n";
        if( ! @copy ( $appData['thumbnail'], $appDir.basename($appData['thumbnail'])) )
            echo "Unable to copy thumbnail... images doesn't exist? \n";

        /***************************************************/


        /********** Download apk **********/
        echo "--> Downloading file: ".basename($fileUrl)." \n\n";
        if( !$dryRun ) copy( $fileUrl, $appDir.basename($fileUrl) );

        /***************************************************/


        $info = "";
        foreach($appData as $key => $value)
            $info .= $key.": ". $value. " \n";

        file_put_contents(realpath($appDir).'/info.txt', $info);

        recordAppAsCrawled($getjarUrl);






        return true;


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

       $sql = "SELECT id FROM getjar_crawled_links_symbian WHERE LOWER(url) = '". strtolower($appUrl)."'";

       $row = $db->fetchRow($sql);

       if( $row ) return true; else return false;

    }

    function recordAppAsCrawled($appUrl) {

        $db  = Zend_Registry::get('db_crawler');

        $sql = "INSERT INTO getjar_crawled_links_symbian (url) VALUES ('". strtolower($appUrl)."')";

        try {
            $db->query($sql);
        }
        catch ( Exception $e) {
            return false;
        }

        return true;




    }

    function isValidFile($filename) {
        $parts = pathinfo($filename);

        if( strtolower($parts['extension']) == 'sis' || strtolower($parts['extension']) == 'sisx' ) return true;

        return false;

    }






?>
