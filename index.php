<?php
// index.php --- Front Controller

// Optional: clear OPcache in development
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Load logger helper
require_once __DIR__ . '/app/helpers/logger.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base directory
$baseDir = __DIR__;

// ----------------------
// Dynamic Base URL
// ----------------------
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// ----------------------
// Global helper function
// ----------------------
function base_url($path = '') {
    global $baseUrl;
    return $baseUrl . $path;
}

// ----------------------
// Parse Request
// ----------------------
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace($baseUrl, '', $requestUri);
$uri = rtrim($uri, '/') ?: '/';

// ----------------------
// Autoload controllers, models, config files
// ----------------------
spl_autoload_register(function($class) use ($baseDir) {
    $paths = [
        "$baseDir/app/controllers/$class.php",
        "$baseDir/app/models/$class.php",
        "$baseDir/app/config/$class.php",
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ----------------------
// Load Routes
// ----------------------
$routes = require $baseDir . '/app/routes.php';

// ----------------------
// Route Matching
// ----------------------
foreach ($routes as $routePattern => $routeHandler) {
    $routeParts = explode(' ', $routePattern, 2);
    $routeMethod = $routeParts[0] ?? '';
    $routePath   = $routeParts[1] ?? '';

    if ($routeMethod !== $method) continue;

    $regexPattern = preg_quote($routePath, '/');
    $regexPattern = preg_replace('/\\\(:num)/', '(\d+)', $regexPattern);
    $regexPattern = '/^' . $regexPattern . '$/i';

    if (preg_match($regexPattern, $uri, $matches)) {
        array_shift($matches);

        if (is_array($routeHandler)) {
            $controllerName = $routeHandler[0] ?? null;
            $action = null;

            if (isset($routeHandler[1]) && is_string($routeHandler[1])) {
                $action = $routeHandler[1];
            } elseif (isset($routeHandler['action'])) {
                $action = $routeHandler['action'];
            }

            if ($controllerName && $action && class_exists($controllerName) && method_exists($controllerName, $action)) {
                $controller = new $controllerName();
                if (!empty($matches)) {
                    $matches = array_map('intval', $matches);
                    call_user_func_array([$controller, $action], $matches);
                } else {
                    $controller->$action();
                }
                exit;
            }
        }
    }
}

// ----------------------
// Fallback: 404 Not Found
// ----------------------
http_response_code(404);
echo "<p>The requested URL '$uri' was not found on this server.</p>";
