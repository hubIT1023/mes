<?php
// index.php -- Front Controller

// Load logger helper
require_once __DIR__ . '/app/helpers/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseDir = __DIR__;
$baseUrl = '/mes';

// Autoload controllers, models, config files
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
});

// -----------------------------
// Parse URL and method
// -----------------------------
$method = $_SERVER['REQUEST_METHOD'];
$rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 🔒 HARDENING: enforce base URL
if (strpos($rawUri, $baseUrl) !== 0) {
    http_response_code(404);
    exit('Invalid base path');
}

// Strip base URL AFTER validation
$uri = substr($rawUri, strlen($baseUrl));
$uri = rtrim($uri, '/') ?: '/';

// -----------------------------
// Load routes
// -----------------------------
$routes = require $baseDir . '/app/routes.php';

// -----------------------------
// Route matching
// -----------------------------
foreach ($routes as $routePattern => $routeHandler) {
    $routeParts = explode(' ', $routePattern, 2);
    $routeMethod = $routeParts[0] ?? '';
    $routePath = $routeParts[1] ?? '';

    if ($routeMethod !== $method) {
        continue;
    }

    // Convert route pattern to regex
    $regexPattern = preg_quote($routePath, '/');
    $regexPattern = preg_replace('/\\\(:num)/', '(\d+)', $regexPattern);
    $regexPattern = '/^' . $regexPattern . '$/i';

    if (preg_match($regexPattern, $uri, $matches)) {
        array_shift($matches);

        if (is_array($routeHandler)) {
            $controllerName = $routeHandler[0] ?? null;
            $action = $routeHandler[1] ?? $routeHandler['action'] ?? null;

            if ($controllerName && $action &&
                class_exists($controllerName) &&
                method_exists($controllerName, $action)
            ) {
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

// -----------------------------
// 404 fallback
// -----------------------------
http_response_code(404);
echo "<p>The requested URL '$uri' was not found on this server.</p>";
