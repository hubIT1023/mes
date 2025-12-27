<?php
// app/controllers/MaintenanceChecklistUpdateController.php

require_once __DIR__ . '/../models/MaintenanceChecklistUpdateModel.php';

class MaintenanceChecklistUpdateController
{
    private $model;

    public function __construct()
    {
        $this->model = new MaintenanceChecklistUpdateModel();
    }

    /**
     * Display checklist form for editing
     */
    public function edit()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        $checklistId = $_GET['id'] ?? null;
        if (!$checklistId) {
            http_response_code(400);
            echo "Missing checklist ID";
            exit;
        }

        try {
            $data = $this->model->getChecklistById($checklistId);
            if (!$data) {
                http_response_code(404);
                echo "Checklist not found";
                exit;
            }

            $checklist = $data[0];
            $tasks = [];
            foreach ($data as $row) {
                if ($row['task_id']) $tasks[] = $row;
            }

            require __DIR__ . '/../views/forms_mms/maintenance_checklist.php';

        } catch (Exception $e) {
            error_log("Checklist load error: " . $e->getMessage());
            http_response_code(500);
            echo "Failed to load checklist";
        }
    }

    /**
     * Handle save or complete checklist
     */
    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method Not Allowed";
            exit;
        }

        $action = $_POST['action'] ?? 'save';
        $mc = $_POST['mc'] ?? [];
        $mct = $_POST['mct'] ?? [];

        $checklistId = $mc['id'] ?? null;
        $tenantId = $mc['tenant_id'] ?? null;
        $technician = $mc['technician'] ?? null;
        $summaryNotes = $_POST['summary_notes'] ?? null;

        if (!$checklistId || !$tenantId || !$technician) {
            header("Location: /mes/form_mms/checklist_edit?id={$checklistId}&error=Missing required fields");
            exit;
        }

        try {
            if ($action === 'save') {
                $this->model->saveChecklist($checklistId, $tenantId, $technician, $mct, $summaryNotes);
                header("Location: /mes/form_mms/checklist_edit?id={$checklistId}&success=Checklist+saved");
            } elseif ($action === 'complete') {
                $this->model->saveChecklist($checklistId, $tenantId, $technician, $mct, $summaryNotes);
                $this->model->completeChecklist($checklistId, $tenantId);
                
                // ✅ ADD ARCHIVING HERE
                $this->model->archiveCompletedChecklist($checklistId, $tenantId, $technician);
                
                header("Location: /mes/form_mms/checklist_edit?id={$checklistId}&success=Checklist+completed+and+archived");
            } else {
                header("Location: /mes/form_mms/checklist_edit?id={$checklistId}&error=Unknown action");
            }
            exit;
        } catch (Exception $e) {
            error_log("Checklist update error: " . $e->getMessage());
            header("Location: /mes/form_mms/checklist_edit?id={$checklistId}&error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}
