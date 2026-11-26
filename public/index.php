<?php
declare(strict_types=1);

use App\Config\Env;
use App\Controllers\HealthController;
use App\Router;

require __DIR__ . '/../vendor/autoload.php';

Env::load(__DIR__ . '/../.env');

// Show detailed PHP errors only on a developer machine.
ini_set('display_errors', Env::get('APP_ENV') === 'local' ? '1' : '0');

$basePath = parse_url(Env::get('APP_URL', ''), PHP_URL_PATH) ?: '';

$router = new Router($basePath);
$router->get('/health', [new HealthController(), 'check']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);