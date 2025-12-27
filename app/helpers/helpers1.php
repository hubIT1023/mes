<?php

if (!function_exists('base_url')) {
    function base_url($path = '') {
        $app = require __DIR__ . '/config/app.php';
        $base = rtrim($app['baseUrl'], '/');
        $path = ltrim($path, '/');
        return $base . ($path ? '/' . $path : '');
    }
}

if (!function_exists('url')) {
    function url($path = '') {
        return base_url($path);
    }
}

if (!function_exists('route')) {
    function route($name) {
        static $routes;

        if (!$routes) {
            $routes = require __DIR__ . '/routes.php';
        }

        foreach ($routes as $routeKey => $routeData) {
            if (($routeData['name'] ?? null) === $name) {
                $uri = explode(' ', $routeKey)[1]; // Extract URI part
                return base_url(ltrim($uri, '/'));
            }
        }

        // If no matching route found
        return '#';
    }
}