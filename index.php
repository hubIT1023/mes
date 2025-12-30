<?php
// index.php —

// Disable OPcache for this request (critical on droplet)
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
    opcache_invalidate(__DIR__ . '/app/routes.php', true);
    opcache_invalidate(__DIR__ . '/app/controllers/PagesController.php', true);
}

// Load dependencies
require_once __DIR__ . '/app/helpers/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = '/mes'; // change once, everywhere updates
$_SERVER['BASE_URL'] = $baseUrl;

// load helpers
//require_once __DIR__ . '/app/helpers/url_helper.php';


// Autoloader
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

// Parse request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$uriPath = parse_url($requestUri, PHP_URL_PATH);

// Remove /mes prefix safely
$uri = preg_replace('#^' . preg_quote($baseUrl, '#') . '#', '', $uriPath);
$uri = rtrim($uri, '/') ?: '/';

// DEBUG: Log request
error_log("=== REQUEST ===");
error_log("REQUEST_URI: $requestUri");
error_log("Parsed URI: '$uri'");

// Load routes
$routes = require $baseDir . '/app/routes.php';
error_log("Routes loaded: " . count($routes));

// Route matching
foreach ($routes as $routePattern => $routeHandler) {
    $routeParts = explode(' ', $routePattern, 2);
    $routeMethod = $routeParts[0] ?? '';
    $routePath   = $routeParts[1] ?? '';

    if ($routeMethod !== $method) continue;

    $regexPattern = preg_quote($routePath, '/');
    $regexPattern = preg_replace('/\\\(:num)/', '(\d+)', $regexPattern);
    $regexPattern = '/^' . $regexPattern . '$/i';

    if (preg_match($regexPattern, $uri, $matches)) {
        error_log("✅ MATCH: $routePattern -> " . json_encode($routeHandler));
        array_shift($matches);

        if (is_array($routeHandler)) {
            $controllerName = $routeHandler[0] ?? null;
            $action = $routeHandler[1] ?? ($routeHandler['action'] ?? null);

            if ($controllerName && $action) {
                if (class_exists($controllerName) && method_exists($controllerName, $action)) {
                    $controller = new $controllerName();
                    if (!empty($matches)) {
                        $matches = array_map('intval', $matches);
                        call_user_func_array([$controller, $action], $matches);
                    } else {
                        $controller->$action();
                    }
                    exit;
                } else {
                    error_log("❌ Controller/action not found: $controllerName::$action");
                }
            }
        }
    }
}

// 404 fallback
http_response_code(404);
echo "<h1>404 Not Found</h1>";
echo "<p>URI '$uri' not matched to any route.</p>";
echo "<p>Check Apache .htaccess and OPcache.</p>";
exit;
