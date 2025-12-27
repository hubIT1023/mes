<?php
// app/helpers/logger.php

if (!function_exists('log_error')) {
    /**
     * Log error message to a daily rotating log file
     */
    function log_error(string $message, string $context = 'general')
    {
        $logDir = __DIR__ . '/../../storage/error_logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/error-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $url = $_SERVER['REQUEST_URI'] ?? 'CLI';

        $logMessage = sprintf(
            "[%s] [%s] IP: %s | URL: %s | ERROR: %s\n",
            $timestamp,
            strtoupper($context),
            $ip,
            $url,
            $message
        );

        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}