<?php
 
class Fabio_MilanoDelivery_Model_Carrier_Method_SuperExpress extends 
Fabio_MilanoDelivery_Model_Carrier_Method_Abstract 
{
    public function getCost()
    {
        return 5.00;
    }
 
    public function getPrice()
    {
        return 6.00;
    }
}