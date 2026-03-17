<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// #region agent log
$__log = function () {
    $path = __DIR__ . '/../debug-95891a.log';
    $line = json_encode(['sessionId' => '95891a', 'hypothesisId' => 'E', 'location' => 'public/index.php', 'message' => 'request_received', 'data' => ['uri' => $_SERVER['REQUEST_URI'] ?? ''], 'timestamp' => (int)(microtime(true) * 1000)]) . "\n";
    @file_put_contents($path, $line, FILE_APPEND);
};
$__log();
// #endregion

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
