<?php

/**
 * MySQL settings
 */

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** MySQL database name */
define('DB_NAME', 'timefor2_igorexpert');

/** MySQL database username */
define('DB_USER', 'timefor2_igor');

/** MySQL database password */
define('DB_PASSWORD', 'igorwormar@1012');

/**
 * Misc Settings
 */

/** The client eKomi interface ID . */
define('EKOMI_INTERFACE_ID', '90614');

/** The client eKomi interface password . */
define('EKOMI_INTERFACE_PASSWORD', '5bb620338469e0ce0ea19c7ba');

/** What to display in case of 0 product reviews found: show_message | show_nothing | show_empty_container . */
define('EKOMI_NO_REVIEWS_FOUND_ACTION', 'show_message');

/** No product reviews found message: You may use ##product_name## and ##product_id## placeholders and they will be replaced by actual product values. */
define('EKOMI_NO_REVIEWS_FOUND_MESSAGE', 'No product reviews received for product ##product_name## with product ID ##product_id##.');

/** The application defualt locale (language) . */
define('EKOMI_APP_LOCALE', 'it');

/** The path to application root . */
define('EKOMI_APP_URI', '/ekomi/mm-review-container/');

/** The path to application root . */
define('EKOMI_CRON_JOB_DELAY', '24');

/** Deault page number on queries. */
define('EKOMI_REVIEWS_PER_PAGE', '5');

/** Disable or enable HTML on comments. */
define('EKOMI_HTML_ON_COMMENTS', '0');

/** Allow  votes on reviews (review helpfulness) */
define('EKOMI_VOTES_ON_REVIEWS', '1');

/** Link to ekomi; certificate => certificate page, 'nothing' => no link, 'website' => an ekomi url*/
define('EKOMI_URL', 'website');

/** Custom styles */
define('EKOMI_AUTOLOAD_ON_SCROLL', '0');
