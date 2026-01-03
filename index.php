<?php
// index.php -- Front Controller

require_once __DIR__ . '/app/helpers/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseDir = __DIR__;
$baseUrl = '/mes';

// -------------------------------------------------
// Enhanced Autoloader with Debugging
// -------------------------------------------------
spl_autoload_register(function ($class) use ($baseDir) {
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
    // 🔥 TEMPORARY DEBUG: log what it tried
    error_log("Autoloader tried and failed to load class '$class' from: " . json_encode($paths));
});

// -------------------------------------------------
// Parse URL & enforce base path (HARDENING)
// -------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'];
$rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($rawUri === $baseUrl) {
    $rawUri .= '/';
}

if (strpos($rawUri, $baseUrl) !== 0) {
    http_response_code(404);
    exit('Invalid base path');
}

$uri = substr($rawUri, strlen($baseUrl));
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
    if (!is_string($routePattern) || !is_array($routeHandler)) {
        continue;
    }

    $parts = explode(' ', $routePattern, 2);
    if (count($parts) !== 2) continue;

    [$routeMethod, $routePath] = $parts;

    if ($routeMethod !== $method) {
        continue;
    }

    $regex = preg_quote($routePath, '/');
    $regex = preg_replace('/\\\(:num)/', '(\d+)', $regex);
    $regex = '/^' . $regex . '$/i';

    if (!preg_match($regex, $uri, $matches)) {
        continue;
    }

    array_shift($matches);
    $matched = true;

    $controllerName = $routeHandler[0] ?? null;
    $action = $routeHandler[1] ?? ($routeHandler['action'] ?? null);

    if (
        !$controllerName ||
        !$action ||
        !class_exists($controllerName) ||
        !method_exists($controllerName, $action)
    ) {
        error_log("Invalid route handler for: $routePattern");
        http_response_code(500);
        exit('Controller or action not found');
    }

    try {
        $controller = new $controllerName();
        if ($matches) {
            call_user_func_array([$controller, $action], array_map('intval', $matches));
        } else {
            $controller->$action();
        }
        exit;
    } catch (Exception $e) {
        error_log("Controller error: " . $e->getMessage());
        http_response_code(500);
        exit('Internal error in controller');
    }
}

// -------------------------------------------------
// 404 fallback
// -------------------------------------------------
http_response_code(404);
echo "<p>The requested URL '$uri' was not found.</p>";
echo "<p>Available routes:</p><ul>";
foreach (array_keys($routes) as $route) {
    echo "<li>" . htmlspecialchars($route) . "</li>";
}
echo "</ul>";