<?php
/**
 * Front Controller — Single Entry Point
 *
 * All HTTP requests are routed through this file via .htaccess mod_rewrite.
 * Initializes autoloading, session, configuration, and dispatches the request
 * to the appropriate controller method via the Router.
 */

declare(strict_types=1);

// Autoload Composer dependencies and PSR-4 namespaces
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration constants
$config = require __DIR__ . '/../config/config.php';

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Instantiate the router and register all application routes
$router = new App\Core\Router();

// ─── Auth Routes ────────────────────────────────────────────────────────────
$router->add('GET', '/login', 'AuthController@loginForm');
$router->add('POST', '/login', 'AuthController@login');
$router->add('POST', '/logout', 'AuthController@logout');
$router->add('GET', '/register', 'AuthController@registerForm');
$router->add('POST', '/register', 'AuthController@register');

// ─── Public Routes ──────────────────────────────────────────────────────────
$router->add('GET', '/', 'PublicController@home');
$router->add('GET', '/notice/{id}', 'PublicController@noticeDetail');
$router->add('GET', '/kiosk', 'PublicController@kiosk');

// ─── API Routes ─────────────────────────────────────────────────────────────
$router->add('GET', '/api/notices/active', 'NoticeController@apiActive');
$router->add('GET', '/api/notices/search', 'NoticeController@apiSearch');

// ─── Admin Routes ───────────────────────────────────────────────────────────
$router->add('GET', '/admin/dashboard', 'DashboardController@index');
$router->add('GET', '/admin/notices', 'NoticeController@index');
$router->add('GET', '/admin/notices/create', 'NoticeController@createForm');
$router->add('POST', '/admin/notices/create', 'NoticeController@create');
$router->add('GET', '/admin/notices/edit/{id}', 'NoticeController@editForm');
$router->add('POST', '/admin/notices/edit/{id}', 'NoticeController@update');
$router->add('POST', '/admin/notices/delete/{id}', 'NoticeController@delete');
$router->add('GET', '/admin/categories', 'CategoryController@index');
$router->add('POST', '/admin/categories/create', 'CategoryController@create');
$router->add('POST', '/admin/categories/edit/{id}', 'CategoryController@update');
$router->add('POST', '/admin/categories/delete/{id}', 'CategoryController@delete');
$router->add('GET', '/admin/users', 'UserController@index');
$router->add('POST', '/admin/users/role/{id}', 'UserController@updateRole');
$router->add('POST', '/admin/users/delete/{id}', 'UserController@delete');
$router->add('GET', '/admin/logs', 'ActivityLogController@index');

// Dispatch the current request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
