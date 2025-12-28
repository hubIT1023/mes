<?php
// Front Controller

phpinfo();
/*
// Load logger helper
require_once __DIR__ . '/app/helpers/logger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseDir = __DIR__;
$baseUrl = '/mes';

// Autoload controllers, models, config files
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

// Parse URL and method
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace($baseUrl, '', $uri);
$uri = rtrim($uri, '/') ?: '/';

// Load routes
$routes = require $baseDir . '/app/routes.php';

// Enhanced route matching - handles both formats
foreach ($routes as $routePattern => $routeHandler) {
    // Parse route pattern 
    $routeParts = explode(' ', $routePattern, 2);
    $routeMethod = $routeParts[0] ?? '';
    $routePath = $routeParts[1] ?? '';
    
    // Skip if HTTP method doesn't match
    if ($routeMethod !== $method) {
        continue;
    }
    
    // Convert route pattern to regex for parameter matching
    $regexPattern = preg_quote($routePath, '/');
    $regexPattern = preg_replace('/\\\(:num)/', '(\d+)', $regexPattern);
    $regexPattern = '/^' . $regexPattern . '$/i';
    
    if (preg_match($regexPattern, $uri, $matches)) {
        array_shift($matches);
        
        if (is_array($routeHandler)) {
            $controllerName = $routeHandler[0] ?? null;
            $action = null;
            
            // Handle both formats: ['Controller', 'method'] and ['Controller', 'action' => 'method']
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


echo "<p>The requested URL '$uri' was not found on this server.</p>";

*/
/*
echo "<p>Available routes:</p><ul>";
foreach (array_keys($routes) as $route) {
    echo "<li>$route</li>";
}
*/
