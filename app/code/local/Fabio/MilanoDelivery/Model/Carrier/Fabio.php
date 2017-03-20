<?php
class Fabio_MilanoDelivery_Model_Carrier_Fabio extends Mage_Shipping_Model_Carrier_Abstract
implements Mage_Shipping_Model_Carrier_Interface {
 
    protected $_code = 'fabiocarrier';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if ($this->getConfigFlag('active'))
        {
            // Getting Shipping Destination City
            $checkout = Mage::getSingleton('checkout/session')->getQuote();
            $billAddress = $checkout->getBillingAddress();
            $shipAddress = $checkout->getShippingAddress();
            $billcity =  strtolower($billAddress['city']);
            $shipcity =  strtolower($shipAddress['city']);
            if (isset($shipcity) && ($billcity != $shipAddress))            
                $city = $shipcity;
            else{
                $city = $billcity;
            }
            if ($city != 'milano'){
                return false;
            }
            // Milano_Product Attribute Distinction
            $product_items = $checkout->getAllItems();

            foreach($product_items as $item):
                $attr = Mage::getModel('catalog/product')->load($item->getProduct()->getId())->getData('milano_product');
                if ($attr == 0){
                   return false;
                }
            endforeach;
            // Time Distinction
            $billtime = $billAddress['updated_at'];
            $dt = new DateTime($billtime);
            $time = $dt->format('H:i:s');
            $weekday = Mage::getSingleton('core/date')->date('w');
            if(($weekday == 0 || $weekday == 6) || strtotime($time) >= strtotime('12:00:00'))
            {
                return false;
            }

        }
        else
        {
            return false;
        }
        
        $result = Mage::getModel('shipping/rate_result');
 
        foreach($this->getAllowedMethods() as $methodName => $methodTitle)
        {
            $method = Mage::getModel('shipping/rate_result_method');
            $methodModel = Mage::getModel("fabio_milanodelivery/carrier_method_{$methodName}");
            $method->setCarrier($this->_code);
            $method->setMethod($methodName);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethodTitle($methodTitle);
            $method->setPrice($methodModel->getPrice());
            $method->setCost($methodModel->getCost());
            $result->append($method);
        }
 
        return $result;
    }
 
    public function getAllowedMethods()
    {
        $methods = array('ecoexpress' => 'Eco Express');  
        //$methods = array('superexpress' => 'SuperExpress', 'eco' => 'Eco', 'bike' =>'Bike');  
        return $methods;
    }
}