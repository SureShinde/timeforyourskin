<?php

class Ekomi_Reviewcontainer_Model_Helper extends Ekomi_Reviewcontainer_Model_Reviews
{

    /**
     * The constructor.
     *
     * @param $modelParameters
     */
    public function __construct($modelParameters)
    {
        // Set params
        $this->modelParams = $modelParameters;

        /**
         * eKOMI MASS MARKET REVIEW CONTAINER
         */
        $config_file = Mage::getBaseDir('base') . DS . 'ekomi/mm-review-container/config.php';
        if (!file_exists($config_file)) {
            $this->isConfigured = false;
        }

        if ($this->isConfigured) {

            $mmReviewContainerAutoloader = Mage::getBaseDir('base') . DS . 'ekomi/mm-review-container/includes/autoload.php';
            require_once($mmReviewContainerAutoloader);

            // Fix magento autoload probleme when on developper mode: Warning: include(xxx.php): failed to open stream..
            $base = Mage::getBaseDir('base') . DS;
            $includePathIncludes = $base . 'ekomi/mm-review-container/includes/';
            $includePathIncludesLib = $base . 'ekomi/mm-review-container/includes/lib/';
            $includePathIncludesLibI10n = $base . 'ekomi/mm-review-container/includes/lib/l10n/';
            set_include_path(get_include_path()
                . PS . $includePathIncludes
                . PS . $includePathIncludesLib
                . PS . $includePathIncludesLibI10n
            );

            // Build params for base container
            $params = array(
                'returnInsteadEcho' => true
            );

            // Call the base container
            $this->eKomi = new mmReviewContainer($params);
        }
    }

    /**
     * Get saved eKomi shop idies from the db
     *
     * @return array
     */
    public function getEkomiShopIDs()
    {
        // Get interface idies
        if ($this->isConfigured) {
            return $this->eKomi->getShopsIDs();
        }

        return array();
    }

    /**
     * Get available languages
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        // Get available languages
        if ($this->isConfigured) {
            return $this->eKomi->getAvailableLanguages();
        }

        return array();
    }
}