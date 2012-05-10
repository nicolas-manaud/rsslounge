<?php

// base configuration
require_once('const.php');

// install or run
if(!file_exists(CONFIG_PATH))
    require_once('updates/install.php');
else {

    /** Zend_Application */
    require_once 'Zend/Application.php';  

    // Create application, bootstrap, and run
    $application = new Zend_Application(
        APPLICATION_ENV, 
        CONFIG_PATH
    );
    $application->bootstrap()
                ->run();

}