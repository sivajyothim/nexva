<?php
error_reporting(E_ALL);

function setProductStatusToIncomplete($id) 
{
    $product = new Model_Product();
    $tmpArr['id'] =$id;
    $tmpArr['status'] = 'INCOMPLETE';
    $product->save($tmpArr);
}

include_once("../application/BootstrapCli.php");
$productBuild = new Model_ProductBuild();
$results = $productBuild->getProductBuildsWithZeroFileSize();
if(count($results)==0){
    die();
}
$text = "<html>
<body>";
$text .= "<p style=\"background-color:red;\">System found 0 byte files uploaded for some products and marked them as INCOMPLETE. Please find the product details below:</p>";
$text .= "<table width=\"95%\" style=\"border: 1px solid black;border-collapse:collapse; \" ><thead>
<tr bgcolor=\"FF8900\">
    <th style=\"border: 1px solid black;\">name</th>
    <th style=\"border: 1px solid black;\">Pro_id</th>
    <th style=\"border: 1px solid black;\">Build_Id</th>
    <th style=\"border: 1px solid black;\">user_id</th>
    <th style=\"border: 1px solid black;\">username</th>
    <th style=\"border: 1px solid black;\">email</th>
    <th style=\"border: 1px solid black;\">filename</th>
    <th style=\"border: 1px solid black;\">filesize</th>
</tr></thead><tbody>";

foreach ($results as $value) {
    $text .="<tr>";
    $text.="<td style=\"border: 1px solid black;\" >$value->name</td>";
    $text.="<td style=\"border: 1px solid black;\" >$value->Pro_id</td>";
    $text.="<td style=\"border: 1px solid black;\" >$value->Build_Id</td>";
    $text.="<td style=\"border: 1px solid black;\" >$value->user_id</td>";
    $text.="<td style=\"border: 1px solid black;\" >$value->username</td>";
    $text.="<td style=\"border: 1px solid black;\" >$value->email</td>";
    $text.="<td style=\"border: 1px solid black;\" >$value->filename</td>";
    $text.="<td style=\"border: 1px solid black;\" >$value->filesize</td>";
    $text.="</tr>";
    
    // Mark the product disable to prevent billing for 0 bytes file purchase.
    setProductStatusToIncomplete($value->Pro_id);
}
$text .= "</tbody></table>";
$text .="</body></html>";

$config = Zend_Registry::get('config');
$config->nexva->application->content_admin->contact;
$mailer = new Nexva_Util_Mailer_Mailer();
$mailer->addTo($config->nexva->application->content_admin->contact, "Support group")
        ->setSubject('ACTION REQUIRED! ZERO BUILD FILE !')
        ->setBodyHtml($text)
        ->send();

?>
