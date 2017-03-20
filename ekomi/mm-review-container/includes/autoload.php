<?php
/*ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);*/

/** Define eKOMI_APP_ABSPATH as this file's directory */
define('eKOMI_APP_ABSPATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('eKOMI_LIB_PATH', eKOMI_APP_ABSPATH . 'includes' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);

/*** nullify any existing autoloads ***/
spl_autoload_register(null, false);

/*** specify extensions that may be loaded ***/
spl_autoload_extensions('.php');

// Add libraries to the include path
set_include_path(get_include_path() . PATH_SEPARATOR . eKOMI_LIB_PATH);

/*** class Loader ***/
function autoloadWhatIsInIncludes($className)
{

    $file = eKOMI_APP_ABSPATH . 'includes/' . $className . '.php';
    if (!file_exists($file)) {
        return false;
    }
    require $file;
}

function autoloadWhatIsInLib($className)
{

    $file = eKOMI_APP_ABSPATH . 'includes/lib' . DIRECTORY_SEPARATOR . $className . '.php';
    if (!file_exists($file)) {
        return false;
    }
    require $file;
}

/*** register the loader functions ***/
spl_autoload_register('autoloadWhatIsInIncludes');
spl_autoload_register('autoloadWhatIsInLib');


// App classes Loader
function autoloadEkomiAppDependencies($class)
{

    $classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $class)));

    // Add App folder
    $classFile = eKOMI_LIB_PATH . $classFile;

    // Add extension
    $classFile .= '.php';

    if (!file_exists($classFile)) {
        // echo $classFile;
        return false;
    }

    include $classFile;
}

spl_autoload_register('autoloadEkomiAppDependencies');