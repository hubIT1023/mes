<?php
// app/controllers/AssetMaintenanceController.php

require_once __DIR__ . '/../models/AssetMaintenanceModel.php';

class AssetMaintenanceController
{
    private $model;

    public function __construct()
    {
        $this->model = new AssetMaintenanceModel();
    }

    /**
     * Display the Add Maintenance Form
     */
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Ensure logged in
        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        // Render form
        require __DIR__ . '/../views/forms_mms/addMaintenance.php';
    }

    /**
     * Handle Maintenance Record Submission (POST)
     */
    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /mes/form_mms/addMaintenance");
            exit;
        }

        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $error = "Invalid CSRF token.";
            require __DIR__ . '/../views/forms_mms/addMaintenance.php';
            return;
        }

        // Tenant context
        $tenant = $_SESSION['tenant'] ?? null;
        if (!$tenant || empty($tenant['org_id'])) {
            $error = "Session expired. Please sign in again.";
            require __DIR__ . '/../views/forms_mms/addMaintenance.php';
            return;
        }

        // Gather form data
        $data = [
            'tenant_id'             => $tenant['org_id'],
            'asset_id'              => trim($_POST['asset_id']),
            'maintenance_type'      => trim($_POST['maintenance_type']),
            'maintenance_date'      => trim($_POST['maintenance_date']),
            'technician_name'       => trim($_POST['technician_name'] ?? ''),
            'work_order'            => trim($_POST['work_order'] ?? ''),
            'description'           => trim($_POST['description'] ?? ''),
            'next_maintenance_date' => trim($_POST['next_maintenance_date'] ?? ''),
            'status'                => trim($_POST['status'])
        ];

        try {
            $inserted = $this->model->insertMaintenance($data);
            if ($inserted) {
                $success = "Maintenance record successfully added!";
            } else {
                $error = "Failed to add maintenance record.";
            }
        } catch (Exception $e) {
            $error = "Error: " . htmlspecialchars($e->getMessage());
        }

        require __DIR__ . '/../views/forms_mms/addMaintenance.php';
    }
}
