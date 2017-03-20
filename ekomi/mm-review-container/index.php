<?php
/**
 * Debugging
 * Uncomment to see all PHP errors
 */
// error_reporting(-1);
// ini_set('display_errors', 'On');

/**
 * eKOMI MASS MARKET REVIEW CONTAINER
 */
require_once 'includes/autoload.php';
$params = array(
    'returnInsteadEcho' => false, // to return or echo the html?
    'productID' => 'Custom-normal-Design',//array('SP4550', '2179'), //'0049', // //050-FAL-3000
    'productName' => 'Test name',
    'isStandalone' => true,
    'hasReviewedItem' => false,
    'needsFullHtmlHeader' => true,
    //'shopID' => 23292,
    'languageLocale' => 'fr'
);
$eKomi = new mmReviewContainer($params);

// What to display for testing
$get = isset($_GET['get']) ? $_GET['get'] : 'productreviews';

/*
 * getProductReviews
 * getMiniStarsCounterAvg
 * getMiniStarsCounter
 * getProductTotalReviews
 *
 * */
switch ($get) {
    case 'producttotalreviews':
        // Total product reviews in the tab
        $html = $eKomi->getProductTotalReviews();
        echo $html;

        break;
    case 'productstarsavg':
        // Total product reviews in the tab
        $html = $eKomi->getProductStarsAvg();
        echo $html;

        break;
    case 'ministarscounter':
        // Generate stars and counter mini widget
        $html = $eKomi->getMiniStarsCounter();
        echo $html;

        break;
    case 'ministarscounteravg':

        // Generate stars and counter mini widget with average
        $html = $eKomi->getMiniStarsCounterAvg();
        echo $html;
        break;
    default:
        // Full reviews widget
        $html = $eKomi->getProductReviews();
        echo $html;

        break;
}

