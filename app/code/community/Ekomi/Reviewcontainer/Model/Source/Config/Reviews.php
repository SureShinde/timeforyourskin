<?php

class Ekomi_Reviewcontainer_Model_Source_Config_Reviews
{
    /**
     * Provide available options as a value/label array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'id', 'label' => 'Product ID'),
            array('value' => 'sku', 'label' => 'Product SKU'),
        );
    }
}