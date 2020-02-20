<?php
/**
 * Created by JetBrains PhpStorm.
 * User: viraj
 * Date: 2/18/14
 * Time: 12:53 PM
 * To change this template use File | Settings | File Templates.
 */
class Nexva_View_Helper_AppStoreScore extends Zend_View_Helper_Abstract{

    function AppStoreScore($id){
        
        
        return 0;

        //let cache do it's thing
        $cache          = Zend_Registry::get('cache');
        $cacheKey       = 'APP_SCORE_' . trim($id);
        $cacheKey       = str_ireplace('-', '_', $cacheKey);

        $url = 'http://index.appbackr.com/v1/apps/info?api_key=0fd8f32d57d34ffa52b07eb5839b3149&package_name='.$id;

        if (($jsonResponse = $cache->get($cacheKey)) === false) {
            $jsonResponse = file_get_contents($url);
        }
        
        $response = json_decode($jsonResponse, True);
        return $response['response'][0]['appscore'];
    }
}