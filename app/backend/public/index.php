<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Load environment-specific configuration
$basePath = dirname(__DIR__);
$baseEnvFile = $basePath . '/.env';

// Load base .env file first
if (file_exists($baseEnvFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable($basePath, '.env');
    $dotenv->safeLoad();
}

// Determine environment and load environment-specific file
$appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'local';
$envSpecificFile = $basePath . "/.env.{$appEnv}";

if (file_exists($envSpecificFile)) {
    $dotenvEnv = Dotenv\Dotenv::createImmutable($basePath, ".env.{$appEnv}");
    $dotenvEnv->load(); // Override with environment-specific values
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
