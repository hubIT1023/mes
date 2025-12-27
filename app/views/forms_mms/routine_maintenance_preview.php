<?php
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

        $filters = $this->model->getFilterOptions($tenantId);
        include __DIR__ . '/../views/forms_mms/routine_maintenance.php';
    }

    public function preview() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $tenantId = $_SESSION['tenant']['org_id'] ?? null;

        $filters = [
            'asset_ids' => $_POST['asset_ids'] ?? [],
            'maintenance_type' => $_POST['maintenance_type'] ?? '',
            'technician' => $_POST['technician'] ?? ''
        ];

        $data = $this->model->getPreviewList($tenantId, $filters);
        include __DIR__ . '/../views/forms_mms/routine_maintenance_preview.php';
    }

    public function generate() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $tenantId = $_SESSION['tenant']['org_id'] ?? null;

        $filters = [
            'asset_ids' => $_POST['asset_ids'] ?? [],
            'maintenance_type' => $_POST['maintenance_type'] ?? '',
            'technician' => $_POST['technician'] ?? ''
        ];

        $count = $this->model->generateRoutineWorkOrders($tenantId, $filters);

        if ($count > 0) {
            $_SESSION['success'] = "✅ $count work orders generated successfully!";
        } else {
            $_SESSION['error'] = "⚠️ No matching data found for the selected filters.";
        }

        header("Location: /mes/dashboard_upcoming_maint");
        exit;
    }
}
