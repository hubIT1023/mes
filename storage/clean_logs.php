<?php
// Delete logs older than 30 days
$logDir = __DIR__ . '/error_logs';
$files = glob($logDir . '/error-*.log');
foreach ($files as $file) {
    if (filemtime($file) < strtotime('-30 days')) {
        unlink($file);
    }
}