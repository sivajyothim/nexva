<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Api_RcaController extends Zend_Controller_Action  {
    public function init() 
    {
        // Set the display
        $this->_helper->layout ()->disableLayout ();
        $this->_helper->viewRenderer->setNoRender (true);
        set_error_handler(array($this, 'myErrorHandler'));
        
        if( APPLICATION_ENV == 'production')
        {
            ini_set('display_errors', '0');
        }
        // Get configurations.
        $this->config = Zend_Registry::get('config');
        
    }
    // Error handler 
    public function myErrorHandler($errno, $errstr, $errfile, $errline)
    {
        header('HTTP/1.0 400 Bad Request');
        $this->echoJson(
            array(
                "status" => $errno,
                "message" => $errstr,
                "details" => $errfile.$errline,
            )
        );
        return true;
    }
    
    // All apps or apps based on requested category. This is optional and passed as id=111.
    public function appsAction() 
    {
        // Obtain feature id from request object.
        $appId=$this->_request->id;
        $this->echoJson(
                        array(
                               array(
                                    "app-name"=>"Test App 1",
                                    "app-cost"=>"Free",
                                    "app-category"=>"Test Category 1",
                                    "app-rating"=>"Test Rating 1",
                                ),
                                array(
                                    "app-name"=>"Test App 2",
                                    "app-cost"=>"Free",
                                    "app-category"=>"Test Category 2",
                                    "app-rating"=>"Test Rating 2",
                                ),
                                array(
                                    "app-name"=>"Test App 3",
                                    "app-cost"=>"Free",
                                    "app-category"=>"Test Category 3",
                                    "app-rating"=>"Test Rating 3",
                                )
                            )
                        );
    }
    
    
   
    // Provides summary for a given app id.
    // The app id is passed as a get parameter.
    public function summaryAction() 
    {
        // Obtain app id from request object.
        $appId=$this->_request->id;
        
        $this->echoJson(
                        array(
                            "app-name"=>"Test App",
                            "app-link"=>"www.test.test",
                            "app-cost"=>"Free",
                            "app-category"=>"Test Category",
                            "app-desciption"=>"Test App Description",
                            "app-rating"=>"Test Rating",
                            "app-size"=>"Test size",
                            )
                        );
    }
    
    protected function echoJson($json, $halt=1) 
    {
            $this->getResponse()
                ->setHeader('Content-type', 'application/json')
                ;
            echo json_encode($json);
            if( $halt ) die();

    }    
}
?>
