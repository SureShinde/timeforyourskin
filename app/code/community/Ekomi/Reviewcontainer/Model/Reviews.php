<?php

class Ekomi_Reviewcontainer_Model_Reviews
{

    /**
     * The base container.
     *
     * @var mmReviewContainer
     */
    protected $eKomi;

    /**
     * Will be true if config.php exists.
     *
     * @var bool
     */
    protected $isConfigured = true;

    /**
     * Params will be retrieved from widget settings.
     *
     * @var array
     */
    public $modelParams = array();

    /**
     * The constructor.
     *
     * @param $modelParameters
     */
    public function __construct($modelParameters)
    {
        // If we are not in a product, return error
        if (!$this->isCorrectContext()) {

            $html = '<pre class="xdebug-var-dump" dir="ltr"><span style="color:#cc0000;text-align:center;display: block;">sdfgdfgThe ekomi reviews widget works only when displayed inside of a product block: please edit your widget layout settings.</span></pre>';

            echo $html;

            return false;
        }

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

            // Check Magento registry for product ID
            $product_id = Mage::registry('current_product')->getId();

            // The product data
            $_product = Mage::getModel('catalog/product')->load($product_id);

            // Call reviews by SKU or product ID?
            switch ($this->modelParams['queryReviewsBy']) {
                case 'id':
                    $queryReviewsBy = $_product->getId();
                    break;
                case 'sku':
                    $queryReviewsBy = $_product->getSku();
                    break;
                default:
                    $queryReviewsBy = $_product->getId();
            }

            // Build params for base container
            $params = array(
                'productID' => $queryReviewsBy,
                'productName' => $_product->getName(),
                'returnInsteadEcho' => true,
                'hasReviewedItem' => false,
                'shopID' => $this->modelParams['shopID'],
                'languageLocale' => $this->modelParams['languageLocale']
            );

            // Call the base container
            $this->eKomi = new mmReviewContainer($params);
        }
    }

    /**
     * Checks if we are in a product conext as it makes no sense to show the product reviews outside of a
     * product context.
     *
     * @return bol
     */
    public function isCorrectContext()
    {
        return (Mage::registry('current_product'));
    }

    /**
     * Generates the product review container.
     *
     * @return string
     */
    public function getProductReviews()
    {
        // If we are not in a product, return error
        if ($this->isCorrectContext()) {
            if ($this->isConfigured) {
                return $this->eKomi->getProductReviews();
            }
            return '<!-- Please first configure the eKomi Review Container! -->';
        }
    }

    /**
     * Generates the product review container's mini stars.
     *
     * @return string
     */
    public function modelMiniStarsCounter()
    {
        // If we are not in a product, return error
        if ($this->isCorrectContext()) {
            if ($this->isConfigured) {
                return $this->eKomi->getMiniStarsCounter();
            }
            return '<!-- Please first configure the eKomi Review container! -->';
        }
    }
}