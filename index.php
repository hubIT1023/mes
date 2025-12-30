<?php
// Front Controller

require_once __DIR__ . '/app/helpers/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseDir = __DIR__;

// === DYNAMICALLY COMPUTE BASE URL ===
// Works whether the app is in root or a subfolder (e.g., /mes)
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /mes/index.php
$scriptDir  = dirname($scriptName);    // e.g., /mes
$baseUrl    = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim($scriptDir, '/');

// === AUTOLOAD CLASSES ===
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

// === PARSE REQUEST URI ===
$fullUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base URL from the request URI
if ($baseUrl !== '' && strpos($fullUri, $baseUrl) === 0) {
    $uri = substr($fullUri, strlen($baseUrl));
} else {
    $uri = $fullUri;
}

// Normalize URI
$uri = '/' . ltrim($uri, '/');
$uri = rtrim($uri, '/') ?: '/';

$method = $_SERVER['REQUEST_METHOD'];

// === LOAD ROUTES ===
$routes = require $baseDir . '/app/routes.php';

// === ROUTE MATCHING ===
$matched = false;
foreach ($routes as $routePattern => $routeHandler) {

    $parts = explode(' ', $routePattern, 2);
    $routeMethod = $parts[0] ?? '';
    $routePath   = $parts[1] ?? '';

    if ($routeMethod !== $method) continue;

    // Convert :num to (\d+)
    $regex = preg_quote($routePath, '/');
    $regex = preg_replace('/\\\(:num)/', '(\d+)', $regex);
    // Allow optional trailing slash
    $regex = '/^' . $regex . '\/?$/i';

    if (preg_match($regex, $uri, $matches)) {
        array_shift($matches); // Remove full match

        if (is_array($routeHandler)) {
            $controllerName = $routeHandler[0] ?? null;
            $action = $routeHandler[1] ?? $routeHandler['action'] ?? null;

            if ($controllerName && $action && class_exists($controllerName) && method_exists($controllerName, $action)) {
                $controller = new $controllerName();
                // Convert numeric parameters to int
                if (!empty($matches)) {
                    $matches = array_map('intval', $matches);
                    call_user_func_array([$controller, $action], $matches);
                } else {
                    $controller->$action();
                }
                $matched = true;
                break;
            }
        }
    }
}

// === NO ROUTE MATCHED ===
if (!$matched) {
    http_response_code(404);
    echo "<p>The requested URL '$fullUri' was not found on this server.</p>";
    exit;
}
