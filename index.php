<?php
// Front Controller

require_once __DIR__ . '/app/helpers/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseDir = __DIR__;

// Autoload
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

// === DYNAMICALLY COMPUTE BASE URL ===
// This works whether the app is in root or in /mes/
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /mes/index.php
$scriptDir = dirname($scriptName);    // e.g., /mes
if ($scriptDir === '/') {
    $baseUrl = '';
} else {
    $baseUrl = rtrim($scriptDir, '/');
}

// Parse actual path
$fullUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Remove base path
if ($baseUrl !== '' && strpos($fullUri, $baseUrl) === 0) {
    $uri = substr($fullUri, strlen($baseUrl));
} else {
    $uri = $fullUri;
}
$uri = rtrim($uri, '/') ?: '/';

$method = $_SERVER['REQUEST_METHOD'];

// Load routes
$routes = require $baseDir . '/app/routes.php';

// Route matching
foreach ($routes as $routePattern => $routeHandler) {
    $parts = explode(' ', $routePattern, 2);
    $routeMethod = $parts[0] ?? '';
    $routePath = $parts[1] ?? '';

    if ($routeMethod !== $method) continue;

    // Convert :num to (\d+)
    $regex = preg_quote($routePath, '/');
    $regex = preg_replace('/\\\(:num)/', '(\d+)', $regex);
    $regex = '/^' . $regex . '$/i';

    if (preg_match($regex, $uri, $matches)) {
        array_shift($matches); // Remove full match

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

// If no route matched
http_response_code(404);
echo "<p>The requested URL '$fullUri' was not found on this server.</p>";
exit;