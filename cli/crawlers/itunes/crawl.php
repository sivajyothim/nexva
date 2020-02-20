<?php
    include_once('../simplehtmldom/simple_html_dom.php');
    include_once("../../../application/BootstrapCli.php");

    $html = new simple_html_dom();


    //$contents = file_get_contents('sample.html');
    $contents = file_get_contents('http://itunes.apple.com/us/app/zombie-highway/id376412160?mt=8');

    //die($contents);

    $html->load($contents);

    //parseiTunesAppPage($contents);

    $return = parseiTunesAppPage($contents);

    Zend_Debug::dump($return);
    die();
    
    /**
    $categories =  $html->find('#genre-nav ul a');
    foreach($categories as $a ) {
        echo $a->innertext."(".$a->href.")".PHP_EOL;

    }

    $topApps =  $html->find('#selectedcontent ul a');
    foreach($topApps as $a ) {
        echo $a->innertext."(".$a->href.")".PHP_EOL;
    }
     *
     */

     $requirements =  $html->find('#left-stack div p',0);
     die($requirements->plaintext);
     


     $categories =  $html->find('li .genre',0);
     die($categories->innertext);
     
     foreach($categories as $category ) 
        echo PHP_EOL.$category->innertext.PHP_EOL;

     die();
     
     
     $appName =  $html->find('#title h1',0)->innertext;
     $developer =  substr($html->find('#title h2',0)->innertext, 3, strlen($html->find('#title h2',0)->innertext));

     echo PHP_EOL."$appName -> $developer".PHP_EOL;
     
     $price =  substr($html->find('.price',0)->innertext,1,strlen($html->find('.price',0)->innertext));
     echo PHP_EOL.$price.PHP_EOL;
          
     
     echo PHP_EOL."Thumbnail: ".PHP_EOL;
     $thumb =  $html->find('.artwork img',0);


     echo PHP_EOL."Description: ".PHP_EOL;
     $description =  $html->find('.center-stack p',0)->innertext;
     echo $description;

     $screenshots =  $html->find('.iphone-screen-shots .lockup img');

     echo PHP_EOL."Screenshots: ".PHP_EOL;
     foreach($screenshots as $a ) {
        echo $a->src.PHP_EOL;
     }

     //<div num-items="5" class="content iphone-screen-shots"


     function parseiTunesAppPage($contents) {

         $html = new simple_html_dom();
         $html->load($contents);

         $data = array();

         $requirements =  $html->find('#left-stack div p',0);
         $data['requirements'] = trim($requirements->plaintext);


         $keywords = $html->find('meta[name="keywords"]',0)->content;
         $data['keywords']  = $keywords;

         if( preg_match('/iPhone/i', $data['requirements']) )
            $data['compatible']['iphone'] = true;

         if( preg_match('/iPod touch/i', $data['requirements']) )
            $data['compatible']['itouch'] = true;

         if( preg_match('/iPad/i', $data['requirements']) )
            $data['compatible']['ipad'] = true;

        $re1='.*?';	# Non-greedy match on filler
        $re2='(\\d)';	# Major version
        $re3='(\\.)';	# .
        $re4='(\\d)';	# Minor version
        $re5='(\\.)?'; # .
        $re6='(\\d)?'; # Revision

        if (preg_match_all ("/".$re1.$re2.$re3.$re4.$re5.$re6."/is", $data['requirements'], $matches)) {
            $major=$matches[1][0];
            $c1=$matches[2][0];
            $minor=$matches[3][0];
            $c2=$matches[4][0];
            $revision=$matches[5][0];
            $data['os_version']['minimum']= trim("$major$c1$minor$c2$revision\n");
        }
        else
            $data['os_version']['minimum'] = false;

        $re1='.*?';	# Non-greedy match on filler
        $re2='(or)';
        $re3='.*?';	# Non-greedy match on filler
        $re4='(later)';

        if (preg_match_all ("/".$re1.$re2.$re3.$re4."/is", $data['requirements'], $matches))
        {
            $word1=$matches[1][0];
            $word2=$matches[2][0];
            $data['os_version']['or_later'] = true;
        }
        else
            $data['os_version']['or_later'] = false;
       
        $universalApp =  $html->find('#left-stack div span',3);
        $data['universal_app'] = preg_match('/designed for both iPhone and iPad/i', $universalApp) > 0;

        $size =  $html->find('#left-stack div ul li ',5)->plaintext;
        $size = substr($size, 7, strlen($size) );
        $data['size'] = $size;

        $languages =  $html->find('#left-stack div ul li',6)->plaintext;
        $languages = substr($languages, 10, strlen($languages));
        $languages = explode(', ', $languages);
        $languages = array_map('trim', $languages);
        $data['languages'] = $languages;

        $version =  $html->find('#left-stack div ul li',4)->plaintext;
        $data['version'] = $version;
        
        $categories =  $html->find('li .genre',0);
        $data['category'] = $categories->innertext;
  
        $appName =  $html->find('#title h1',0)->innertext;
        $developer =  substr($html->find('#title h2',0)->innertext, 3, strlen($html->find('#title h2',0)->innertext));

        $data['app_name'] = $appName;
        $data['developer']['name'] = $developer;

        $developerHomepage = $html->find('.app-links a',0);
        $data['developer']['homepage'] = $developerHomepage->href;

        $developerSupport = $html->find('.app-links a',1);
        $data['developer']['support'] = $developerSupport->href;
        
        $price = $html->find('.price',0)->innertext;

        if( preg_match('/^Free/', $price) )
            $data['price'] = 0;
        else {
            $price =  substr($html->find('.price',0)->innertext,1,strlen($html->find('.price',0)->innertext));
            $data['price'] = $price;
        }
    
        $thumb =  $html->find('.artwork img',0);
        $data['thumbnail'] = $thumb->src;

        $description =  $html->find('.center-stack p',0)->innertext;
        $data['description'] = $description;

        $screenshots =  $html->find('.iphone-screen-shots .lockup img');

        $i = 0;
        foreach($screenshots as $screenshot ) {
            $data['screenshots'][$i] =$screenshot->src;
            $i++;
        }

        return $data;

     }




     
     








  








    

    


