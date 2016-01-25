<?php

date_default_timezone_set('UTC');

use Phalcon\Mvc\Application;

error_reporting(E_ALL);
ini_set('display_errors', 1);

try
{
    /**
     * Define some useful constants
     */
    define('BASE_DIR', dirname(__DIR__));
    define('APPS_DIR', BASE_DIR.'/apps');

    /**
     * Read configuration
     */
    $config = include APPS_DIR . '/config/config.php';
    $lang_es = include APPS_DIR . '/config/lang/es.php';
    /**
     * Auto-loader configuration
     */
    require APPS_DIR . '/config/loader.php';
    /**
     * Include services
     */    
    require APPS_DIR . '/config/services.php';

    /**
     * Handle the request
     */
    $application = new Application($di);

    /**
     * Setup cache
     */
    require APPS_DIR . '/config/cache.php';

    /**
     * Include modules
     */
    require APPS_DIR . '/config/modules.php';

    echo $application->handle()->getContent();
}
catch (Phalcon\Exception $e)
{
    echo("404 or check error details:<br />");
    echo $e->getMessage();
}
catch (PDOException $e)
{
    echo $e->getMessage();
}