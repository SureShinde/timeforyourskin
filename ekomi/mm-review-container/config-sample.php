<?php

/**
 * MySQL settings
 */

/** MySQL hostname */
define('DB_HOST', '');

/** MySQL database name */
define('DB_NAME', '');

/** MySQL database username */
define('DB_USER', '');

/** MySQL database password */
define('DB_PASSWORD', '');

/**
 * Misc Settings
 */

/** The client eKomi interface ID . */
define('EKOMI_INTERFACE_ID', '');

/** The client eKomi interface password . */
define('EKOMI_INTERFACE_PASSWORD', '');

/** What to display in case of 0 product reviews found: show_message | show_nothing | show_empty_container . */
define('EKOMI_NO_REVIEWS_FOUND_ACTION', 'show_message');

/** No product reviews found message: You may use ##product_name## and ##product_id## placeholders and they will be replaced by actual product values. */
define('EKOMI_NO_REVIEWS_FOUND_MESSAGE', 'So far no product reviews given.');

/** The application defualt locale (language) . */
define('EKOMI_APP_LOCALE', 'en');

/** The path to application root . */
define('EKOMI_APP_URI', '/');

/** The path to application root . */
define('EKOMI_CRON_JOB_DELAY', '24');

/** Deault page number on queries. */
define('EKOMI_REVIEWS_PER_PAGE', 5);

/** Disable or enable HTML on comments. */
define('EKOMI_HTML_ON_COMMENTS', 0); //0 - disable, 1 - enable

/** Allow  votes on reviews (review helpfulness) */
define('EKOMI_VOTES_ON_REVIEWS', 1); //0 - disable, 1 - enable

/** Link to ekomi; certificate => certificate page, 'nothing' => no link, 'website' => an ekomi url*/
define('EKOMI_URL', 'http://www.ekomi.de/');

/** Custom styles */
define('EKOMI_AUTOLOAD_ON_SCROLL', 0); //0 - disable, 1 - enable; SCROLLBARS must be enabled