<?php
declare(strict_types=1);

use App\Config\Env;
use App\Controllers\AdminController;
use App\Controllers\CustomerController;
use App\Controllers\CodeAdminController;
use App\Controllers\CustomerAdminController;
use App\Controllers\AuthController;
use App\Controllers\ClaimController;
use App\Controllers\DashboardController;
use App\Controllers\HealthController;
use App\Controllers\RedeemController;
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

header('X-Content-Type-Options: nosniff');   // browser must not guess file types
header('X-Frame-Options: DENY');             // our pages cannot be embedded (clickjacking)
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');           // Back button cannot show private pages

$router = new Router($basePath);
$router->get('/health', [new HealthController(), 'check']);

$router->get('/',          [new AuthController(), 'home']);
$router->get('/register',  [new AuthController(), 'showRegister']);
$router->post('/register', [new AuthController(), 'register']);
$router->get('/login',     [new AuthController(), 'showLogin']);
$router->post('/login',    [new AuthController(), 'login']);
$router->post('/logout',   [new AuthController(), 'logout']);

$router->get('/dashboard', [new CustomerController(), 'dashboard']);
$router->get('/history',   [new CustomerController(), 'history']);
$router->get('/claim',     [new ClaimController(), 'show']);
$router->get('/scan', [new ClaimController(), 'show']);
$router->post('/claim',    [new ClaimController(), 'submit']);
$router->get('/admin',     [new DashboardController(), 'admin']);
$router->get('/admin/products',          [new AdminController(), 'products']);
$router->post('/admin/products/create',  [new AdminController(), 'createProduct']);
$router->post('/admin/products/update',  [new AdminController(), 'updateProduct']);
$router->post('/admin/products/toggle',  [new AdminController(), 'toggleProduct']);
$router->get('/admin/batches',           [new AdminController(), 'batches']);
$router->post('/admin/batches/generate', [new AdminController(), 'generateBatch']);
$router->get('/admin/batches/sheet',     [new AdminController(), 'sheet']);
$router->get('/admin/codes',            [new CodeAdminController(), 'index']);
$router->post('/admin/codes/lookup',    [new CodeAdminController(), 'lookup']);
$router->post('/admin/codes/block',     [new CodeAdminController(), 'block']);
$router->post('/admin/codes/unblock',   [new CodeAdminController(), 'unblock']);
$router->post('/admin/batches/block',   [new CodeAdminController(), 'blockBatch']);
$router->get('/admin/customers',        [new CustomerAdminController(), 'index']);
$router->post('/admin/customers/toggle', [new CustomerAdminController(), 'toggle']);
$router->get('/redeem',             [new RedeemController(), 'show']);
$router->post('/redeem',            [new RedeemController(), 'submit']);
$router->get('/admin/redemptions',  [new RedeemController(), 'adminList']);
$router->post('/admin/redemptions', [new RedeemController(), 'decide']);
$router->post('/admin/insights', [new DashboardController(), 'insights']);


$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);