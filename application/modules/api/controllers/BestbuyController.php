<?php
/**
 * ************************* FOR BESTBUY DEMO PURPOSES ONLY ***************************
 *
 * This API was written for a quick and dirty demo for BestBuy Mobile for 27th Mar '11
 * 
 * Please review and remove if not needed.
 * 
 * @author jahufar
 * 
 * This controller is now hooked up to \demos\bestbuy\admin which works as its admin
 * 
 *  @author John
 *  
 * 
 * 
 ***************************************************************************************/


class Api_BestbuyController extends Zend_Controller_Action  {
    
    private $__dd;


    private $__allowedDeviceIds = array('JS001', '000000000000000', '354635030438464');
    
    public function init() {
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender (true);
        
        //load up the IMEI and the products from the fake admin
        $data       = $this->loadAdminData();
        $this->__allowedDeviceIds   = array_keys($data);
    }


    public function indexAction() {

        $this->getResponse()->setHttpResponseCode(404);
        $this->getResponse()->setHeader('X-NEXVA-ERROR','No method call given.'); 

        //$o = new Api_Model_Bestbuy_10_Device();


        //$method = new ReflectionMethod('Api_Model_Bestbuy_10_Device', 'registerDevice');
        //echo $this->getDocComment($method->getDocComment(), '@nexva-note');

        //if( $this->docCommentTagExists($method->getDocComment(), '@nexva-service')) echo "This is a service method"; else echo "This is not a service method";
        
        //if( $this->docCommentTagExists($method->getDocComment(), '@nexva-requires-auth')) echo "This method requires authentication"; else echo "This method does not require auth";



        //Zend_Debug::dump($method->getDocComment());
        

        //http://api.nexva.com/bestbuy/1.0/device/registerDevice/id/12343.html


    }

    public function validatedeviceAction() {

        $response = array();

        $this->validateDevice($this->_request->id);

        //device was found
        $response['status'] = 1;
                      
        echo json_encode ( $response );


    }

    public function getavailablecontentAction() {
        $this->validateDevice($this->_request->id);
        $response           = $this->getAvailableContent($this->_request->id);
        $filtered_reponse   = array();
        if( $this->_request->type != "" ) { //filter out my type
            $i=0;
            foreach($response as $r) {
                //Zend_Debug::dump($r);
                if( strtoupper($this->_request->type) == strtoupper($r['type']) ) {
                    $filtered_reponse[$i] = $r;
                    $i++;
                }
            }
        }

        if( $this->_request->type == "" ) {
            echo json_encode ( $response );
        } else {
            echo json_encode ( $filtered_reponse );
        }
                    
    }


    protected function validateDevice($id) {  
      if(!in_array($id, $this->__allowedDeviceIds)) {
            $response['error'] = "Unauthorized device. No applications found for this device";
            $response['status'] = 0;

            //$this->getResponse()->setHttpResponseCode(401);
            //$this->getResponse()->setHeader('X-NEXVA-ERROR',$response['error']);
            
            echo json_encode($response);
            flush();
            
            die();
      }
    }


    function getDocComment($str, $tag = '')
    {
        if (empty($tag))
        {
            return $str;
        }

        $matches = array();
        preg_match("/".$tag."(.*)(\\r\\n|\\r|\\n)/U", $str, $matches);

        if (isset($matches[1]))
        {
            return trim($matches[1]);
        }

        return '';
    }

    function docCommentTagExists($str, $tag) {

        $matches = array();
        $r = preg_match("/".$tag."(.*)(\\r\\n|\\r|\\n)/U", $str, $matches);

        //Zend_Debug::dump($matches); die();

        //if (isset($matches[1])) return true;
        //echo $r;
        if ($r > 0 && isset($matches[1]) && $matches[1] == '' ) return true;
        return false;


    }


    public function getAction() {

        die( "in GET!!");
    }


    protected function getAvailableContent($id = null) {

        $content[2]['id'] = 3;
        $content[2]['type'] = 'MUSIC';
        $content[2]['artist'] = 'Mixalis Xatzigiannis & Reamonn';
        $content[2]['title'] = 'Tonight';
        $content[2]['album'] = 'Mad Video Music Awards 2007';
        $content[2]['album_art']        = 'http://nexva.com/demos/bestbuy/content/music/album1/albumart_1_128.jpg';
        $content[2]['album_art_thumb']  = 'http://nexva.com/demos/bestbuy/content/music/album1/albumart_1_48.jpg';
        $content[2]['length'] = '03:12';
        $content[2]['url']  = 'http://nexva.com/demos/bestbuy/content/music/album1/tonight.mp3';
        $content[2]['bio']  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius egestas magna, non condimentum ipsum tempus non. Etiam iaculis luctus augue non tempor. ';
        $content[2]['genre']= 'Pop Rock';
        $content[2]['release']= 'Feb 19, 2008';
        
        $content[3]['id'] = 4;
        $content[3]['type'] = 'MUSIC';
        $content[3]['artist'] = 'The Raconteurs';
        $content[3]['title'] = 'Salute Your Solution';
        $content[3]['album'] = 'Consolers Of The Lonely';
        $content[3]['album_art']    = 'http://nexva.com/demos/bestbuy/content/music/album1/albumart_2_128.jpg';
        $content[3]['album_art_thumb']  = 'http://nexva.com/demos/bestbuy/content/music/album1/albumart_2_48.jpg';
        $content[3]['length'] = '03:12';
        $content[3]['url'] = 'http://nexva.com/demos/bestbuy/content/music/album1/salute_your_solution.mp3';
        $content[3]['bio']  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius egestas magna, non condimentum ipsum tempus non. Etiam iaculis luctus augue non tempor. ';
        $content[3]['genre']= 'Garage Rock';
        $content[3]['release']= 'Feb 19, 2009';

        $content[4]['id'] = 5;
        $content[4]['type'] = 'MUSIC';
        $content[4]['artist'] = 'Alter Bridge';
        $content[4]['title'] = 'Wonderful Life';
        $content[4]['album'] = 'ABIII';
        $content[4]['album_art'] = 'http://nexva.com/demos/bestbuy/content/music/album1/albumart_3_128.jpg';
        $content[4]['album_art_thumb']  = 'http://nexva.com/demos/bestbuy/content/music/album1/albumart_3_48.jpg';
        $content[4]['length'] = '03:12';
        $content[4]['url'] = 'http://nexva.com/demos/bestbuy/content/music/album1/wonderful_life.mp3';
        $content[4]['bio']  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius egestas magna, non condimentum ipsum tempus non. Etiam iaculis luctus augue non tempor. ';
        $content[4]['genre']= 'Alternative Rock';
        $content[4]['release']= 'March 29, 2010';
        
        $content[5]['id'] = 6;
        $content[5]['type'] = 'MUSIC';
        $content[5]['artist'] = 'Alicia Keys';
        $content[5]['title'] = 'Try sleep with a broken heart';
        $content[5]['album'] = 'The Best of Alicia Keys';
        $content[5]['album_art'] = 'http://nexva.com/demos/bestbuy/content/music/album1/albumart_3_128.jpg';
        $content[5]['album_art_thumb']  = 'http://nexva.com/demos/bestbuy/content/music/album1/albumart_3_48.jpg';
        $content[5]['length'] = '03:12';
        $content[5]['url'] = 'http://nexva.com/demos/bestbuy/content/music/album1/tonight.mp3';
        $content[5]['bio']  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce varius egestas magna, non condimentum ipsum tempus non. Etiam iaculis luctus augue non tempor. ';
        $content[5]['genre']= 'Soul';
        $content[5]['release']= 'April 21, 2008';

        
        $applications   = $this->getAvailableApplications($id);
        foreach ($applications as $app) {
            $content[]  = $app;
        }
        return $content;
    }

    private function getAvailableApplications($id) {
        $proModel       = new Model_Product();
        $data           = $this->loadAdminData();
        $proIds         = $data[$id]['PRODUCTS'];
        $content        = array();
        $count          = 0;
        foreach ($proIds as $proid => $proName) {
            $product    = $proModel->getProductDetailsById($proid);
            if (!empty($product)) {
                $content[$count]['id']          = $product['id'];
                $content[$count]['type']        = 'APPLICATION';
                $content[$count]['name']        = $product['name'];
                $content[$count]['desc_brief']  = $product['desc_brief'];
                $content[$count]['desc_full']   = strip_tags(substr($product['desc'], 0, 300));
                
                //prepare proper thumbnail
                //http://thor.mobilereloaded.com/vendors/phpThumb/phpThumb.php?src=/product_visuals/staging/SurfervsWave10Tsurferswavetheme.gif&w=50&h=70&aoe=0&fltr[]=ric|0|0&q=100&f=png
                $phpthumbBase   = 'http://' . Zend_Registry::get('config')->nexva->application->base->url . '/vendors/phpThumb/phpThumb.php?src=';      
                $content[$count]['thumbnail'] = $phpthumbBase . $product['thumb'] . '&w=50&h=50';
                
                if (isset($product['screenshots'][0])) {
                    $image = $product['screenshots'][0];
                } else {
                    $image  = $product['thumb'];
                }
                
                $content[$count]['image']    = $phpthumbBase . $image . '&w=128&h=128';
                $content[$count]['url'] = 'http://' . Zend_Registry::get('config')->nexva->application->mobile->url . '/app/download/id/' . $product['id']; 
                $count++;
            } 
        }
        return $content;        
    }
    
    
    public function getcontentAction()  {

        $this->validateDevice($this->_request->id);

        $content = $this->getAvailableContent();

        die("-->".$this->_request->contentid );

        if( $this->_request->contentid == "" || !isset($content[$this->_request->contentid+1]) ) {

            $this->getResponse()->setHttpResponseCode(404);
            $this->getResponse()->setHeader('X-NEXVA-ERROR','The content you requested does not exist on this server');
            
            die();



        }

        $response = $content[$this->_request->contentid+1];
        echo json_encode ( $response );


        



    }


    protected function findContent($id) {
        $content = $this->getAvailableContent();

        foreach ($content as $c ) {
            if( $c['id'] == $id ) return $c;
        }
        
        return false;

    }

    private function loadAdminData() {
        $filename       = APPLICATION_PATH . '/../tmp/bestbuy-demo.txt';
        
        if (!is_file($filename)) {
            $this->getResponse()->setHttpResponseCode(401);
            $this->getResponse()->setHeader('X-NEXVA-ERROR','Could not load data file.');
            die();
        }
        $data           = unserialize(file_get_contents($filename));
        return $data;
    }

    



//$method = new ReflectionMethod('Example', 'myMethod');

// will return Hello World!
//echo getDocComment($method->getDocComment(), '@DocTag');



    

    


    


    
}
?>
