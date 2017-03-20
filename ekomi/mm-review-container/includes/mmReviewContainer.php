<?php

/**
 * Mass Market Review Container main class file.
 *
 * @since 2.0.0
 */
class mmReviewContainer
{

    /**
     * Class default params.
     *
     * @var array
     * @since 2.1
     */
    private $defaultParams = array(
        'returnInsteadEcho' => false,
        'productID' => null,
        'productName' => '',
        'hasReviewedItem' => true,
        'scriptName' => 'PRC-standalone',
        'scriptVersion' => '',
        'isStandalone' => false,
        'needsFullHtmlHeader' => false,
        'shopID' => null,
        'languageLocale' => 'en'
    );

    /**
     * Class params.
     *
     * @var array
     * @since 2.0
     */
    public $params;

    /**
     * Template engine (Twig).
     *
     * @var object
     * @since 2.0
     */
    private $twig;

    /**
     * eKomiDB class.
     *
     * @var object
     * @access public
     * @since 2.0
     */
    private $eKomiDB = null;

    /**
     * Minimum PHP version.
     *
     * @var object
     * @since 2.0
     */
    private $minimumPhpVersion = '5.2.4';

    /**
     * Script version.
     *
     * Used when calling the putPing etc.
     *
     * @var string
     * @since 2.0
     */
    private $scriptVersion = '2.1.3';

    /**
     * Class constructor
     *
     * @param array $params array('returnInsteadEcho' => bol,  'productID' => string, 'productName' => string)
     * @since 2.0
     */
    public function __construct($params = array())
    {
        // Update params
        $this->defaultParams['scriptVersion'] = $this->scriptVersion;

        // Parse the args into some array
        $this->params = Utils::parse_args($params, $this->defaultParams);

        // Check the PHP version; we need to have 5.2.4 for using the twig template
        if ((!function_exists('version_compare') || version_compare($this->minimumPhpVersion, PHP_VERSION, '>'))) {
            $warning = "Your host needs to use at least PHP 5.2.4 or higher to run this version of the script! Please note that php 5.2.4 is also obsolete (released on 30 Aug 2007). It was declared end-of-life nearly half a decade ago, and hasn't had any security patches since then. You are exposing yourself to a lot of risk, as pretty much everyone dropped support for php 5.2 some time ago, so you'll be stuck on old versions, again probably with nasty security holes. We strongly recommend that you try to upgrade if possible";
            die($warning);
        }

        // Load utility class
        $Utils = new Utils();

        // Load configuration file (redirect to install on fail)
        $this->loadConfig();

        // Load translations environement
        $this->loadTranslations();

        // Prepare database functionality eKomiDB
        $this->eKomiDB = new eKomiDB($this->params);

        // Is this ajax request?
        if ($Utils->isAjax()) {
            $this->handleAjaxRequests();
        }

        // Load Twig template engine
        $this->loadTwig();
    }

    /**
     * Loads Twig template engine
     *
     * @since 2.0
     */
    public function loadTwig()
    {
        require_once(eKOMI_APP_ABSPATH . 'includes/lib/Twig/Autoloader.php');
        Twig_Autoloader::register();

        $loader = new Twig_Loader_Filesystem(eKOMI_APP_ABSPATH . 'templates');
        $this->twig = new Twig_Environment(
            $loader, array(
                'debug' => true,
            )
        );

        // Add support for calling getext funtions inside Twig templates
        // This allows us to use one translation framework withing the whole application.
        $this->twig->addExtension(
            new simpleGettext(
                array(
                    'date',
                    '___',
                    '_e',
                    'esc_attr__',
                    'esc_html__',
                    'esc_attr_e',
                    'esc_html_e',
                    '_x',
                    '_ex',
                    'esc_attr_x',
                    'esc_html_x',
                    '_n',
                    '_nx',
                    '_n_noop',
                    '_nx_noop',
                    'sprintf',
                    array(
                        $this,
                        'getAppURI'
                    ),
                    array(
                        'Utils',
                        'get_iso_time'
                    ),
                )
            )
        );

        // Add twig debug
        $this->twig->addExtension(new Twig_Extension_Debug());

    }

    /**
     * Render Twig templates.
     *
     * @param string $html_file HTML file name.
     * @param array $variables Passed args to template
     *
     * @return mixed
     */
    public function loadTemplate($html_file, $variables = array())
    {
        $html = $this->twig->render($html_file, $variables);
        if ($this->params['returnInsteadEcho'] === true) {
            return $html;
        } else {
            echo $html;
        }

        die();
    }

    /**
     * Loads translations environement
     *
     * @since 2.0
     */
    public function loadTranslations()
    {
        // Setup a locale
        $_REQUEST['languageLocale'] = isset($_REQUEST['languageLocale']) ? $_REQUEST['languageLocale'] : $this->params['languageLocale'];

        // Require the translation library
        require_once(eKOMI_APP_ABSPATH . 'includes/lib/I10n/l10n.php');

        // Setup a text domain
        load_default_textdomain('ekomi', eKOMI_APP_ABSPATH . 'languages');
    }

    /**
     * Get all available languages based on the presence of *.mo files in a given directory.
     * The default directory is 'languages'.
     * English is the default language
     *
     * @param null $dir
     * @return array An array of language codes or an empty array if no languages are present. Language codes are formed by stripping the .mo extension from the language file names.
     *
     * @since 2.0
     */
    public function getAvailableLanguages($dir = null)
    {
        // The languages model
        $languageModel = new eKomiLanguage();
        $ekLanguages = $languageModel->getEkomiLanguages();

        // Languages array; add English by default
        $languages = array($ekLanguages['en']);

        // Parse language directory
        $languageDir = is_null($dir) ? eKOMI_APP_ABSPATH . 'languages' : $dir;

        // MO files in the languages directory
        $moFiles = (array)glob($languageDir . DIRECTORY_SEPARATOR . '*.mo');

        // Loop existing language translations
        foreach ($moFiles as $moFile) {
            $moFile = basename($moFile, '.mo');
            $languages[] = $ekLanguages[$moFile];
        }

        return $languages;
    }

    /**
     * Returns the app URI from REQUEST_URI
     *
     * @since 2.0
     */
    public function getAppURI()
    {

        // Check if the EKOMI_APP_URI is defined in the config
        if (defined('EKOMI_APP_URI')) {
            return EKOMI_APP_URI;
        }

        // This is called when the config.php doesn't exist yet.
        $pathinfo = pathinfo($_SERVER['PHP_SELF']);

        return array_key_exists('REQUEST_URI', $_SERVER) ? $pathinfo['dirname'] . '/' : '';
    }

    /**
     * Loads the configuration file or redirects to the install page.
     *
     * @since 2.0
     */
    public function loadConfig()
    {
        // If the configuration is not good, then die
        if (!$this->isReady()) {
            error_log('Review container is not configured: ' . eKOMI_APP_ABSPATH . 'config.php is not readable!');
            $this->getInstallPage();
        }
    }

    /**
     * Helper function that checks if the solution is configured and DB is accessible.
     *
     * @since 2.0
     *
     * @return boolean true if configured.
     */
    public function isReady()
    {
        // If config exist and Database accessible
        if (is_readable(eKOMI_APP_ABSPATH . 'config.php')) {
            require_once(eKOMI_APP_ABSPATH . 'config.php');

            // Init the database
            $this->eKomiDB = new eKomiDB($this->params);

            // Check database is ready
            if (!$this->eKomiDB->validateDatabaseConnection(null, false, true)) {

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Displays eKomi reviews.
     *
     * @since 2.0
     */
    public function getProductReviews()
    {
        // Do cron job if anay
        $this->checkCronJobStatus();

        // Parse the args into some array
        $args = $this->params;

        // Which shop?
        $args['shopID'] = $args['shopID'] ? $args['shopID'] : EKOMI_INTERFACE_ID;

        // Query the reviews
        $reviews_data = $this->eKomiDB->queryReviews($args['productID'], $args['shopID']);

        $pages = (EKOMI_REVIEWS_PER_PAGE == '-1') ? 1 : $reviews_data['query_total_reviews'] / EKOMI_REVIEWS_PER_PAGE;

        // If no product reviews found, return error
        if (isset($reviews_data['query_total_reviews']) && $reviews_data['query_total_reviews'] < 1 && EKOMI_NO_REVIEWS_FOUND_ACTION != 'show_empty_container') {

            // Is product id an array?
            $args['productID'] = is_array($args['productID']) ? json_encode($args['productID']) : $args['productID'];

            // Parse the no products found message
            $noProductsFoundMessage = str_replace('##product_name##', '<strong>' . $args['productName'] . '</strong>', EKOMI_NO_REVIEWS_FOUND_MESSAGE);
            $noProductsFoundMessage = str_replace('##product_id##', '<strong>' . $args['productID'] . '</strong>', $noProductsFoundMessage);

            // Switch the return type
            switch (EKOMI_NO_REVIEWS_FOUND_ACTION) {
                case 'show_message':
                    $html = $this->loadTemplate(
                        'partials/no_product_reviews.html.twig',
                        array(
                            'productID' => is_array($args['productID']) ? $args['productID'][0] : $args['productID'],
                            'productName' => $args['productName'],
                            'message' => $noProductsFoundMessage
                        )
                    );
                    break;
                default:
                    $html = '<p style="display: none !important;">' . sprintf(___("eKomi mass market review container log: %s"), $noProductsFoundMessage) . '</p>';
            }

            return $html;
        }

        // Load full reviews page
        return $this->loadTemplate(
            'widgets/reviews_large.html.twig',
            array(
                'page_title' => ___('eKomi › Reviews'),
                'reviews_data' => $reviews_data,
                'productID' => is_array($args['productID']) ? implode(',', $args['productID']) : $args['productID'],
                'pages' => $pages,
                'args' => $args,
                'ekomi_path' => EKOMI_APP_URI,
                'ekomiURL' => $this->getEkomiLogoLink(),
                'ekomi_count_pages' => ceil($reviews_data['query_total_reviews'] / EKOMI_REVIEWS_PER_PAGE),
                'ekomi_review_count' => $reviews_data['query_total_reviews'] > EKOMI_REVIEWS_PER_PAGE ? EKOMI_REVIEWS_PER_PAGE : $reviews_data['query_total_reviews'],
                'EKOMI_AUTOLOAD_ON_SCROLL' => EKOMI_AUTOLOAD_ON_SCROLL,
                'EKOMI_VOTES_ON_REVIEWS' => defined('EKOMI_VOTES_ON_REVIEWS') ? EKOMI_VOTES_ON_REVIEWS : false,
                'EKOMI_REVIEWS_PER_PAGE' => EKOMI_REVIEWS_PER_PAGE,
                'productName' => $args['productName'],
            )
        );
    }

    /**
     * Get product reviews.
     *
     * @return int
     * @since 2.0
     */
    public function getProductTotalReviews()
    {
        // Do cron job if anay
        $this->checkCronJobStatus();

        return $this->eKomiDB->getTotalReviewsCount($this->params['productID'], $this->params['shopID']);
    }

    /**
     * Get product reviews average.
     *
     * @return float
     */
    public function getProductStarsAvg()
    {
        // Do cron job if anay
        $this->checkCronJobStatus();

        return $this->eKomiDB->getProductAverageRating($this->params['productID'], $this->params['shopID']);
    }

    /**
     * Renders the ministars view
     *
     * @since 2.0
     */
    public function getMiniStarsCounter()
    {
        // Parse the args into some array
        $args = $this->params;

        // Which shop?
        $args['shopID'] = $args['shopID'] ? $args['shopID'] : EKOMI_INTERFACE_ID;

        // Do cron job if anay
        $this->checkCronJobStatus();

        // Return the widget
        return $this->loadTemplate(
            'widgets/mini_stars_counter.html.twig',
            array(
                'args' => $args,
                'page_title' => ___('eKomi › Reviews'),
                'product_total_reviews' => self::getProductTotalReviews(),
                'product_total_reviews_average' => $this->eKomiDB->getProductAverageRating($this->params['productID'], $this->params['shopID']),
            )
        );
    }

    /**
     * Renders the ministars view with average
     */
    public function getMiniStarsCounterAvg()
    {
        // Parse the args into some array
        $args = $this->params;

        // Which shop?
        $args['shopID'] = $args['shopID'] ? $args['shopID'] : EKOMI_INTERFACE_ID;

        // Do cron job if anay
        $this->checkCronJobStatus();

        // Return the widget
        return $this->loadTemplate(
            'widgets/mini_stars_counter.html.twig',
            array(
                'args' => $args,
                'showAverage' => true,
                'page_title' => ___('eKomi › Reviews'),
                'product_total_reviews' => self::getProductTotalReviews(),
                'product_total_reviews_average' => $this->eKomiDB->getProductAverageRating($this->params['productID'], $this->params['shopID']),
            )
        );
    }

    /**
     * Handles Ajax requests submited from the app.js
     *
     * @since 2.0
     */
    public function handleAjaxRequests()
    {
        if (isset($_POST['action']) && !empty($_POST['action'])) {

            // Load translations environement
            $this->loadTranslations();

            // Prepare database functionality for the install pages only
            $params = array(
                'ekomiInterfaceID' => isset($_POST['ekomi_interface_id']) ? $_POST['ekomi_interface_id'] : 0,
                'ekomiInterfacePassword' => isset($_POST['ekomi_interface_password']) ? $_POST['ekomi_interface_password'] : 0
            );

            // Merge the array of parameters
            $this->params = Utils::parse_args($params, $this->params);

            //Checks if action value exists
            $action = $_POST["action"];
            switch ($action) {
                case "validate_db_connection":
                    $this->eKomiDB = new eKomiDB($this->params);
                    $this->eKomiDB->validateDatabaseConnection($_POST["db_settings"]);
                    break;
                case "create_db":
                    $this->eKomiDB = new eKomiDB($this->params);
                    $this->eKomiDB->createDatabase($_POST["db_settings"]);
                    break;
                case "populate_db":
                    $this->eKomiDB = new eKomiDB($this->params);
                    $this->eKomiDB->populateDatabase();
                    break;
                case "populate_db_status":
                    $this->eKomiDB = new eKomiDB($this->params);
                    $this->eKomiDB->populateDatabaseStatus();
                    break;
                case "saveFeedback":
                    $this->saveFeedback();
                    break;
                case "loadReviews":
                    $this->loadReviews();
                    break;
                case "delete_config_php":
                    $this->deleteConfigPhp();
                    break;
            }
        } else {
            echo json_encode(
                array(
                    'state' => 'error',
                    'message' => 'No ajax todo action is specified!',
                    '_POST' => $_POST
                )
            );
            die();
        }
    }

    /**
     * Saves the helpfulness of a feedback and returns json response.
     *
     * @since 2.0
     */
    public function saveFeedback()
    {

        // Check if ekomi_interface_id is set $review_id, $column
        $review_id = isset($_POST['review_id']) ? $_POST['review_id'] : null;
        $helpfulness = isset($_POST['helpfulness']) ? $_POST['helpfulness'] : null;

        // Check submited data
        if (!$review_id || is_null($helpfulness)) {

            echo json_encode(
                array(
                    'state' => 'error',
                    'message' => 'Please provide the review parameters',
                    '_POST' => $_POST,
                    'helpfulness' => $helpfulness . ' ' . gettype($helpfulness),
                )
            );
        } else {
            $rate_helpfulness = $this->eKomiDB->rate_single_review_helpfulness($review_id, $helpfulness);

            if ($rate_helpfulness['query']) {
                echo json_encode(
                    array(
                        'state' => 'success',
                        'message' => 'Helpfulness successfully recorder!',
                        '_POST' => $_POST,
                        'rate_helpfulness' => $rate_helpfulness,
                    )
                );
            } else {

                // Return
                echo json_encode(
                    array(
                        'state' => 'error',
                        'message' => 'Could not process the request! ' . $rate_helpfulness['last_error'],
                        '_POST' => $_POST,
                        'rate_helpfulness' => $rate_helpfulness,
                    )
                );
            }
        }

        // Return a proper json answer
        die();
    }

    /**
     * Returns the reviews to Ajax requests.
     *
     * @since 2.0
     */
    public function loadReviews()
    {
        // Check if ekomi_interface_id is set $review_id, $column
        $shopID = isset($_POST['ekomi_shop_id']) ? $_POST['ekomi_shop_id'] : null;
        $productID = isset($_POST['product_id']) ? $_POST['product_id'] : null;
        $sort_type = isset($_POST['sort_type']) ? $_POST['sort_type'] : '1';
        $offset_page = isset($_POST['offset_page']) ? $_POST['offset_page'] : 0;
        $filter_type = isset($_POST['filter_type']) ? $_POST['filter_type'] : 0;

        // If this is an array of products
        $productID = strpos($productID, ',') !== false ? explode(',', $productID) : $productID;

        // Check submited data
        if (is_null($productID)) {

            echo json_encode(
                array(
                    'state' => 'error',
                    'message' => 'Please provide the review parameters',
                    '_POST' => $_POST,
                )
            );
            die();
        }

        // Query the reviews
        $reviews_data = $this->eKomiDB->queryReviews($productID, $shopID, $offset_page, $sort_type, $filter_type);

        // return
        echo json_encode(
            array(
                'state' => 'success',
                'message' => 'reviews loaded!',
                'reviews_data' => $reviews_data,
                '_POST' => $_POST,
            )
        );
        die();
    }

    /**
     * Get a list of shop IDs
     *
     * @return array
     */
    public function getShopsIDs()
    {
        // Shortcuts
        $db = $this->eKomiDB;

        // Get DB snapshot
        $shops = $db->get_option('ekomi_shops');

        return $shops;
    }

    /**
     * Render the install page
     *
     * @since 2.0
     */
    public function getInstallPage()
    {

        // Load utility classes
        $Utils = new Utils();

        // Load translations environement
        $this->loadTranslations();

        // Is this ajax request?
        if ($Utils->isAjax()) {
            $this->handleAjaxRequests();
        }

        // Prepare database functionality eKomiDB
        $this->eKomiDB = new eKomiDB();

        // Load Twig template engine
        $this->loadTwig();

        // Prepare form defaults
        $form_defaults = array(
            'db_host' => defined('DB_HOST') ? DB_HOST : 'localhost',
            'db_name' => defined('DB_NAME') ? DB_NAME : 'mm-review-container',
            'db_user' => defined('DB_USER') ? DB_USER : 'root',
            'db_password' => defined('DB_PASSWORD') ? DB_PASSWORD : 'mysql',
            'ekomi_interface_id' => defined('EKOMI_INTERFACE_ID') ? EKOMI_INTERFACE_ID : '639170', // Wrong one :)
            'ekomi_interface_password' => defined(
                'EKOMI_INTERFACE_PASSWORD'
            ) ? EKOMI_INTERFACE_PASSWORD : '00786cc0099662077e3e22ff30', // Wrong one :)
            'ekomi_app_locale' => defined('EKOMI_APP_LOCALE') ? EKOMI_APP_LOCALE : 'en',
            'ekomi_reviews_per_page' => defined('EKOMI_REVIEWS_PER_PAGE') ? EKOMI_REVIEWS_PER_PAGE : '5',
            'ekomi_app_uri' => $this->getAppURI(),
            'ekomi_cron_job_delay' => 24,
            'available_languages' => $this->getAvailableLanguages(),
            'default_language' => isset($_REQUEST['languageLocale']) ? $_REQUEST['languageLocale'] : 'en'
        );

        // Load install page
        $this->loadTemplate(
            'install.html.twig',
            array(
                'ek_debug' => isset($_GET['ek_debug']),
                'page_title' => ___('eKomi › Setup'),
                'needs_foundation' => true,
                'args' => $this->params,
                'form_defaults' => $form_defaults,
                'config_file_exist' => is_readable(eKOMI_APP_ABSPATH . 'config.php')
            )
        );

        // Die if in Standalone
        if ($this->params['isStandalone']) {
            die();
        }
    }

    /**
     * Delete configuration file.
     *
     * @since 2.0
     */
    public function deleteConfigPhp()
    {

        if (is_readable(eKOMI_APP_ABSPATH . 'config.php')) {

            $file_deleted = unlink(eKOMI_APP_ABSPATH . 'config.php');

            if ($file_deleted) {

                echo json_encode(
                    array(
                        'state' => 'success',
                        'message' => 'File: "config.php" is deleted!',
                        '_POST' => $_POST
                    )
                );
            } else {
                echo json_encode(
                    array(
                        'state' => 'error',
                        'message' => 'Could not delete "config.php", please delete it manually!',
                        '_POST' => $_POST
                    )
                );
            }
        } else {
            echo json_encode(
                array(
                    'state' => 'error',
                    'message' => 'File: "config.php" not found!',
                    '_POST' => $_POST
                )
            );
        }
        die();
    }

    /**
     * Checks/launches the cron job and its status.
     *
     * @return array|void
     */
    public function checkCronJobStatus()
    {
        // Do nothing if configuration is not ready
        if (!$this->isReady()) {
            return false;
        }

        // Get cron job settings
        $ekomi_cron_job_delay = $this->eKomiDB->get_option('ekomi_cron_job_delay');
        $ekomi_last_cron_job_time = $this->eKomiDB->get_option('ekomi_last_cron_job_time');

        // If cron job never set, then set a new one
        if (!$ekomi_cron_job_delay || !$ekomi_last_cron_job_time) {
            return $this->eKomiDB->doCronJob();
        }

        // if the last cron job is old, then just ignore
        $now = time();
        $next_cron_job = (int)$ekomi_last_cron_job_time + ((int)$ekomi_cron_job_delay * 60 * 60);
        if ($now > $next_cron_job) {
            return $this->eKomiDB->doCronJob();
        }

        // if none of above, then we are okay :)
        return array(
            'ekomi_cron_job_delay' => (int)$ekomi_cron_job_delay,
            'ekomi_last_cron_job_time' => (int)$ekomi_last_cron_job_time,
            'ekomi_next_cron_job_time' => (int)$next_cron_job,
            'ekomi_time_now' => (int)$now
        );
    }

    /**
     * Gets the logo link
     *
     * @return string|void
     */
    public function getEkomiLogoLink()
    {
        // Do nothing if configuration is not ready
        if (!$this->isReady()) {
            return false;
        }

        // Default link
        $link = '#';

        // Check the config and return appropriate link
        switch (EKOMI_URL) {
            case 'certificate':
                // get shop settings
                $shop = eKomiAPI::apiGetSettings(
                    EKOMI_INTERFACE_ID,
                    EKOMI_INTERFACE_PASSWORD
                );
                $link = $shop['ekomi_certificate'];
                break;
            case 'website':
                // get ekomi url for that language
                $language = new eKomiLanguage();
                $language->setLanguage(EKOMI_APP_LOCALE);
                $languageAtts = $language->getLanguageAtts();
                $link = $languageAtts['website'];
                break;
            case 'nothing':
                $link = '';
                break;
        }

        return $link;
    }

    /**
     * Debug the app
     *
     * @return string|void
     */
    public function debug()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $db = $this->eKomiDB->initDB();

        $sql = "SELECT * FROM  63917_ekomi_product_reviews";

        $reviews = $db->get_results($sql, ARRAY_A);

        if ($db->last_error) {
            Utils::jsonDebugDie(
                'error',
                $db->last_error,
                array(
                    'in' => 'query_reviews',
                    'sSQL' => $sql
                )
            );
        }

        echo json_encode($reviews);
        die();
    }
}
