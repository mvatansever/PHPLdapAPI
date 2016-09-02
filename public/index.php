<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 15/06/16 14:55
 */

error_reporting(E_ERROR);

require_once "../vendor/autoload.php";

$auth = require_once "../config/auth.php";

$app = new \Slim\App([
    'settings' => require_once "../config/settings.php",
    'connection' => require_once "../config/connection.php"
]);

// Helpers
require_once "../app/Helper/repository.php";

if ($app->getContainer()->get('settings')['basic_auth']) {
    // Basic Authentication for all routes
    $basic_auth = new \App\Middleware\BasicAuth($auth['basic_auths']);
    $app->add($basic_auth);
}

require_once "../app/routes.php";

$app->run();