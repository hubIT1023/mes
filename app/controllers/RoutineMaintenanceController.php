<?php
// RoutineMaintenanceController.php

require_once __DIR__ . '/../models/RoutineMaintenanceModel.php';

class RoutineMaintenanceController {
    private $model;

    public function __construct() {
        $this->model = new RoutineMaintenanceModel();
    }

    /**
     * Show Routine Maintenance Form
     */
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
     * Handle Form Submission
     */
    public function generate() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $tenantId = $_SESSION['tenant']['org_id'] ?? null;

        if (!$tenantId) {
            header("Location: /mes/signin?error=Please login first");
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid or expired submission token.";
            header("Location: /mes/form_mms/routine_maintenance");
            exit;
        }

        unset($_SESSION['csrf_token']);

        $workOrder = trim($_POST['work_order'] ?? '');
        $assetIds = $_POST['asset_ids'] ?? [];
        $technicianOverride = trim($_POST['technician'] ?? '');

        if (empty($assetIds)) {
            $_SESSION['error'] = "Please select at least one asset.";
            header("Location: /mes/form_mms/routine_maintenance");
            exit;
        }

        if (empty($workOrder)) {
            $_SESSION['error'] = "Please select a Work Order.";
            header("Location: /mes/form_mms/routine_maintenance");
            exit;
        }

        try {
            // Prepare filters array
            $filters = [
                'asset_ids' => $assetIds,
                'work_order' => $workOrder,
                'technician' => $technicianOverride ?: null
            ];

            $count = $this->model->generateRoutineWorkOrders($tenantId, $filters);

            if ($count > 0) {
                $_SESSION['success'] = "✅ $count routine work orders generated successfully!";
            } else {
                $_SESSION['error'] = "⚠️ No new work orders created (may already exist).";
            }

        } catch (Exception $e) {
            error_log("Routine Maintenance Error: " . $e->getMessage());

            // For debugging or AJAX requests, return JSON error
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to generate work orders',
                    'details' => $e->getMessage()
                ]);
                exit;
            }

            $_SESSION['error'] = "Failed to generate work orders. Please try again.";
        }

        header("Location: /mes/form_mms/routine_maintenance");
        exit;
    }

    /**
     * AJAX: Get maintenance_type by work_order
     */
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

