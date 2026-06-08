<?php

declare(strict_types=1);

use App\Core\Env;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Env.php';

Env::load(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

session_start();

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '/') {
    $basePath = '';
}

define('APP_BASE_PATH', $basePath);

if (!function_exists('app_url')) {
    function app_url(string $path): string
    {
        return rtrim(APP_BASE_PATH, '/') . $path;
    }
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR .
        str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

    if (is_file($path)) {
        require_once $path;
        
    }
});

$appConfig = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';
date_default_timezone_set($appConfig['timezone']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (APP_BASE_PATH !== '' && str_starts_with($uri, APP_BASE_PATH)) {
    $uri = substr($uri, strlen(APP_BASE_PATH)) ?: '/';
}
$uri = rtrim($uri, '/') ?: '/';

$routes = [
    'GET' => [
        '/login' => [App\Controllers\UiController::class, 'login'],
        '/' => [App\Controllers\UiController::class, 'dashboard'],
        '/products' => [App\Controllers\UiController::class, 'products'],
        '/products/new' => [App\Controllers\UiController::class, 'newProduct'],
        '/categories' => [App\Controllers\UiController::class, 'categories'],
        '/stock' => [App\Controllers\UiController::class, 'stock'],
        '/purchase-orders' => [App\Controllers\UiController::class, 'purchaseOrders'],
        '/low-stock' => [App\Controllers\UiController::class, 'lowStock'],
        '/suppliers' => [App\Controllers\UiController::class, 'suppliers'],
        '/history' => [App\Controllers\UiController::class, 'history'],
        '/users' => [App\Controllers\UiController::class, 'users'],
        '/employees/search' => [App\Controllers\UiController::class, 'employeeSearch'],
        '/reports' => [App\Controllers\UiController::class, 'reports'],
        '/accountability' => [App\Controllers\UiController::class, 'accountability'],
        '/accountability/new' => [App\Controllers\UiController::class, 'newAccountability'],
    ],
    'POST' => [
        '/login' => [App\Controllers\UiController::class, 'authenticate'],
        '/logout' => [App\Controllers\UiController::class, 'logout'],
        '/products' => [App\Controllers\UiController::class, 'createProduct'],
        '/categories' => [App\Controllers\UiController::class, 'createCategory'],
        '/suppliers' => [App\Controllers\UiController::class, 'createSupplier'],
        '/stock' => [App\Controllers\UiController::class, 'createStockMovement'],
        '/purchase-orders' => [App\Controllers\UiController::class, 'createPurchaseOrder'],
        '/accountability' => [App\Controllers\UiController::class, 'createAccountability'],
    ],
];

$publicRoutes = [
    'GET' => ['/login'],
    'POST' => ['/login'],
];
$isPublicRoute = in_array($uri, $publicRoutes[$method] ?? [], true);
$authRole = (string) ($_SESSION['auth_employee_role'] ?? '');
$authEmployeeId = (int) ($_SESSION['auth_employee_id'] ?? 0);

if (!$isPublicRoute && ($authEmployeeId <= 0 || $authRole !== 'internal')) {
    if ($authEmployeeId > 0) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    $next = $uri !== '' ? $uri : '/';
    header('Location: ' . app_url('/login?next=' . rawurlencode($next)));
    exit;
}

$handler = $routes[$method][$uri] ?? null;
if ($handler === null && $method === 'GET' && preg_match('#^/accountability/(\d+)$#', $uri, $matches) === 1) {
    $handler = [App\Controllers\UiController::class, 'showAccountability'];
    $_GET['id'] = $matches[1];
}
if ($handler === null && $method === 'GET' && preg_match('#^/users/(\d+)$#', $uri, $matches) === 1) {
    $handler = [App\Controllers\UiController::class, 'showEmployeeTransactions'];
    $_GET['id'] = $matches[1];
}
if ($handler === null && $method === 'POST' && preg_match('#^/products/(\d+)/update$#', $uri, $matches) === 1) {
    $handler = [App\Controllers\UiController::class, 'updateProduct'];
    $_POST['id'] = $matches[1];
}
if ($handler === null && $method === 'GET' && preg_match('#^/accountability/(\d+)/print$#', $uri, $matches) === 1) {
    $handler = [App\Controllers\UiController::class, 'printAccountability'];
    $_GET['id'] = $matches[1];
}
if ($handler === null && $method === 'POST' && preg_match('#^/accountability/(\d+)/return$#', $uri, $matches) === 1) {
    $handler = [App\Controllers\UiController::class, 'returnAccountability'];
    $_POST['id'] = $matches[1];
}
if ($handler === null && $method === 'POST' && preg_match('#^/purchase-orders/(\d+)/send$#', $uri, $matches) === 1) {
    $handler = [App\Controllers\UiController::class, 'sendPurchaseOrder'];
    $_POST['id'] = $matches[1];
}
if ($handler === null && $method === 'POST' && preg_match('#^/purchase-orders/(\d+)/receive$#', $uri, $matches) === 1) {
    $handler = [App\Controllers\UiController::class, 'receivePurchaseOrder'];
    $_POST['id'] = $matches[1];
}
if ($handler === null && $method === 'POST' && preg_match('#^/purchase-orders/(\d+)/cancel$#', $uri, $matches) === 1) {
    $handler = [App\Controllers\UiController::class, 'cancelPurchaseOrder'];
    $_POST['id'] = $matches[1];
}
if ($handler === null) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

[$controllerClass, $action] = $handler;
$controller = new $controllerClass();
$controller->{$action}();
