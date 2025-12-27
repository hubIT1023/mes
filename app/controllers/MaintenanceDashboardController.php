<?php
// app/controllers/MaintenanceDashboardController.php

require_once __DIR__ . '/../models/RoutineWorkOrderModel.php';

class MaintenanceDashboardController {
    private $model;

    public function __construct() {
        $this->model = new RoutineWorkOrderModel();
    }

    public function upcoming() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        $tenantId = $_SESSION['tenant']['org_id'];

        // Capture filter parameters from GET
        $filters = [
            'asset_id'         => $_GET['asset_id'] ?? '',
            'asset_name'       => $_GET['asset_name'] ?? '',
            'work_order_ref'   => $_GET['work_order_ref'] ?? '',
            'maintenance_type' => $_GET['maintenance_type'] ?? '',
            'technician_name'  => $_GET['technician_name'] ?? ''
        ];

        // Fetch filtered data + distinct dropdown lists
        $assets     = $this->model->getUpcomingMaintenance($tenantId, $filters);
        $filterData = $this->model->getFilterOptions($tenantId);

        // Include your PHP dashboard view
        include __DIR__ . '/../views/dashboard_upcoming_maint.php';
    }
}
