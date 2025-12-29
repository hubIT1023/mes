<?php
// Front Controller

// -----------------------------
// Initialize
// -----------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseDir = __DIR__;
$baseUrl = '/mes'; // Base path if app is not at root

// -----------------------------
// Load helpers
// -----------------------------
require_once $baseDir . '/app/helpers/logger.php';

// -----------------------------
// Autoload classes
// -----------------------------
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

// -----------------------------
// Parse URI
// -----------------------------
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace($baseUrl, '', $uri);  // remove base URL
$uri = rtrim($uri, '/') ?: '/';          // default to '/'

// -----------------------------
// Load routes
// -----------------------------
$routes = require $baseDir . '/app/routes.php';

// -----------------------------
// Route matching function
// -----------------------------
function matchRoute($uri, $routePath, &$matches) {
    // Convert route parameters to regex
    $regex = preg_quote($routePath, '/');
    $regex = str_replace(['\:num', ':id'], ['(\d+)', '(\d+)'], $regex);
    $regex = '/^' . $regex . '$/i';

    return preg_match($regex, $uri, $matches);
}

// -----------------------------
// Dispatch request
// -----------------------------
foreach ($routes as $routePattern => $routeHandler) {
    [$routeMethod, $routePath] = explode(' ', $routePattern, 2);

    if ($routeMethod !== $method) {
        continue; // skip different HTTP methods
    }

    $matches = [];
    if (matchRoute($uri, $routePath, $matches)) {
        array_shift($matches); // remove full match

        if (is_array($routeHandler)) {
            $controllerName = $routeHandler[0] ?? null;
            $action = null;

            // Determine action
            if (isset($routeHandler[1]) && is_string($routeHandler[1])) {
                $action = $routeHandler[1];
            } elseif (isset($routeHandler['action'])) {
                $action = $routeHandler['action'];
            }

            if ($controllerName && $action && class_exists($controllerName) && method_exists($controllerName, $action)) {
                $controller = new $controllerName();
                call_user_func_array([$controller, $action], $matches);
                exit;
            }
        }
    }
}

// -----------------------------
// 404 Not Found
// -----------------------------
http_response_code(404);
echo "<p>The requested URL '$uri' was not found on this server.</p>";

// -----------------------------
// Debug: available routes (optional)
// -----------------------------
/*
echo "<p>Available routes:</p><ul>";
foreach (array_keys($routes) as $route) {
    echo "<li>$route</li>";
}
*/