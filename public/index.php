<?php

/**
 * Mesut Vatansever | mesut.vts@gmail.com
 * Date: 15/06/16 14:55
 */

error_reporting(E_WARNING);

require_once "../vendor/autoload.php";

$auth = require_once "../config/auth.php";
$basic_auth = new \App\Middleware\BasicAuth($auth['basic_auths']);

$app = new \Slim\App([
    'settings' => require_once "../config/settings.php",
    'connection' => require_once "../config/connection.php"
]);

// Helpers
require_once "../app/Helper/repository.php";

// Basic Authentication for all routes
$app->add($basic_auth);

require_once "../app/routes.php";

$app->run();