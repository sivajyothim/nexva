<?php         
    include_once("../../application/BootstrapCli.php");

    echo "Querying\n";
    $product    = new Model_Product();
    $query      = $product->select(false)->setIntegrityCheck(false)->from('products', array('id', 'name'))
        ->join('product_meta', 'product_meta.product_id = products.id', array('meta_name', 'meta_value'))
        ->where("meta_name IN ('BRIEF_DESCRIPTION', 'FULL_DESCRIPTION')");
    $products   = $product->fetchAll($query);
    if ($products == null) {
        die('no products');
    }

    echo "Organizing\n";
    $data   = array();
    foreach ($products as $product) {
         $data[$product->id]['name']    = $product->name;       
         $data[$product->id][$product->meta_name]    = $product->meta_value;
    } 
    echo "Data organized, updating now\n";
    
    $productModel    = new Model_Product();
    $db     = Zend_Registry::get('db');
    $db->query('SET NAMES "utf8"')->execute();
    $productIds=    '';
    $count  = 0;
    foreach ($data as $id => $product) {
        $productModel->update(array('name' => $product['name']), 'products.id = ' . $id);
        $productMeta = new Model_ProductMeta();
        $productMeta->setEntityId($id);
        $productMeta->BRIEF_DESCRIPTION = $product['BRIEF_DESCRIPTION'];
        $productMeta->FULL_DESCRIPTION = $product['FULL_DESCRIPTION'];
        
        if (($count % 300) == 0) {
            echo $count . " done\n";
        }
        
        $count++;
    }
    die('All done! Updated ' . count($data) . ' products');
