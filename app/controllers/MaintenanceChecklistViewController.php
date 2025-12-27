<?php
// app/controllers/MaintenanceChecklistViewController.php

require_once __DIR__ . '/../models/MaintenanceChecklistViewModel.php';

class MaintenanceChecklistViewController
{
    public function show()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 🔐 Authentication: ensure user is logged in
        if (!isset($_SESSION['tenant']) || !is_array($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        // 🆔 Extract tenant UUID from session
        $sessionTenantId = $_SESSION['tenant']['org_id'] ?? null;
        if (!$sessionTenantId) {
            $_SESSION['flash_error'] = 'Invalid session: missing organization ID.';
            header("Location: /mes/dashboard_upcoming_maint");
            exit;
        }

        // 🆔 Validate checklist ID from URL
        $checklistId = $_GET['id'] ?? null;
        if (!$checklistId) {
            $_SESSION['flash_error'] = 'Missing checklist ID.';
            header("Location: /mes/dashboard_upcoming_maint");
            exit;
        }

        // 📂 Fetch data from model
        $model = new MaintenanceChecklistViewModel();
        $data = $model->getChecklistById($checklistId);

        if (empty($data)) {
            $_SESSION['flash_error'] = 'Checklist not found.';
            header("Location: /mes/dashboard_upcoming_maint");
            exit;
        }

        // 🔒 Authorization: verify ownership
        if ($data[0]['tenant_id'] !== $sessionTenantId) {
            $_SESSION['flash_error'] = 'Access denied.';
            header("Location: /mes/dashboard_upcoming_maint");
            exit;
        }

        // 🧾 Prepare data for view
        $checklist = $data[0];
        $tasks = array_filter($data, fn($row) => !empty($row['task_id']));

        // 👁️ Render HTML view
        require __DIR__ . '/../views/forms_mms/maintenance_checklist.php';
    }
}