<?php

class Ekomi_Reviewcontainer_Block_Product_Container extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    /**
     * Get the rei
     * @return string
     */
    public function getProductReviews()
    {
        // Init the reviews model
        $reviewsModel = $this->getReviewsModel();

        // Call the full container
        $reviews = $reviewsModel->getProductReviews();

        return $reviews;
    }

    public function showMiniStarsCounter()
    {
        // Init the reviews model
        $reviewsModel = $this->getReviewsModel();

        // Call the mini stars container
        $reviews = $reviewsModel->modelMiniStarsCounter();

        return $reviews;
    }

    public function getReviewsModel()
    {
        // Get the widget parameters for review by
        $queryReviewsBy = $this->getData('query_reviews_by');

        // Get the widget parameters for shop id
        $shopID = $this->getData('shop_id');

        // Get the widget parameters for language
        $languageLocale = $this->getData('languageLocale');

        // Build model parameters
        $modelParameters = array(
            'queryReviewsBy' => $queryReviewsBy,
            'shopID' => $shopID,
            'languageLocale' => $languageLocale
        );

        $reviewsModel = Mage::getModel('reviewcontainer/reviews', $modelParameters);

        return $reviewsModel;
    }
}