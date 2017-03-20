<?php

/**
 *
 */
class eKomiDB
{

    /**
     * ezSQL_mysql Database object.
     *
     * @since 2.0.0
     * @var object
     * @access public
     */
    private $db;


    private $params;
    private $table_reviews;
    private $table_suffix_reviews;
    private $table_options;
    private $table_suffix_options;

    /**
     * Database Handle
     *
     * @since 2.0.0
     * @access protected
     * @var string
     */
    protected $dbh;

    public function __construct($params = array())
    {
        // Load translations for the install page or other places where there are no translations
        require_once(eKOMI_APP_ABSPATH . 'includes/lib/I10n/l10n.php');
        load_default_textdomain('ekomi', eKOMI_APP_ABSPATH . 'languages');

        // Update params
        $defaults = array(
            'ekomiInterfaceID' => defined('EKOMI_INTERFACE_ID') ? EKOMI_INTERFACE_ID : null,
            'ekomiInterfacePassword' => defined('EKOMI_INTERFACE_PASSWORD') ? EKOMI_INTERFACE_PASSWORD : null,
            // Tables names
            'table_suffix_reviews' => '_ekomi_product_reviews',
            'table_suffix_options' => '_ekomi_product_reviews_options'
        );

        // Parse the args into some array
        $this->params = Utils::parse_args($params, $defaults);

        // Update table names
        $this->table_suffix_reviews = $this->params['table_suffix_reviews'];
        $this->table_reviews = $this->params['ekomiInterfaceID'] . $this->table_suffix_reviews;
        $this->table_suffix_options = $this->params['table_suffix_options'];
        $this->table_options = $this->params['ekomiInterfaceID'] . $this->table_suffix_options;

        // Include ezSQL core
        require_once(eKOMI_APP_ABSPATH . 'includes/lib/ezSQL/shared/ez_sql_core.php');

        // Include ezSQL database specific component (in this case mySQL)
        require_once(eKOMI_APP_ABSPATH . 'includes/lib/ezSQL/mysql/ez_sql_mysql.php');
    }

    /**
     * Get parameters from this class
     *
     * @since 2.0
     * @param string $param parameter to return.
     *
     * @return mixed
     */
    public function getParam($param)
    {
        return isset($this->params[$param]) ? $this->params[$param] : null;
    }

    /**
     * Initialize a database connection.
     *
     * @since 2.0
     */
    public function initDB()
    {

        // Initialize database if not already initialized
        if (!($this->db instanceof ezSQL_mysql)) {

            if (!defined('DB_NAME') || !defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASSWORD')) {
                echo json_encode(
                    array(
                        'state' => 'error',
                        'message' => ___('Missing database configuration!'),
                        'params' => $this->params
                    )
                );

                die();

            }
            $this->db = new ezSQL_mysql(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        }

        // Disable errors
        //$this->db->show_errors = false;

        // Set this db
        return $this->db;
    }

    /**
     * Checks if database connection exist.
     *
     * @param array $db_settings database connection settings.
     * @param bool $return_json Return type.
     * @param bool $checkTables Verify tables if they exist.
     *
     * @return mixed
     *
     * @since 2.0
     */
    public function validateDatabaseConnection(
        $db_settings = array(),
        $return_json = true,
        $checkTables = false
    )
    {
        // Prepare return
        $json_encode = null;
        $status = null;

        // Test the db connection.
        if (!empty($db_settings)) {
            if (!defined('DB_HOST')) {
                define('DB_HOST', $db_settings['db_host']);
            }
            if (!defined('DB_USER')) {
                define('DB_USER', $db_settings['db_user']);
            }
            if (!defined('DB_PASSWORD')) {
                define('DB_PASSWORD', $db_settings['db_password']);
            }
            if (!defined('DB_NAME')) {
                define('DB_NAME', $db_settings['db_name']);
            }
        }

        $this->dbh = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, true, 131074);
        if (!$this->dbh) {

            $json_encode = json_encode(
                array(
                    'state' => 'error',
                    'message' => sprintf(___('Error establishing a database connection. This either means that the username and password information are incorrect or we can\'t contact the database server at %s'), '<i>' . DB_HOST . '</i>'),
                    '_POST' => $_POST,
                    'mysql_connect' => array(
                        DB_HOST,
                        DB_USER,
                        DB_PASSWORD,
                        true,
                        131074
                    ),
                    'dbh' => json_encode($this->dbh),
                )
            );
            $status = false;
        } else {

            // Initialize database
            $this->initDB();

            $this->db->dbh = $this->dbh;
            $this->db->show_errors = false;

            // Chek if db exists
            $db_exists = $this->db->select(DB_NAME, $this->dbh);

            if (!$db_exists) {

                // Ann error occured; db doens't exist?
                $json_encode = json_encode(
                    array(
                        'state' => 'error',
                        'message' => sprintf(___('Unknown database %s Please create the %s database first!'), '<i>' . DB_NAME . '</i>', '<i>' . DB_NAME . '</i>'),
                        'last_error' => $this->db->last_error,
                        'db_exists' => $db_exists,
                        '_POST' => $_POST
                    )
                );
                $status = false;
            } else {
                if ($checkTables) {
                    // Check if necessary tables exist
                    $tableReviewsExist = $this->db->query('SELECT 1 FROM `' . $this->table_reviews . '` LIMIT 1');
                    $tableOptionsExist = $this->db->query('SELECT 1 FROM `' . $this->table_options . '` LIMIT 1');
                    if (!$tableReviewsExist && !$tableOptionsExist) {
                        // Ann error occured; db doens't exist?
                        $json_encode = json_encode(
                            array(
                                'state' => 'error',
                                'message' => sprintf(___('Table %s does not exist.'), '<i>' . $this->table_reviews . '</i>'),
                                'last_error' => $this->db->last_error,
                                'db_exists' => $db_exists,
                                '_POST' => $_POST
                            )
                        );
                        $status = false;
                    } else {
                        // Success
                        $json_encode = json_encode(
                            array(
                                'state' => 'success',
                                'message' => sprintf(___('eKomi can now communicate with your %s local database. To populate your local database with product reviews, please click on "Populate & Sync Database"'), '<i>' . DB_NAME . '</i>'),
                                '_POST' => $_POST
                            )
                        );
                        $status = true;
                    }
                } else {
                    // Success
                    $json_encode = json_encode(
                        array(
                            'state' => 'success',
                            'message' => sprintf(___('eKomi can now communicate with your %s local database. To populate your local database with product reviews, please click on "Populate & Sync Database"'), '<i>' . DB_NAME . '</i>'),
                            '_POST' => $_POST
                        )
                    );
                    $status = true;
                }
            }
        }

        // Success
        if ($return_json) {
            echo $json_encode;

            die();
        } else {
            return $status;
        }
    }

    /**
     * @param array $db_settings
     */
    public function createDatabase($db_settings = array())
    {

        // Test the db connection.
        if (!empty($db_settings)) {
            if (!defined('DB_HOST')) {
                define('DB_HOST', $db_settings['db_host']);
            }
            if (!defined('DB_USER')) {
                define('DB_USER', $db_settings['db_user']);
            }
            if (!defined('DB_PASSWORD')) {
                define('DB_PASSWORD', $db_settings['db_password']);
            }
            if (!defined('DB_NAME')) {
                define('DB_NAME', $db_settings['db_name']);
            }
        }

        $this->dbh = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, true, 131074);
        if (!$this->dbh) {

            echo json_encode(
                array(
                    'state' => 'error',
                    'message' => sprintf(___('Error establishing a database connection. This either means that the username and password information are incorrect or we can\'t contact the database server at %s'), '<i>' . DB_HOST . '</i>'),
                    '_POST' => $_POST,
                    'mysql_connect' => array(
                        DB_HOST,
                        DB_USER,
                        DB_PASSWORD,
                        true,
                        131074
                    ),
                    'dbh' => json_encode($this->dbh),
                )
            );
        } else {

            // Initialize database
            $this->initDB();
            $this->db->dbh = $this->dbh;
            $this->db->show_errors = false;

            // Chek if db exists
            $db_exists = $this->db->select(DB_NAME, $this->dbh);

            if (!$db_exists) {
                $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);

                // Prepare query
                $sql = 'CREATE DATABASE ' . DB_NAME;
                $sql_query = $mysqli->query($sql);

                if ($mysqli->connect_error || !$sql_query === true) {

                    // return
                    echo json_encode(
                        array(
                            'state' => 'error',
                            'message' => sprintf(___('Could not create the database %s'), '<i>' . DB_NAME . '</i>') . '!<br>' . $mysqli->error,
                            'sql_query' => $sql_query,
                            'sql' => $sql,
                            '_POST' => $_POST
                        )
                    );
                } else {

                    // Success
                    echo json_encode(
                        array(
                            'state' => 'success',
                            'message' => sprintf(___('Database %s created successfully.'), '<i>' . DB_NAME . '</i>'),
                            '_POST' => $_POST
                        )
                    );
                }
            } else {

                // Success
                echo json_encode(
                    array(
                        'state' => 'success',
                        'message' => sprintf(___('Database %s already exist.'), '<i>' . DB_NAME . '</i>'),
                        '_POST' => $_POST
                    )
                );
            }
        }

        die();
    }

    /**
     * Checks the current database populate status.
     * Works only from Ajax calls
     *
     * @since 2.0
     */
    public function populateDatabaseStatus()
    {

        /*// Check if ekomi_interface_id is set
        $ekomiInterfaceID = isset($_POST['ekomi_interface_id']) ? $_POST['ekomi_interface_id'] : false;
        $ekomiInterfacePassword = isset($_POST['ekomi_interface_password']) ? $_POST['ekomi_interface_password'] : false;

        // Update local vars
        $this->params['ekomiInterfaceID'] = $ekomiInterfaceID;
        $this->params['ekomiInterfacePassword'] = $ekomiInterfacePassword;

        // Check if the db connection
        $isValidDB = $this->validateDatabaseConnection($_POST["db_settings"], false, true);

        // Get total feedbacks of the shop as the snapshot returns only last year's!
        $totalReviews = self::get_option('ekomi_total_reviews_last_check');

        // Die: if no database connection or interface ID not provided.
        if (!$isValidDB || !$ekomiInterfaceID) {
            Utils::jsonDebugDie(
                'error',
                ___('No database connection or interface ID not provided.'),
                array(
                    'is_valid_db' => $isValidDB,
                    'ekomiInterfaceID' => $ekomiInterfaceID,
                    'reviewsTotal' => $totalReviews,
                    'reviewsCopied' => self::countLocalReviewsTotal(),
                    '_POST' => $_POST
                )
            );
        }*/

        // Check if the db connection
        $isValidDB = $this->validateDatabaseConnection($_POST["db_settings"], false, true);
        if ($isValidDB ) {
            // Save the status of the sync in the session to enhance UX
            $installProcess = self::get_option('installProcess');

            // Get the install session
            if (is_array($installProcess) && array_key_exists('installPercentage', $installProcess) && array_key_exists('installMessage', $installProcess)) {
                // Return status
                echo json_encode(
                    array(
                        'state' => 'success',
                        'message' => ___('Populating database status...'),
                        'installPercentage' => $installProcess['installPercentage'],
                        'installMessage' => $installProcess['installMessage']
                    )
                );
                die();
            }
        }


        // Return error
        echo json_encode(
            array(

                'state' => 'error',
                'message' => ___('Populating database status...'),
                'installPercentage' => 0,
                'installMessage' => ''
            )
        );
        die();
    }

    /**
     * Populate the database data.
     *
     * @since 2.0
     */
    public function populateDatabase()
    {
        // Check if ekomi_interface_id is set
        $this->params['ekomiInterfaceID'] = isset($_POST['ekomi_interface_id']) ? $_POST['ekomi_interface_id'] : false;
        $this->params['ekomiInterfacePassword'] = isset($_POST['ekomi_interface_password']) ? $_POST['ekomi_interface_password'] : false;

        // Check if we could authenticate to the API
        $shopExists = eKomiAPI::apiGetSettings(
            $this->params['ekomiInterfaceID'],
            $this->params['ekomiInterfacePassword']
        );

        // Die if the auth is wrong
        if (!$shopExists) {
            Utils::jsonDebugDie(
                'error',
                sprintf(___('The credentials you supplied for interface ID %s were not correct or did not grant access to the eKomi API.'), $this->params['ekomiInterfaceID']),
                array(
                    'apiGetSettings' => $shopExists
                )
            );
        }

        // Loop the extra shops and see if there's a problem
        if (isset($_POST['ekomi_extra_shops'])) {
            foreach ($_POST['ekomi_extra_shops'] as $shop) {
                // Check if we could authenticate to the API
                $extraShopExistsResponse = eKomiAPI::apiGetSettings(
                    $shop['ekomi_interface_id'],
                    $shop['ekomi_interface_password']
                );

                // If shop doesn't exist
                if (!$extraShopExistsResponse) {
                    Utils::jsonDebugDie(
                        'error',
                        sprintf(___('The credentials you supplied for interface ID %s were not correct or did not grant access to the eKomi API.'), $shop['ekomi_interface_id']),
                        array(
                            'apiGetSnapshot' => $extraShopExistsResponse
                        )
                    );
                }
            }
        }

        // Check the db connection
        $isValidDB = $this->validateDatabaseConnection($_POST["db_settings"], false);

        // Die: if no database connection or interface ID not provided.
        if (!$isValidDB || !$this->params['ekomiInterfaceID']) {
            Utils::jsonDebugDie(
                'error',
                ___('No database connection or interface ID not provided.'),
                array(
                    'is_valid_db' => $isValidDB,
                    'ekomiInterfaceID' => $this->params['ekomiInterfaceID'],
                )
            );
        }

        // Create database tables
        $this->createDatabaseTables();

        // Update install status
        self::update_option('installProcess', array(
            'installPercentage' => 10,
            'installMessage' => ___('Creating database tables')
        ));
        sleep(2);

        // Update install status
        self::update_option('installProcess', array(
            'installPercentage' => 20,
            'installMessage' => ___('Updating snapshot for main shop and extra shops')
        ));
        sleep(2);

        // Update snapshot
        $this->updateSnapshot();

        // Update install status
        self::update_option('installProcess', array(
            'installPercentage' => 30,
            'installMessage' => ___('Add reviews returned by API to local database')
        ));
        sleep(2);

        // Add the result reviews to the local database
        $this->syncReviewsDatabase();

        // Update cron job timestamp
        self::update_option('ekomi_cron_job_delay', $_POST['ekomi_cron_job_delay']);

        // Update install status
        self::update_option('installProcess', array(
            'installPercentage' => 99,
            'installMessage' => ___('Creating the config file')
        ));
        sleep(2);

        // Create a "config.php" file
        $this->initConfigFile(true);
    }

    /**
     * Add reviews returned by API to local database.
     *
     * This a bit time consuming as it calls the API to get product name
     * and append it to the review in local database.
     *
     * @since 2.0
     */
    public function syncReviewsDatabase()
    {
        // TODO
        // Refractor this?
        set_time_limit(3600);

        // Collect the shop reviews
        $apiProductReviewsLastCheck = array();

        // Get the shops list in the database and sync them
        $ekomiShops = self::get_option('ekomi_shops');
        $shops = $ekomiShops ? $ekomiShops : array();

        // Calculate install status increase amount; last status was 30% and 90% starts the next task
        $lastIncreaseValue = 30;
        $statusIncreaseAmount = 60 / count($shops);

        foreach ($shops as $shop) {

            // Update install status
            $lastIncreaseValue += $statusIncreaseAmount;
            self::update_option('installProcess', array(
                'installPercentage' => $lastIncreaseValue,
                'installMessage' => ___('Syncing reviews for shop with interface ID:' . $shop['ekomi_interface_id'])
            ));

            // Check if the database has already existing reviews
            $table = $shop['ekomi_interface_id'] . $this->table_suffix_reviews;
            $reviewsExistOnDatabase = $this->db->get_var('SELECT COUNT(`id`) FROM `' . $table . '`');

            // If they exist, then we call the API with range parameter to avoid high memory usage on API
            $range = $reviewsExistOnDatabase ? $this->prepareApiRange(true, $shop['ekomi_interface_id']) : 'all';

            // Call API to get the product or shop reviews.
            $apiProductReviews = eKomiAPI::apiCall(
                'apiGetProductfeedback',
                array(
                    $shop['ekomi_interface_id'],
                    $shop['ekomi_interface_password'],
                    $range
                )
            );

            // Check for no reviews
            $apiProductReviews = is_array($apiProductReviews) ? $apiProductReviews : array();

            $apiProductReviewsLastCheck[$shop['ekomi_interface_id']] = count($apiProductReviews);

            // Get all the reviews in the local database
            $localReviews = $this->getAllLocalReviews($shop['ekomi_interface_id']);

            // Loop through the returned API reviews and insert them on database if they are not already there
            foreach ($apiProductReviews as $ApiReview) {
                // Force UTF-8 encoding
                $ApiReview = array_map(array('Encoding', 'toUTF8'), $ApiReview);

                // If the review is already on local database skip it.
                if (Utils::isReviewInDatabase($localReviews, $ApiReview)) {
                    continue;
                }

                //Add review to database
                $sSQL = 'INSERT INTO
                        `' . $table . '`
                        SET
							  `timestamp`      = \'' . $this->db->escape($ApiReview['submitted']) . '\',
							  `order_id`       = \'' . $this->db->escape($ApiReview['order_id']) . '\',
							  `product_id`     = \'' . $this->db->escape($ApiReview['product_id']) . '\',
							  `stars`          = \'' . $this->db->escape($ApiReview['rating']) . '\',
							  `review_comment_text`         = \'' . $this->db->escape($ApiReview['review']) . '\',
							  `helpful`        = \'0\',
							  `nothelpful`     = \'0\';';

                $insert_review = $this->db->query($sSQL);

                if ($this->db->last_error) {

                    Utils::jsonDebugDie(
                        'error',
                        'Add review to database failed!<br>' . $sSQL . '<br>Last error was: ' . $this->db->last_error,
                        array(
                            'insert_review' => $insert_review
                        )
                    );
                }
            }
        }

        // Update total products count
        self::update_option('ekomi_total_reviews_last_check', $apiProductReviewsLastCheck);

        // Update cron job timestamp
        self::update_option('ekomi_last_cron_job_time', time());
    }

    /**
     * Creates DB tables
     */
    public function createDatabaseTables()
    {
        // Collect settings
        $db_settings = $_POST["db_settings"];
        $forceDelete = $_POST['ekomi_force_delete'] == 'true' ? true : false;

        // Check if the db connection
        $isValidDB = $this->validateDatabaseConnection($db_settings, false);

        // Do we need to delete the existing tables?
        if ($forceDelete) {
            $sql = "DROP TABLE IF EXISTS `" . $this->table_reviews . "`";
            $dropTable = $this->db->query($sql);

            if ($this->db->last_error) {
                Utils::jsonDebugDie(
                    'error',
                    sprintf(___('Could not drop the reviews table! %s Last error was: %s'), '<i>' . $sql . '</i><br>', '<br>' . $this->db->last_error),
                    array(
                        'dropTable' => $dropTable,
                        'is_valid_db' => $isValidDB,
                        'sql' => $sql
                    )
                );
            }

            // Delete also extra shop
            if (array_key_exists('ekomi_extra_shops', $_POST)) {
                foreach ($_POST['ekomi_extra_shops'] as $shop) {
                    // Table name
                    $extraShopReviewsTable = $shop['ekomi_interface_id'] . $this->table_suffix_reviews;

                    $sql = "DROP TABLE IF EXISTS `" . $extraShopReviewsTable . "`";
                    $dropTable = $this->db->query($sql);

                    if ($this->db->last_error) {
                        Utils::jsonDebugDie(
                            'error',
                            sprintf(___('Could not drop the reviews table! %s Last error was: %s'), '<i>' . $sql . '</i><br>', '<br>' . $this->db->last_error),
                            array(
                                'dropTable' => $dropTable,
                                'is_valid_db' => $isValidDB,
                                'sql' => $sql
                            )
                        );
                    }
                }
            }

        }

        // To do
        // check with COLLATE utf8mb4_unicode_ci bug
        $sqlQueries = array(
            "CREATE TABLE IF NOT EXISTS `" . $this->table_reviews . "` (
				`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `timestamp` INT(11) UNSIGNED NOT NULL,
                `order_id` VARCHAR(64) NOT NULL,
                `product_id` VARCHAR(64) NOT NULL,
                `stars` INT(1) UNSIGNED NOT NULL,
                `review_comment_text` TEXT NOT NULL,
                `helpful` INT(11) UNSIGNED,
                `nothelpful` INT(11) UNSIGNED,
                PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci",
            "CREATE TABLE IF NOT EXISTS `" . $this->table_options . "` (
                  `option_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `option_name` VARCHAR(64) NOT NULL DEFAULT '',
                  `option_value` LONGTEXT NOT NULL,
                  `autoload` VARCHAR(20) NOT NULL DEFAULT 'yes',
                  PRIMARY KEY (`option_id`),
                  UNIQUE KEY `option_name` (`option_name`)
                ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci",
        );

        // Loop statements and create tables
        foreach ($sqlQueries as $sqlQuery) {
            $create_table = $this->db->query($sqlQuery);

            if ($this->db->last_error) {
                Utils::jsonDebugDie(
                    'error',
                    sprintf(___('Could not create the database tables! %s Last error was: %s'), '<i>' . $sqlQuery . '</i><br>', '<br>' . $this->db->last_error),
                    array(
                        'create_table' => $create_table,
                        'is_valid_db' => $isValidDB,
                        'sql' => $sqlQuery
                    )
                );
            }
        }

        // Create also extra shop
        $extraShops = isset($_POST['ekomi_extra_shops']) ? $_POST['ekomi_extra_shops'] : array();
        foreach ($extraShops as $shop) {
            // Table name
            $extraShopReviewsTable = $shop['ekomi_interface_id'] . $this->table_suffix_reviews;

            // The query
            $sql = "CREATE TABLE IF NOT EXISTS `" . $extraShopReviewsTable . "` (
				`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `timestamp` INT(11) UNSIGNED NOT NULL,
                `order_id` VARCHAR(64) NOT NULL,
                `product_id` VARCHAR(64) NOT NULL,
                `stars` INT(1) UNSIGNED NOT NULL,
                `review_comment_text` TEXT NOT NULL,
                `helpful` INT(11) UNSIGNED,
                `nothelpful` INT(11) UNSIGNED,
                PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci";

            $create_table = $this->db->query($sql);

            if ($this->db->last_error) {
                Utils::jsonDebugDie(
                    'error',
                    sprintf(___('Could not create the database tables! %s Last error was: %s'), '<i>' . $sql . '</i><br>', '<br>' . $this->db->last_error),
                    array(
                        'create_table' => $create_table,
                        'is_valid_db' => $isValidDB,
                        'sql' => $sql
                    )
                );
            }
        }

        // Update the shops list in the database
        $shops = $this->getPostedShops();
        self::update_option('ekomi_shops', $shops);
    }

    /**
     * Update snapshot for main shop and extra shops
     */
    public function updateSnapshot()
    {
        // Get the shops list in the database
        $shops = self::get_option('ekomi_shops');

        // Loop the shops and get snapshot results
        $snapshots = array();
        foreach ($shops as $shop) {
            // Call API to get a snapshot
            $snapshotResultsArray = eKomiAPI::apiGetSnapshot(
                $shop['ekomi_interface_id'],
                $shop['ekomi_interface_password']
            );

            if (!$snapshotResultsArray) {
                Utils::jsonDebugDie(
                    'error',
                    ___('Could not talk to the API using the current ID & password combination!')
                );
            }

            // Update the snapshot in options table for the main shop
            $snapshots[$shop['ekomi_interface_id']] = array(
                'fb_avg' => $snapshotResultsArray['info']['fb_avg'],
                'ekomi_certificate' => $snapshotResultsArray['info']['ekomi_certificate'],
                'fb_count' => $snapshotResultsArray['info']['fb_count']
            );
        }

        // Update snapshots
        self::update_option('ekomi_api_snapshot', $snapshots);
    }

    /**
     * Creates a config file
     *
     * @param bool|false $deleteIfExists Prevents returning error to the browser if the config.php already exists.
     * @since 2.0
     */
    public function initConfigFile($deleteIfExists = false)
    {
        // Path
        $path_to_config = 'config.php';

        // Check config file
        if (!file_exists($path_to_config)) {
            // Create a "config.php" file
            $this->buildConfigFile();
        } else {
            if ($deleteIfExists) {
                // Delete the existing one
                unlink($path_to_config);

                // Create a "config.php" file
                $this->buildConfigFile();

                // return success
                echo json_encode(
                    array(
                        'state' => 'success',
                        'message' => ___('Config file exists and not updated.'),
                        'path_to_config' => $path_to_config
                    )
                );
            }
            // return error
            echo json_encode(
                array(
                    'state' => 'error',
                    'message' => ___('Config file "config.php" already exists!'),
                )
            );
        }

        // return proper json result
        die();
    }

    /**
     * Builds the actual config.php file
     *
     * @since 2.0
     */
    public function buildConfigFile()
    {
        // Set the vars
        $db_settings = $_POST['db_settings'];
        if (!defined('EKOMI_APP_LOCALE')) {
            define('EKOMI_APP_LOCALE', $_POST['ekomi_app_locale']);
        }
        if (!defined('EKOMI_APP_URI')) {
            define('EKOMI_APP_URI', $_POST['ekomi_app_uri']);
        }
        if (!defined('EKOMI_INTERFACE_ID')) {
            define('EKOMI_INTERFACE_ID', $_POST['ekomi_interface_id']);
        }
        if (!defined('EKOMI_INTERFACE_PASSWORD')) {
            define('EKOMI_INTERFACE_PASSWORD', $_POST['ekomi_interface_password']);
        }

        if (!defined('EKOMI_NO_REVIEWS_FOUND_ACTION')) {
            define('EKOMI_NO_REVIEWS_FOUND_ACTION', $_POST['ekomi_no_reviews_found_action']);
        }
        if (!defined('EKOMI_NO_REVIEWS_FOUND_MESSAGE')) {
            define('EKOMI_NO_REVIEWS_FOUND_MESSAGE', $_POST['ekomi_no_reviews_found_message']);
        }

        if (!defined('EKOMI_CRON_JOB_DELAY')) {
            define('EKOMI_CRON_JOB_DELAY', $_POST['ekomi_cron_job_delay']);
        }

        if (!defined('EKOMI_REVIEWS_PER_PAGE')) {
            define('EKOMI_REVIEWS_PER_PAGE', $_POST['ekomi_reviews_per_page']);
        }
        if (!defined('EKOMI_HTML_ON_COMMENTS')) {
            define('EKOMI_HTML_ON_COMMENTS', ($_POST['ekomi_html_on_comments'] == 'true' ? 1 : 0));
        }
        if (!defined('EKOMI_VOTES_ON_REVIEWS')) {
            define('EKOMI_VOTES_ON_REVIEWS', ($_POST['ekomi_votes_on_reviews'] == 'true' ? 1 : 0));
        }
        if (!defined('EKOMI_URL')) {
            define('EKOMI_URL', $_POST['ekomi_url']);
        }
        if (!defined('EKOMI_AUTOLOAD_ON_SCROLL')) {
            define('EKOMI_AUTOLOAD_ON_SCROLL', ($_POST['ekomi_autoload_on_scroll'] == 'true' ? 1 : 0));
        }

        if (file_exists('config-sample.php')) {
            $config_file = file(eKOMI_APP_ABSPATH . 'config-sample.php');
        } else {

            // return error
            echo json_encode(
                array(
                    'state' => 'error',
                    'message' => ___('A config-sample.php file is needed to work from. Please re-upload this file and try again!'),
                    'db_settings' => $db_settings
                )
            );
            die();
        }

        // Loop file content
        foreach ($config_file as $line_num => $line) {

            // Ignore non define lines
            if (!preg_match('/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match)) {
                continue;
            }

            $constant = $match[1];
            $padding = $match[2];

            switch ($constant) {
                case 'DB_HOST':
                case 'DB_NAME':
                case 'DB_USER':
                case 'DB_PASSWORD':
                case 'EKOMI_APP_LOCALE':
                case 'EKOMI_APP_URI':
                case 'EKOMI_AUTOLOAD_ON_SCROLL':
                case 'EKOMI_CRON_JOB_DELAY':
                case 'EKOMI_HTML_ON_COMMENTS':
                case 'EKOMI_INTERFACE_ID':
                case 'EKOMI_INTERFACE_PASSWORD':
                case 'EKOMI_NO_REVIEWS_FOUND_ACTION':
                case 'EKOMI_NO_REVIEWS_FOUND_MESSAGE':
                case 'EKOMI_REVIEWS_PER_PAGE':
                case 'EKOMI_URL':
                case 'EKOMI_VOTES_ON_REVIEWS':
                    $config_file[$line_num] = "define('" . $constant . "'," . $padding . "'" . addcslashes(
                            constant($constant),
                            "\\'"
                        ) . "');\r";
                    break;
            }
        }
        unset($line);

        if (!is_writable(eKOMI_APP_ABSPATH)) {
            echo json_encode(
                array(
                    'state' => 'error',
                    'message' => sprintf(___('It is not possible to write in the %s folder.'), '<code>' . eKOMI_APP_ABSPATH . '</code>'),
                    'config_file' => $config_file
                )
            );

            // return proper json result
            die();
        } else {
            $path_to_config = eKOMI_APP_ABSPATH . 'config.php';

            // Generate the new config
            $handle = fopen($path_to_config, 'w');
            foreach ($config_file as $line) {
                fwrite($handle, $line);
            }
            fclose($handle);
            chmod($path_to_config, 0666);

            // return success
            echo json_encode(
                array(
                    'state' => 'success',
                    'message' => ___('Config file created.'),
                    'path_to_config' => $path_to_config
                )
            );

            // return proper json result
            die();
        }
    }


    /**
     * Calculates the range variable for the API call based on latest review in the local database.
     *
     * @param bool $rangeParameter
     * @param null $shopID
     *
     * @return string
     */
    public function prepareApiRange($rangeParameter = false, $shopID = null)
    {
        // Which shop table?
        $table = $shopID ? $shopID . $this->table_suffix_reviews : $this->table_reviews;

        // The range parameter tells the API that we need to update the reviews depending on certain time range
        $time = 0; // 0 = all
        if ($rangeParameter == true) {
            $sql = 'SELECT timestamp FROM `' . $table . '` ORDER BY timestamp DESC LIMIT 1';
            $latestReview = $this->db->get_row($sql);
            $time = $latestReview ? (time() - $latestReview->timestamp) : 0;
        }

        // Call Api depending on the range
        switch (true) {
            case $time == 0:
                $range = 'all';
                break;
            case $time < 86400 * 6:
                $range = '1w';
                break;
            case $time < 86400 * 32:
                $range = '1m';
                break;
            case $time < 86400 * 31 * 3:
                $range = '3m';
                break;
            case $time < 86400 * 31 * 6:
                $range = '6m';
                break;
            case $time < 86400 * 31 * 12:
                $range = '1y';
                break;
            default:
                $range = 'all';
        }

        return $range;
    }

    /**
     * Query DB for reviews.
     *
     * @param string $product_id
     * @param int $shopID
     * @param int $offset_page
     * @param string $sort_type
     * @param int $filter_type
     * @return array Current reviews
     * @since 2.0
     */
    public function queryReviews($product_id, $shopID = null, $offset_page = 0, $sort_type = '1', $filter_type = 0)
    {
        // Table
        $table = $shopID ? $shopID . $this->table_suffix_reviews : $this->table_reviews;

        // Initialize database
        $db = $this->initDB();

        // Product ID maybe array of IDs
        $final_product_id = is_array($product_id) ? implode('", "', $product_id) : $product_id;

        // Sanitize
        //$final_product_id = $db->escape($final_product_id);
        $offset_page = $db->escape($offset_page);
        $sort_type = $db->escape($sort_type);
        $filter_type = $db->escape($filter_type);

        /* define sorting method */
        switch ($sort_type) {
            case "1":
                $sort_type = 'timestamp DESC';
                break;
            case "2":
                $sort_type = 'timestamp ASC';
                break;
            case "3":
                $sort_type = 'helpful DESC, nothelpful ASC, timestamp DESC';
                break;
            case "4":
                $sort_type = 'stars DESC, helpful DESC, nothelpful ASC, timestamp DESC';
                break;
            case "5":
                $sort_type = 'stars ASC, nothelpful DESC, helpful ASC, timestamp DESC';
                break;
            default:
                exit();
        }

        $filter = ' AND `stars` = ' . $filter_type . ' ';
        $limit = EKOMI_REVIEWS_PER_PAGE == '-1' ? '' : ' LIMIT ' . EKOMI_REVIEWS_PER_PAGE . ' OFFSET ' . ($offset_page * EKOMI_REVIEWS_PER_PAGE);

        if ($filter_type == 0) {
            $filter = '';
        }

        // Prepare the where
        $where = is_array($product_id) ? '`product_id` IN ("' . $final_product_id . '") ' : '`product_id` = \'' . $final_product_id . '\'';

        // The sql
        $sql = "SELECT
                *
             FROM
                `" . $table . "`
             WHERE
                " . $where . "
            $filter
            ORDER BY "
            . $sort_type
            . $limit;

        // query DB
        $reviews = $this->db->get_results($sql, ARRAY_A);

        if ($this->db->last_error) {
            Utils::jsonDebugDie(
                'error',
                $this->db->last_error,
                array(
                    'in' => 'query_reviews',
                    'sSQL' => $sql
                )
            );
        }

        // Force UTF-8 encoding
        //$reviews = array_map(array('Encoding', 'toUTF8'), $reviews);

        // Prepare return data
        $reviews_data = array();

        // Insert empty review array
        $reviews_data['reviews'] = array();

        foreach ($reviews as $review) {

            // Set date to Europe/Berlin
            $timeZone = 'Europe/Berlin';
            $dateTime = new DateTime("now", new DateTimeZone($timeZone));
            $dateTime->setTimestamp($review['timestamp']);
            $date = $dateTime->format('d.m.Y H:i');

            // Normal date
            $review['date'] = $date;

            // For use in google snippet
            $review['dateISO8601'] = Utils::get_iso_time($date);

            /* helpfulness stats */
            $helpfulness_vote_total = $review['helpful'] + $review['nothelpful'];
            $review['helpful_total'] = $helpfulness_vote_total;
            $review['helpfulness_status_msg'] = '';
            if ($helpfulness_vote_total > 0) {
                $review['helpfulness_status_msg'] = sprintf(_nx('%s person found this review helpful.', '%s people out of %s found this review helpful.', $helpfulness_vote_total, 'First number is total helpful, second is total not helpful.'), '<span class="ekomi_helpful">' . $review['helpful'] . '</span> ', ' <span class="ekomi_helpful_total">' . $helpfulness_vote_total . '</span> ');
            }

            // HTML Strip Tags
            if (EKOMI_HTML_ON_COMMENTS == '0') {
                $review['review_comment_text'] = strip_tags($review['review_comment_text']);
            }

            $reviews_data['reviews'][] = $review;
        }

        // Average rating
        $reviews_data['avg_rating'] = self::getProductAverageRating($product_id, $shopID);

        // rating of reviews based on stars
        $reviews_data['stars_array'] = self::getStarsArray($product_id, $shopID);

        /* Number of reviews */
        $reviews_data['query_total_reviews'] = count($reviews);
        $reviews_data['total_product_reviews'] = self::getTotalReviewsCount($product_id, $shopID);

        // Set pagination
        $reviews_data['pages'] = 0;

        /* return number of reviews for filtered results */
        $sqlCount = "SELECT COUNT(`id`) AS `count`
                FROM
				  `" . $table . "`
				WHERE
                " . $where . "
				$filter";
        $reviews_count = $this->db->get_var($sqlCount);

        if ($this->db->last_error) {
            $reviews_count = 0;
        }

        $reviews_data['pages'] = ceil($reviews_count / EKOMI_REVIEWS_PER_PAGE);

        // Add total filtred reviews
        $reviews_data['total_reviews_with_filter'] = $reviews_count;

        return $reviews_data;
    }

    /**
     * Counts the total reviews in the local database.
     *
     * @param string $product_id
     * @param int $shopID
     * @return int
     * @since 2.0
     */
    public function getTotalReviewsCount($product_id, $shopID = null)
    {
        // Table
        $table = $shopID ? $shopID . $this->table_suffix_reviews : $this->table_reviews;

        // Initialize database
        $this->initDB();

        // Product ID maybe array of IDs
        $final_product_id = is_array($product_id) ? implode(',', $product_id) : $product_id;

        // Prepare the where
        $where = is_array($product_id) ? '`product_id` IN ("' . implode('", "', $product_id) . '") ' : "`product_id` = '" . $product_id . "'";

        $sql = 'SELECT COUNT(`id`)
                FROM `' . $table . '`
                WHERE ' . $where . '
                AND stars BETWEEN 0 AND 5';

        $reviews_count = $this->db->get_var($sql);

        if ($this->db->last_error) {
            return 0;
        }

        // return total
        return (int)$reviews_count;
    }


    /**
     * Get all product reviews from local database.
     * @param null $shopID
     *
     * @return array
     */
    public function getAllLocalReviews($shopID = null)
    {
        // Which shop table?
        $table = $shopID ? $shopID . $this->table_suffix_reviews : $this->table_reviews;

        // Initialize database
        $this->initDB();

        $sql = 'SELECT *
                FROM `' . $table . '`';

        $reviews = $this->db->get_results($sql, ARRAY_A);

        if ($this->db->last_error) {
            return array();
        }

        // return all reviews
        return $reviews;
    }

    /**
     * Calculates the average rating for single product.
     *
     * @param string $product_id
     * @param int $shopID
     * @return float|int
     * @since 2.0
     */
    public function getProductAverageRating($product_id, $shopID = null)
    {
        // Which shop table?
        $table = $shopID ? $shopID . $this->table_suffix_reviews : $this->table_reviews;

        // Initialize database
        $this->initDB();

        // Product ID maybe array of IDs
        $final_product_id = is_array($product_id) ? implode(',', $product_id) : $product_id;

        // Prepare the where
        $where = is_array($product_id) ? '`product_id` IN ("' . implode('", "', $product_id) . '") ' : '`product_id` =  "'.$final_product_id.'"';

        // Query DB
        $sql = 'SELECT AVG( `stars`)
                FROM `' . $table . '`
                WHERE ' . $where . '
                AND stars <= 5';


        $average_ekomi_review = $this->db->get_var($sql);

        // If any error return 0
        if ($this->db->last_error) {
            return 0;
        }

        // return rounded average
        return round($average_ekomi_review, 1);
    }

    /**
     * Updates the review's helpfulness.
     *
     * @param string $review_id
     * @param string $helpfulness
     * @return array
     * @since 2.0
     */
    public function rate_single_review_helpfulness(
        $review_id,
        $helpfulness
    )
    {

        // Check helpfulness data
        if ($helpfulness !== '1' && $helpfulness !== '0') {
            return array(
                'sql' => '',
                'query' => false,
                'last_error' => 'cheating huh? '
            );
        }

        // Initialize database
        $db = $this->initDB();

        // sanitize data
        $helpfulness = $db->escape($helpfulness);
        $review_id = $db->escape($review_id);

        // get the right column
        $column = $helpfulness == '1' ? 'helpful' : 'nothelpful';

        // Prepare sql statement
        $sql = 'UPDATE `' . $this->table_reviews . '`
                SET ' . $column . ' = ' . $column . ' + 1
                WHERE id = ' . $review_id;

        // Do query
        $query = $db->query($sql);

        return array(
            'sql' => $sql,
            'query' => $query,
            'last_error' => $db->last_error
        );
    }

    /**
     * Builds an array for stars.
     *
     * @param $product_id
     * @param int $shopID
     * @return array
     * @since 2.0
     */
    public function getStarsArray($product_id, $shopID = null)
    {
        // Which shop table?
        $table = $shopID ? $shopID . $this->table_suffix_reviews : $this->table_reviews;

        // Initialize database
        $this->initDB();

        // Product ID maybe array of IDs
        $final_product_id = is_array($product_id) ? implode(',', $product_id) : $product_id;

        // Prepare the where
        $where = is_array($product_id) ? '`product_id` IN ("' . implode('", "', $product_id) . '") ' : '`product_id` = "' . $final_product_id . '"';

        // Prepare the array
        $reviews_stars_stats = array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        );

        foreach ($reviews_stars_stats as $key => $value) {
            $sql = 'SELECT
                        COUNT(`id`)
                    FROM
                        `' . $table . '`
                    WHERE
                       ' . $where . '
                    AND
                        stars = ' . $key;
            $reviews_stars_stats[$key] = $this->db->get_var($sql);
        }

        // return array of stars
        return $reviews_stars_stats;
    }

    /**
     * Executes custom queries
     *
     * @param string $sql_query
     * @return array
     */
    public function customQuery(
        $sql_query
    )
    {
        // Initialize database
        $this->initDB();

        $reviews = $this->db->get_results($sql_query, ARRAY_A);

        return $reviews;
    }

    /**
     * Prepares sql queries
     * The %s (string), %d (integer) and %f (float) formats are supported.
     *
     * @param string $query
     * @param array|mixed $args
     * @return string|void
     */
    public function prepare(
        $query,
        $args
    )
    {
        if (is_null($query)) {
            return false;
        }

        // This is not meant to be foolproof -- but it will catch obviously incorrect usage.
        if (strpos($query, '%') === false) {
            die(___('The query argument of % s must have a placeholder.') . ' in ' . $query);
        }

        $args = func_get_args();
        array_shift($args);
        // If args were passed as an array (as in vsprintf), move them up
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }
        $query = str_replace("'%s'", '%s', $query); // in case someone mistakenly already singlequoted it
        $query = str_replace('"%s"', '%s', $query); // doublequote unquoting
        $query = preg_replace('|(?<!%)%f|', '%F', $query); // Force floats to be locale unaware
        $query = preg_replace('|(?<!%)%s|', "'%s'", $query); // quote the strings, avoiding escaped strings like %%s
        array_walk($args, array($this, 'escape_by_ref'));

        return @vsprintf($query, $args);
    }

    /**
     * Escapes content by reference for insertion into the database, for security
     *
     * @param string $string to escape
     * @since 2.0
     */
    public function escape_by_ref(
        &$string
    )
    {
        if (!is_float($string)) //
        {
            $string = $this->_real_escape($string);
        }
    }

    /**
     * Real escape, using mysqli_real_escape_string() or mysql_real_escape_string()
     *
     * @see mysqli_real_escape_string()
     * @see mysql_real_escape_string()     *
     * @param  string $string to escape
     * @return string escaped
     * @since 2.0
     */
    public function _real_escape($string)
    {
        if ($this->dbh) {
            return @mysql_real_escape_string($string, $this->dbh);
        }
        return addslashes($string);
    }

    /**
     * Update the value of an option that was already added.
     *
     * @param string $option_name
     * @param string $option_value
     * @return bool
     * @since 2.0
     */
    public function update_option(
        $option_name,
        $option_value

 )
    {
        // Initialize database
        $this->initDB();

        $option_name = trim($option_name);
        if (empty($option_name)) {
            return false;
        }

        if (is_object($option_value)) {
            $option_value = unserialize(serialize($option_value));
        }

        $old_value = $this->get_option($option_name);

        // If the new and old values are the same, no need to update.
        if ($option_value === $old_value) {
            return false;
        }

        // Check if option exist
        if (!$old_value) {
            return $this->add_option($option_name, $option_value);
        }

        $serialized_value = Utils::maybe_serialize($option_value);

        $sql = $this->prepare(
            "UPDATE $this->table_options SET option_value = %s  WHERE option_name =  %s",
            $serialized_value,
            $option_name
        );

        $result = $this->db->query($sql);

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve option value based on name of option.
     *
     * @param string $option
     * @return bool|mixed
     * @since 2.0
     */
    public function get_option(
        $option
    )
    {
        // Initialize database
        $this->initDB();

        $option = trim($option);
        if (empty($option)) {
            return false;
        }

        // Query db for option
        $sql = $this->prepare("SELECT option_value FROM $this->table_options WHERE option_name = %s LIMIT 1", $option);
        $row = $this->db->get_row($sql);

        // If error, then return false
        if ($this->db->last_error) {
            return false;
        }

        // Has to be get_row instead of get_var because of funkiness with 0, false, null values
        if (is_object($row)) {
            $value = $row->option_value;
        } else {

            return $row;
        }

        return Utils::maybe_unserialize($value);
    }

    /**
     * Retrieve option value based on name of option.
     *
     * @param string $option
     * @param string $value
     * @param string $autoload
     * @return bool
     * @since 2.0
     */
    public function add_option(
        $option,
        $value = '',
        $autoload = 'yes'
    )
    {
        // Initialize database
        $this->initDB();

        $option = trim($option);
        if (empty($option)) {
            return false;
        }

        $serialized_value = Utils::maybe_serialize($value);

        $sql = $this->prepare("INSERT INTO `$this->table_options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)", $option, $serialized_value, $autoload);

        $result = $this->db->query($sql);
        error_log('add_option: ' . $option . ' => ' . $sql . '===>' . $this->db->last_error);

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Executes the cron job.
     *
     * @since 2.0
     */
    public function doCronJob()
    {
        // Update the database with the new reviews
        $this->syncReviewsDatabase();

        // Update cron job timestamp
        $timeNow = time();
        $lastCronJobTime = $this->get_option('ekomi_last_cron_job_time');

        // Next cron job
        $cronJobDelay = (int)$this->get_option('ekomi_cron_job_delay');
        $nextCronJobTime = (int)$lastCronJobTime + ((int)$cronJobDelay * 60 * 60);

        // Return new cron job settings
        return array(
            'ekomi_cron_job_delay' => (int)$this->get_option('ekomi_cron_job_delay'),
            'ekomi_last_cron_job_time' => (int)$lastCronJobTime,
            'ekomi_next_cron_job_time' => (int)$nextCronJobTime,
            'ekomi_time_now' => (int)$timeNow
        );
    }

    /**
     * Counts the total reviews in local database
     *
     * @since 2.0
     */
    public function countLocalReviewsTotal()
    {
        return (int)$this->db->get_var('SELECT COUNT(`id`) FROM `' . $this->table_reviews . '`');
    }

    /**
     * Returns the shops posted from the install page; both main shop and extra shops
     */
    public function getPostedShops()
    {
        // Init the shops
        $shops = array();

        // Merge main posted shops
        $shops[$this->params['ekomiInterfaceID']] = array(
            'ekomi_interface_id' => $this->params['ekomiInterfaceID'],
            'ekomi_interface_password' => $this->params['ekomiInterfacePassword']
        );

        // Get extra shops
        $extraShops = isset($_POST['ekomi_extra_shops']) ? $_POST['ekomi_extra_shops'] : array();

        // Add extra shops
        foreach ($extraShops as $shop) {
            $shops[$shop['ekomi_interface_id']] = $shop;
        }

        return $shops;
    }

    /**
     * Returns the shops posted from the install page; both main shop and extra shops
     */
    public function getInstalledShops()
    {
        // Init the shops
        $shops = array();

        // Merge main posted shops
        $shops[$this->params['ekomiInterfaceID']] = array(
            'ekomi_interface_id' => $this->params['ekomiInterfaceID'],
            'ekomi_interface_password' => $this->params['ekomiInterfacePassword']
        );

        // Get extra shops
        $extraShops = isset($_POST['ekomi_extra_shops']) ? $_POST['ekomi_extra_shops'] : array();

        // Add extra shops
        foreach ($extraShops as $shop) {
            $shops[$shop['ekomi_interface_id']] = $shop;
        }

        return $shops;
    }
}
