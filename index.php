<?php
// index.php --- Front Controller

// Optional: clear OPcache in development to reflect changes immediately
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Load logger helper
require_once __DIR__ . '/app/helpers/logger.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseDir = __DIR__;

// ----------------------
// Dynamic Base URL
// ----------------------
// Computes the base URL automatically depending on deployment
// Example:
// Local: http://localhost/mes/       -> /mes
// Droplet: https://example.com/     -> ''
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// ----------------------
// Parse Request
// ----------------------
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize URI for routing
$uri = str_replace($baseUrl, '', $requestUri);
$uri = rtrim($uri, '/') ?: '/';

// ----------------------
// Temporary Debug (remove in production)
// ----------------------

echo '<pre>';
var_dump([
    'REQUEST_URI' => $_SERVER['REQUEST_URI'],
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
    'baseUrl'     => $baseUrl,
    'uri'         => $uri
]);
exit;


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
    // Split route into HTTP method and path
    $routeParts = explode(' ', $routePattern, 2);
    $routeMethod = $routeParts[0] ?? '';
    $routePath   = $routeParts[1] ?? '';

    // Skip if HTTP method doesn't match
    if ($routeMethod !== $method) continue;

    // Convert route pattern to regex for parameter matching
    $regexPattern = preg_quote($routePath, '/');
    $regexPattern = preg_replace('/\\\(:num)/', '(\d+)', $regexPattern);
    $regexPattern = '/^' . $regexPattern . '$/i';

    if (preg_match($regexPattern, $uri, $matches)) {
        array_shift($matches);

        if (is_array($routeHandler)) {
            $controllerName = $routeHandler[0] ?? null;
            $action = null;

            // Support both formats: ['Controller','method'] or ['Controller','action'=>'method']
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
