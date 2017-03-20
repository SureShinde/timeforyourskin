<?php

class Ekomi_Reviewcontainer_Model_Source_Config_Shops
{
    /**
     * Provide available options as a value/label array
     *
     * @return array
     */
    public function toOptionArray()
    {
        // return values
        $options = array();

        // Use the shops model
        $reviews = Mage::getModel('reviewcontainer/helper');

        // Get list of shops with their IDs and names
        $shops = $reviews->getEkomiShopIDs();

        foreach ($shops as $shop) {
            $options[] = array('value' => $shop['ekomi_interface_id'], 'label' => $shop['ekomi_interface_id']);
        }

        return $options;
    }
}