<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$router = new App\Core\Router();

// Register all default routes from the Router
$router->registerDefaultRoutes();

// Additional routes not covered by defaults
$router->add('GET', '/kiosk', 'PublicController@kiosk');
$router->add('GET', '/bookmarks', 'PublicController@bookmarks');
$router->add('GET', '/archived', 'PublicController@archived');
$router->add('GET', '/admin/users/role/{id}', 'UserController@updateRole');
$router->add('POST', '/admin/users/toggle-active/{id}', 'UserController@toggleActive');
$router->add('GET', '/admin/logs', 'ActivityLogController@index');
$router->add('GET', '/admin/notices/api/export', 'NoticeController@apiExport');

// API extra routes
$router->add('GET', '/api/faculties', 'FacultyController@apiAll');
$router->add('GET', '/api/departments', 'DepartmentController@apiByFaculty');
$router->add('GET', '/api/programmes', 'ProgrammeController@apiByDepartment');
$router->add('GET', '/api/levels', 'LevelController@apiAll');
$router->add('GET', '/api/activity-log/recent', 'ActivityLogController@apiRecent');
$router->add('GET', '/api/activity-log/entity', 'ActivityLogController@apiByEntity');
$router->add('GET', '/api/analytics/monthly', 'AnalyticsController@apiMonthlyNotices');
$router->add('GET', '/api/analytics/categories', 'AnalyticsController@apiCategoryDistribution');
$router->add('GET', '/api/analytics/most-viewed', 'AnalyticsController@apiMostViewed');
$router->add('GET', '/api/analytics/status-breakdown', 'AnalyticsController@apiStatusBreakdown');
$router->add('GET', '/api/analytics/summary', 'AnalyticsController@apiSummary');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
