<?php
    include_once('../simplehtmldom/simple_html_dom.php');
    include_once("../../../application/BootstrapCli.php");

    $html = new simple_html_dom();

    $select = Zend_registry::get('db')->select()
        ->from('product_device_saved_attributes', array('build_files.filename'))

        ->join('product_builds', 'product_device_saved_attributes.build_id = product_builds.id')
        ->join('products', 'products.id = product_builds.product_id')
        ->join('build_files', 'product_builds.id = build_files.build_id')
        
        ->where('product_device_saved_attributes.device_attribute_definition_id =  1')
        ->where("product_device_saved_attributes.value =  'iPhone OS'")
        ->where("product_builds.build_type =  'urls'")
        ->where("product_builds.device_selection_type =  'BY_ATTRIBUTE' ")
        ->where("products.status = 'APPROVED'")
        ->where("products.deleted =  0");


        $rows = Zend_Registry::get('db')->fetchAll($select);

        //return Zend_debug::dump($rows);

     
        $productMeta = new Model_ProductMeta();

        foreach($rows as $row) {
            //Zend_debug::dump($row);die();
            $productMeta->setEntityId($row->product_id);

            if( $productMeta->ALT_CP_NAME == "") {

                try {
                    $client = new Zend_Http_Client($row->filename, array(
                        'maxredirects' => 5,
                        'timeout'      => 300
                        )

                    );

                    $response = $client->request();
                } catch(Exception $e) {echo "Error in URL: ".$row->filename.PHP_EOL; continue;}
                
                $html->load($response->getBody());

                $r =  $html->find('#title h2',0);

                if( isset($r->innertext) && $r->innertext != "") {

                    $cpName = trim(substr($r->innertext, 3, strlen($r->innertext)));
                    
                    $productMeta->ALT_CP_NAME = $cpName;
                    echo $cpName. PHP_EOL;

                }



                $client = null;

                flush();
            }      
        }

        die();
               
    




    $client = new Zend_Http_Client("http://itunes.apple.com/us/app/heywire-text-worldwide-free/id383949089?mt=8", array(
        'maxredirects' => 5,
        'timeout'      => 300
        )

    );

    $response = $client->request();
    
    $html->load($response->getBody());



    $r =  $html->find('#title h2',0);

    if( isset($r->innertext) && $r->innertext != "")
        echo substr($r->innertext, 3, strlen($r->innertext));
        
    die();


/**
SELECT
product_device_saved_attributes.id,
product_device_saved_attributes.build_id,
product_builds.product_id,
products.name,
product_builds.name,
build_files.filename
FROM
product_device_saved_attributes
Inner Join product_builds ON product_device_saved_attributes.build_id = product_builds.id

Inner Join products ON products.id = product_builds.product_id
 *
Inner Join build_files ON product_builds.id = build_files.build_id
WHERE
product_device_saved_attributes.device_attribute_definition_id =  1 AND
product_device_saved_attributes.value =  'iPhone OS' AND
product_builds.build_type =  'urls' AND
product_builds.device_selection_type =  'BY_ATTRIBUTE'

 * 
 */






?>