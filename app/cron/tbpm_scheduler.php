<?php
// app/cron/tbpm_scheduler.php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../models/AssetMaintenanceModel.php';
require_once __DIR__ . '/../models/RoutineWorkOrderModel.php';

// -----------------------------------------
// Logging helper
// -----------------------------------------
function logMessage($msg) {
    $logFile = __DIR__ . '/../storage/cron_logs/tbpm_cron.log';
    $date = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$date] $msg\n", FILE_APPEND);
}

// Initialize middleware/models
$tenantMiddleware = new TenantMiddleware();
$assetMaintenanceModel = new AssetMaintenanceModel();
$routineWorkOrderModel = new RoutineWorkOrderModel();

logMessage("=== TBPM Scheduler Started ===");

foreach ($tenants as $tenant) {
    $tenantId = $tenant['org_id'];
    logMessage("Processing tenant: $tenantId");

    $dueTasks = $assetMaintenanceModel->getDueMaintenance($tenantId);
    $configs  = $tenantMiddleware->getMaintenanceConfig($tenantId);

    foreach ($dueTasks as $task) {
        $type = strtolower($task['maintenance_type']);
        $conf = $configs[$type] ?? null;

        if (!$conf || !$conf['enabled']) {
            logMessage("  Skipping disabled maintenance type: $type");
            continue;
        }

        // Calculate next date based on config
        $nextDate = date('Y-m-d', strtotime("+{$conf['interval_days']} days", strtotime($task['maintenance_date'])));

        // Override technician if configured
        if (!empty($conf['technician_name'])) {
            $task['technician_name'] = $conf['technician_name'];
        }

        // Create work order
        $routineWorkOrderModel->createFromMaintenance($task);
        $assetMaintenanceModel->updateNextMaintenanceDate($task['id'], $nextDate);
        $assetMaintenanceModel->markAsProcessed($task['id']);

        logMessage("    Work order created for asset: {$task['asset_id']}, next maintenance: $nextDate");
    }
}

logMessage("TBPM Scheduler finished.");
