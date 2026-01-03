<?php
// app/controllers/RoutineMaintenanceController.php

require_once __DIR__ . '/../models/RoutineMaintenanceModel.php';

class RoutineMaintenanceController {
    private $model;

    public function __construct() {
        $this->model = new RoutineMaintenanceModel();
    }

    public function generateForm() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $tenantId = $_SESSION['tenant']['org_id'] ?? null;

        if (!$tenantId) {
            header("Location: /mes/signin?error=Please login first");
            exit;
        }

        $tenantId = trim((string)$tenantId);
        error_log("📌 Normalized tenant_id: " . $tenantId);

        $filters = $this->model->getFilterOptions($tenantId);

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        include __DIR__ . '/../views/forms_mms/routine_maintenance.php';
    }

    /**
     * ✅ ENHANCED: Now returns detailed JSON on failure for debugging
     */
    public function generate() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $tenantId = $_SESSION['tenant']['org_id'] ?? null;

        if (!$tenantId) {
            // Debug output even on redirect
            error_log("❌ No tenant_id in session");
            header("Location: /mes/signin?error=Please login first");
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            error_log("❌ CSRF token mismatch");
            $_SESSION['error'] = "Invalid or expired submission token.";
            header("Location: /mes/form_mms/routine_maintenance");
            exit;
        }

        unset($_SESSION['csrf_token']);

        $workOrder = trim($_POST['work_order'] ?? '');
        $assetIds = $_POST['asset_ids'] ?? [];
        $technicianOverride = trim($_POST['technician'] ?? '');

        if (empty($assetIds)) {
            error_log("❌ No assets selected");
            $_SESSION['error'] = "Please select at least one asset.";
            header("Location: /mes/form_mms/routine_maintenance");
            exit;
        }

        if (empty($workOrder)) {
            error_log("❌ No work order selected");
            $_SESSION['error'] = "Please select a Work Order.";
            header("Location: /mes/form_mms/routine_maintenance");
            exit;
        }

        try {
            $filters = [
                'asset_ids' => $assetIds,
                'work_order' => $workOrder,
                'technician' => $technicianOverride ?: null
            ];

            error_log("🔍 Generating WOs for tenant: $tenantId, filters: " . json_encode($filters));

            $count = $this->model->generateRoutineWorkOrders($tenantId, $filters);

            if ($count > 0) {
                $_SESSION['success'] = "✅ $count routine work orders generated successfully!";
            } else {
                $_SESSION['error'] = "⚠️ No new work orders created (may already exist).";
            }

        } catch (Exception $e) {
            // ✅ ALWAYS log full error
            $errorMsg = "Routine Maintenance Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
            error_log($errorMsg);

            // ✅ Return JSON for debugging (even on regular POST)
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Failed to generate work orders',
                'details' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => [
                    'tenant_id' => $tenantId,
                    'work_order' => $workOrder,
                    'asset_ids' => $assetIds,
                    'technician' => $technicianOverride
                ]
            ], JSON_PRETTY_PRINT);
            exit; // ⚠️ Stop redirect to show JSON
        }

        header("Location: /mes/form_mms/routine_maintenance");
        exit;
    }

    public function getMaintenanceTypeByWorkOrder() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $tenantId = $_SESSION['tenant']['org_id'] ?? null;
        $workOrder = $_GET['work_order'] ?? '';

        error_log("AJAX: tenant = " . ($tenantId ?: 'MISSING'));
        error_log("AJAX: Requested work_order = " . $workOrder);

        header('Content-Type: application/json');

        if (!$tenantId || $workOrder === '') {
            echo json_encode(['maintenance_type' => '']);
            return;
        }

        try {
            $type = $this->model->getMaintenanceTypeByWorkOrder($tenantId, $workOrder);
            echo json_encode(['maintenance_type' => $type ?: '']);
        } catch (Exception $e) {
            error_log("AJAX Error: " . $e->getMessage());
            echo json_encode([
                'maintenance_type' => '',
                'error' => 'Failed to fetch maintenance type',
                'details' => $e->getMessage()
            ]);
        }
    }
}