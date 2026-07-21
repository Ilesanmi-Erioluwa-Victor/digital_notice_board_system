<?php
/**
 * Router — Simple Front Controller Router
 *
 * Maps HTTP method + URL path patterns to controller methods.
 * Supports route parameters ({id}, {slug}, etc.) extracted into
 * the $params argument of the controller method.
 *
 * Entry point: public/index.php registers all routes, then calls
 * Router::dispatch() which resolves and executes the matching handler.
 */

namespace App\Core;

class Router
{
    /**
     * Registered routes: array of [method, pattern, handler].
     */
    private array $routes = [];

    /**
     * Register a route.
     *
     * @param string $method  HTTP method (GET, POST)
     * @param string $path    URL path pattern (e.g., /notice/{id})
     * @param string $handler Controller@method string (e.g., NoticeController@show)
     */
    public function add(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => $path,
            'handler' => $handler,
        ];
    }

    /**
     * Dispatch the current request to the matching route handler.
     *
     * @param string $requestMethod
     * @param string $requestUri
     */
    public function dispatch(string $requestMethod, string $requestUri): void
    {
        // Normalize URI: strip query string and trailing slash
        $uri = parse_url($requestUri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Remove base path if behind a subdirectory (for Render deployment)
        // $uri = preg_replace('#^/subdirectory#', '', $uri);

        foreach ($this->routes as $route) {
            // Convert route pattern to regex: {param} -> named capture group
            $pattern = preg_replace(
                '/\{(\w+)\}/',
                '(?P<$1>[^/]+)',
                $route['path']
            );
            $pattern = '#^' . $pattern . '$#';

            if (
                $route['method'] === strtoupper($requestMethod) &&
                preg_match($pattern, $uri, $matches)
            ) {
                // Extract named route parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Resolve handler: ControllerName@method
                $handlerParts = explode('@', $route['handler']);
                $controllerName = $handlerParts[0];
                $methodName = $handlerParts[1];

                $this->callController($controllerName, $methodName, $params);
                return;
            }
        }

        // No route matched — 404
        http_response_code(404);
        $this->renderError(404, 'Page not found');
    }

    /**
     * Instantiate the controller and call the method with params.
     *
     * @param string $controllerName
     * @param string $methodName
     * @param array  $params
     */
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

        // Call method with route params as associative array
        $controller->$methodName($params);
    }

    /**
     * Render a simple error page.
     *
     * @param int    $code
     * @param string $message
     */
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
