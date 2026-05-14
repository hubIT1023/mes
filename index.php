<?php
// index.php -- Front Controller (UPDATED)

require_once __DIR__ . '/app/middleware/logger.php'; // optional logging helper

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseDir = __DIR__;  // project root

// -------------------------------------------------
// 🔧 DYNAMIC BASE URL DETECTION
// Works at root (/) or any subdirectory (/mes, /app, etc.)
// -------------------------------------------------
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = rtrim($scriptName, '/') ?: '/';

// -------------------------------------------------
// SINGLE, ENHANCED AUTOLOADER
// -------------------------------------------------
spl_autoload_register(function ($class) use ($baseDir) {
    $paths = [
        "$baseDir/app/controllers/$class.php",
        "$baseDir/app/models/$class.php",
        "$baseDir/app/config/$class.php",
        "$baseDir/app/middleware/$class.php",
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            error_log("[AUTOLOAD] Loaded class '$class' from $path");
            return;
        }
    }

    error_log("[AUTOLOAD FAIL] Could not load class '$class'. Tried paths: " . implode(', ', $paths));
});

// -------------------------------------------------
// CLI vs HTTP
// -------------------------------------------------
if (php_sapi_name() === 'cli') {
    // Stop routing in CLI (for cron, scheduler scripts)
    return;
}

// -------------------------------------------------
// Parse URL & enforce base path
// -------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'];
$rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize: ensure baseUrl ends with / for consistent matching
$normalizedBase = $baseUrl === '/' ? '/' : rtrim($baseUrl, '/') . '/';

// If request is exactly the base URL, redirect to add trailing slash
if ($rawUri === rtrim($baseUrl, '/')) {
    header("Location: $normalizedBase");
    exit;
}

// Validate base path (only if not root)
if ($baseUrl !== '/' && strpos($rawUri, $normalizedBase) !== 0) {
    http_response_code(404);
    exit('Invalid base path. Expected: ' . $normalizedBase);
}

// Extract URI relative to base
if ($baseUrl === '/') {
    $uri = $rawUri;
} else {
    $uri = substr($rawUri, strlen(rtrim($baseUrl, '/')));
}
$uri = rtrim($uri, '/') ?: '/';

// -------------------------------------------------
// Load routes
// -------------------------------------------------
$routesPath = $baseDir . '/app/routes.php';
if (!file_exists($routesPath)) {
    error_log("Routes file not found: $routesPath");
    http_response_code(500);
    exit('Internal server error');
}

$routes = require $routesPath;
if (!is_array($routes)) {
    error_log("Routes file did not return an array");
    http_response_code(500);
    exit('Internal server error');
}

// -------------------------------------------------
// Route matching
// -------------------------------------------------
$matched = false;
foreach ($routes as $routePattern => $routeHandler) {
    if (!is_string($routePattern) || !is_array($routeHandler)) continue;

    $parts = explode(' ', $routePattern, 2);
    if (count($parts) !== 2) continue;

    [$routeMethod, $routePath] = $parts;
    if ($routeMethod !== $method) continue;

    // Convert route pattern to regex (support :num placeholder)
    $regex = preg_quote($routePath, '/');
    $regex = preg_replace('/\\\(:num)/', '(\d+)', $regex);
    $regex = '/^' . $regex . '$/i';

    if (!preg_match($regex, $uri, $matches)) continue;

    array_shift($matches); // Remove full match
    $matched = true;

    $controllerName = $routeHandler[0] ?? null;
    $action = $routeHandler[1] ?? ($routeHandler['action'] ?? null);

    if (!$controllerName || !$action || !class_exists($controllerName) || !method_exists($controllerName, $action)) {
        error_log("Invalid route handler for: $routePattern");
        http_response_code(500);
        exit('Controller or action not found');
    }

    try {
        $controller = new $controllerName();
        if ($matches) {
            // Convert numeric params to int, leave others as string
            $params = array_map(function($val) {
                return is_numeric($val) && ctype_digit($val) ? intval($val) : $val;
            }, $matches);
            call_user_func_array([$controller, $action], $params);
        } else {
            $controller->$action();
        }
        exit;
    } catch (Exception $e) {
        error_log("Controller error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        http_response_code(500);
        exit('Internal error in controller');
    } catch (Error $e) {
        error_log("Fatal error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        http_response_code(500);
        exit('Fatal error in controller');
    }
}

// -------------------------------------------------
// 404 fallback
// -------------------------------------------------
http_response_code(404);

// Load custom 404 page if exists
$notFoundPage = __DIR__ . '/404/404.php';
if (file_exists($notFoundPage)) {
    include $notFoundPage;
} else {
    echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body>";
    echo "<h1>404 - Page Not Found</h1>";
    echo "<p>The requested URL <code>" . htmlspecialchars($_SERVER['REQUEST_URI']) . "</code> was not found.</p>";
    echo "<p><a href='" . htmlspecialchars($baseUrl) . "'>Return to Home</a></p>";
    echo "</body></html>";
}
exit;