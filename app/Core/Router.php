<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $requestMethod, string $requestUri): void
    {
        $uri = parse_url($requestUri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        $method = $this->getEffectiveMethod($requestMethod);

        foreach ($this->routes as $route) {
            $pattern = preg_replace(
                '/\{(\w+)\}/',
                '(?P<$1>[^/]+)',
                $route['path']
            );
            $pattern = '#^' . $pattern . '$#';

            if (
                $route['method'] === $method &&
                preg_match($pattern, $uri, $matches)
            ) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $handlerParts = explode('@', $route['handler']);
                $controllerName = $handlerParts[0];
                $methodName = $handlerParts[1];

                $this->callController($controllerName, $methodName, $params);
                return;
            }
        }

        http_response_code(404);
        $this->renderError(404, 'Page not found');
    }

    public function registerDefaultRoutes(): void
    {
        // Auth routes
        $this->add('GET',  '/login',           'AuthController@loginForm');
        $this->add('POST', '/login',           'AuthController@login');
        $this->add('POST', '/logout',          'AuthController@logout');
        $this->add('GET',  '/register',        'AuthController@registerForm');
        $this->add('POST', '/register',        'AuthController@register');
        $this->add('POST', '/forgot-password', 'AuthController@forgotPassword');
        $this->add('POST', '/reset-password',  'AuthController@resetPassword');

        // Dashboard
        $this->add('GET', '/admin/dashboard', 'DashboardController@index');
        $this->add('GET', '/staff/dashboard', 'DashboardController@staff');

        // Admin notice management
        $this->add('GET',  '/admin/notices',          'NoticeController@index');
        $this->add('GET',  '/admin/notices/create',   'NoticeController@createForm');
        $this->add('POST', '/admin/notices/create',   'NoticeController@create');
        $this->add('GET',  '/admin/notices/edit/{id}','NoticeController@editForm');
        $this->add('POST', '/admin/notices/edit/{id}','NoticeController@update');
        $this->add('POST', '/admin/notices/delete/{id}', 'NoticeController@delete');
        $this->add('POST', '/admin/notices/approve/{id}', 'NoticeController@approve');
        $this->add('POST', '/admin/notices/reject/{id}',  'NoticeController@reject');
        $this->add('POST', '/admin/notices/duplicate/{id}','NoticeController@duplicate');
        $this->add('POST', '/admin/notices/pin/{id}',     'NoticeController@pin');
        $this->add('GET',  '/admin/notices/pending',      'NoticeController@pending');

        // Public / staff notice routes
        $this->add('GET', '/notice/{id}', 'NoticeController@show');

        // API routes
        $this->add('GET',    '/api/notices/active',          'NoticeController@apiActive');
        $this->add('GET',    '/api/notices/search',          'NoticeController@apiSearch');
        $this->add('GET',    '/api/notices/calendar',        'NoticeController@apiCalendar');
        $this->add('POST',   '/api/notices/bookmark/{id}',   'NoticeController@bookmark');
        $this->add('DELETE', '/api/notices/bookmark/{id}',   'NoticeController@unbookmark');
        $this->add('POST',   '/api/notices/archive/{id}',    'NoticeController@archive');
        $this->add('GET',    '/api/notices/bookmarks',        'NoticeController@apiBookmarks');
        $this->add('GET',    '/api/notices/archived',          'NoticeController@apiArchived');

        // Reports
        $this->add('GET',  '/admin/reports',           'ReportController@index');
        $this->add('POST', '/admin/reports/generate',  'ReportController@generate');

        // Analytics
        $this->add('GET', '/admin/analytics', 'AnalyticsController@index');

        // Profile
        $this->add('GET',  '/profile', 'UserController@profileForm');
        $this->add('POST', '/profile', 'UserController@updateProfile');

        // Category management
        $this->add('GET',  '/admin/categories',        'CategoryController@index');
        $this->add('POST', '/admin/categories/create', 'CategoryController@create');
        $this->add('POST', '/admin/categories/edit/{id}', 'CategoryController@update');
        $this->add('POST', '/admin/categories/delete/{id}', 'CategoryController@delete');

        // Faculty management
        $this->add('GET',  '/admin/faculties',          'FacultyController@index');
        $this->add('POST', '/admin/faculties/create',   'FacultyController@create');
        $this->add('POST', '/admin/faculties/edit/{id}','FacultyController@update');
        $this->add('POST', '/admin/faculties/delete/{id}', 'FacultyController@delete');

        // Department management
        $this->add('GET',  '/admin/departments',          'DepartmentController@index');
        $this->add('POST', '/admin/departments/create',   'DepartmentController@create');
        $this->add('POST', '/admin/departments/edit/{id}','DepartmentController@update');
        $this->add('POST', '/admin/departments/delete/{id}', 'DepartmentController@delete');

        // Programme management
        $this->add('GET',  '/admin/programmes',          'ProgrammeController@index');
        $this->add('POST', '/admin/programmes/create',   'ProgrammeController@create');
        $this->add('POST', '/admin/programmes/edit/{id}','ProgrammeController@update');
        $this->add('POST', '/admin/programmes/delete/{id}', 'ProgrammeController@delete');

        // Level management
        $this->add('GET',  '/admin/levels',          'LevelController@index');
        $this->add('POST', '/admin/levels/create',   'LevelController@create');
        $this->add('POST', '/admin/levels/edit/{id}','LevelController@update');
        $this->add('POST', '/admin/levels/delete/{id}', 'LevelController@delete');

        // User management
        $this->add('GET',  '/admin/users',        'UserController@index');
        $this->add('POST', '/admin/users/create', 'UserController@create');
        $this->add('POST', '/admin/users/edit/{id}', 'UserController@update');
        $this->add('POST', '/admin/users/delete/{id}', 'UserController@delete');

        // Activity log
        $this->add('GET', '/admin/activity-log', 'ActivityLogController@index');

        // Home / public
        $this->add('GET', '/', 'PublicController@index');
    }

    private function getEffectiveMethod(string $requestMethod): string
    {
        $method = strtoupper($requestMethod);

        if ($method === 'POST') {
            if (!empty($_POST['_method'])) {
                return strtoupper($_POST['_method']);
            }
            $header = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '';
            if (!empty($header)) {
                return strtoupper($header);
            }
        }

        return $method;
    }

    private function callController(string $controllerName, string $methodName, array $params): void
    {
        $fullClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($fullClass)) {
            http_response_code(500);
            $this->renderError(500, "Controller $fullClass not found");
            return;
        }

        $controller = new $fullClass();

        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            $this->renderError(500, "Method $methodName not found in $fullClass");
            return;
        }

        $controller->$methodName($params);
    }

    private function renderError(int $code, string $message): void
    {
        http_response_code($code);
        echo '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>' . $code . ' - ' . htmlspecialchars($message) . '</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container" style="text-align:center;padding:4rem 1rem;">
    <h1>' . $code . '</h1>
    <p>' . htmlspecialchars($message) . '</p>
    <a href="/" class="btn btn-primary mt-2">Go Home</a>
</div>
</body>
</html>';
    }
}
