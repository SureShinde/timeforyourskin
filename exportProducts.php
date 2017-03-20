<?php
require_once 'app/Mage.php';
Mage::app();
$products = Mage::getModel("catalog/product")->getCollection();
$products->addAttributeToFilter('status', 1);
$fopen = fopen('products.csv', 'w');
$csvHeader = array("id","sku", "name", "url_path");// Add the fields you need to export
fputcsv( $fopen , $csvHeader,",");
foreach ($products as $product){
    $id = $product->getId();
	$sku = $product->getSku();
	$name = $product->getName();
	$url_path = $product->getUrlPath();
    fputcsv($fp, array($id,$sku,$name, $url_path), ",");//Add the fields you added in csv header
}
fclose($fopen );