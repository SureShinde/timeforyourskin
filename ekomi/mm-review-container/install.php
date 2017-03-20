<?php
/**
 * eKOMI MASS MARKET REVIEW CONTAINER
 */
require 'includes/autoload.php';
$params = array(
    'returnInsteadEcho' => false, // to return or echo the html?
    'isStandalone' => true,
    'hasReviewedItem' => false,
    'needsFullHtmlHeader' => true
);
$ekomi = new mmReviewContainer($params);

// Full reviews widget
$ekomi->getInstallPage();