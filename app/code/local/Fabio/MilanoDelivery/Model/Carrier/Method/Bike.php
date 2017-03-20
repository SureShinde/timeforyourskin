<?php
 
class Fabio_MilanoDelivery_Model_Carrier_Method_Bike extends Fabio_MilanoDelivery_Model_Carrier_Method_Abstract
{
    public function getCost()
    {
        return 1.00;
    }
 
    public function getPrice()
    {
        return 6.00;
    }
}
