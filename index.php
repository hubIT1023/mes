<?php
// index.php -- Front Controller

require_once __DIR__ . '/app/helpers/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseDir = __DIR__;
$baseUrl = '/mes';

// -------------------------------------------------
// Autoloader
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
});

// -------------------------------------------------
// Parse URL & enforce base path (HARDENING)
// -------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'];
$rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize `/mes` → `/mes/`
if ($rawUri === $baseUrl) {
    $rawUri .= '/';
}

// Reject requests outside /mes
if (strpos($rawUri, $baseUrl) !== 0) {
    http_response_code(404);
    exit('Invalid base path');
}

// Strip base URL
$uri = substr($rawUri, strlen($baseUrl));
$uri = rtrim($uri, '/') ?: '/';

// -------------------------------------------------
// Load routes
// -------------------------------------------------
$routes = require $baseDir . '/app/routes.php';

// -------------------------------------------------
// Route matching
// -------------------------------------------------
foreach ($routes as $routePattern => $routeHandler) {
    [$routeMethod, $routePath] = explode(' ', $routePattern, 2);

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

    if (!is_array($routeHandler)) {
        continue;
    }

    $controllerName = $routeHandler[0] ?? null;
    $action = $routeHandler[1] ?? ($routeHandler['action'] ?? null);

    if (
        !$controllerName ||
        !$action ||
        !class_exists($controllerName) ||
        !method_exists($controllerName, $action)
    ) {
        continue;
    }

    $controller = new $controllerName();

    if ($matches) {
        call_user_func_array([$controller, $action], array_map('intval', $matches));
    } else {
        $controller->$action();
    }
    exit;
}

// -------------------------------------------------
// 404 fallback
// -------------------------------------------------
http_response_code(404);
echo "<p>The requested URL '$uri' was not found.</p>";
