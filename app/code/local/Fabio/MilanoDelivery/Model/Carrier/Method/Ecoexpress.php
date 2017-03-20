<?php
 
class Fabio_MilanoDelivery_Model_Carrier_Method_Ecoexpress extends Fabio_MilanoDelivery_Model_Carrier_Method_Abstract
{
    public function getCost()
    {
        return 3.00;
    }
 
    public function getPrice()
    {
        return 6.00;
    }
}