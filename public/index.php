<?php
declare(strict_types=1);

use App\Config\Env;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HealthController;
use App\Http\Session;
use App\Http\Url;
use App\Router;

require __DIR__ . '/../vendor/autoload.php';

Env::load(__DIR__ . '/../.env');

// Show detailed PHP errors only on a developer machine.
ini_set('display_errors', Env::get('APP_ENV') === 'local' ? '1' : '0');

$basePath = parse_url(Env::get('APP_URL', ''), PHP_URL_PATH) ?: '';
Url::setBase($basePath);
Session::start();

$router = new Router($basePath);
$router->get('/health', [new HealthController(), 'check']);

$router->get('/',          [new AuthController(), 'home']);
$router->get('/register',  [new AuthController(), 'showRegister']);
$router->post('/register', [new AuthController(), 'register']);
$router->get('/login',     [new AuthController(), 'showLogin']);
$router->post('/login',    [new AuthController(), 'login']);
$router->post('/logout',   [new AuthController(), 'logout']);

$router->get('/dashboard', [new DashboardController(), 'customer']);
$router->get('/admin',     [new DashboardController(), 'admin']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);