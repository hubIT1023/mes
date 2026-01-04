<?php
// tbpm_scheduler.php
// CLI script for TBPM (Time-Based Preventive Maintenance)

require_once __DIR__ . '/../config/bootstrap.php'; // ensure PDO and environment loaded

$tenantMiddleware = new TenantMiddleware();
$tenants = $tenantMiddleware->getAllTenants();

$assetMaintenanceModel = new AssetMaintenanceModel();
$routineWorkOrderModel = new RoutineWorkOrderModel();

logMessage("TBPM Scheduler started.");

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
