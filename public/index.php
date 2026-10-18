<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$basePath = is_file(__DIR__.'/artisan') ? __DIR__ : dirname(__DIR__);

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

$autoload = $basePath.'/vendor/autoload.php';

if (! is_file($autoload)) {
    http_response_code(500);
    exit('Missing vendor/autoload.php. Run composer install --no-dev in the project folder.');
}

require $autoload;

/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
