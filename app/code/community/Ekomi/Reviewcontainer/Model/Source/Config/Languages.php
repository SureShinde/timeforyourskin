<?php

class Ekomi_Reviewcontainer_Model_Source_Config_Languages
{
    /**
     * Provide available options as a value/label array
     *
     * @return array
     */
    public function toOptionArray()
    {
        // Use the shops model
        $helper = Mage::getModel('reviewcontainer/helper');

        // Get list of shops with their IDs and names
        $languages = $helper->getAvailableLanguages();

        // return values
        $options = array();
        foreach ($languages as $language) {
            $options[] = array('value' => $language['code'], 'label' => $language['name']);
        }

        return $options;
    }
}