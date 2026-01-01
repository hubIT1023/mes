<?php
// app/controllers/ChecklistController.php

require_once __DIR__ . '/../models/ChecklistModel.php';

class ChecklistController {
    private $model;

    public function __construct() {
        $this->model = new ChecklistModel();
    }

    /**
     * Show checklist list with filters
     */
    public function index() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in");
            exit;
        }

        $tenantId = $_SESSION['tenant']['org_id'];

        $filters = [
            'maintenance_type' => $_GET['maintenance_type'] ?? '',
            'checklist_id'     => $_GET['checklist_id'] ?? ''
        ];

        $checklists = $this->model->getAllChecklists($tenantId, $filters);

        $grouped = [];
        foreach ($checklists as $row) {
            $id = $row['checklist_id'];
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    "header" => $row,
                    "tasks"  => []
                ];
            }
            if (!empty($row['task_id'])) {
                $grouped[$id]['tasks'][] = $row;
            }
        }

        include __DIR__ . '/../views/forms_mms/checklist_lists.php';
    }

    /**
     * Show create form
     */
    public function create() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in");
            exit;
        }

        include __DIR__ . '/../views/forms_mms/checklist_template.php';
    }

    /**
     * Handle new checklist creation
     */
    public function store() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in");
            exit;
        }

        $tenantId = $_SESSION['tenant']['org_id'];

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Invalid submission token.";
            $_SESSION['old'] = $_POST;
            header("Location: /mes/form_mms/checklist_template");
            exit;
        }

        $checklistId     = trim($_POST['checklist_id'] ?? '');
        $workOrder       = trim($_POST['work_order'] ?? '');
        $maintenanceType = trim($_POST['maintenance_type'] ?? '');
        $intervalDays    = (int)($_POST['interval_days'] ?? 30);
        $description     = trim($_POST['description'] ?? '');
        $taskTexts       = $_POST['task_text'] ?? [];

        if (empty($checklistId) || empty($workOrder)) {
            $_SESSION['error'] = "Task No. and Work Order are required.";
            $_SESSION['old'] = $_POST;
            header("Location: /mes/form_mms/checklist_template");
            exit;
        }

        if (empty($taskTexts) || count(array_filter(array_map('trim', $taskTexts))) === 0) {
            $_SESSION['error'] = "At least one task is required.";
            $_SESSION['old'] = $_POST;
            header("Location: /mes/form_mms/checklist_template");
            exit;
        }

        if ($this->model->checklistIdExists($tenantId, $checklistId)) {
            $_SESSION['error'] = "A checklist with Task No. '$checklistId' already exists for your organization.";
            $_SESSION['old'] = $_POST;
            header("Location: /mes/form_mms/checklist_template");
            exit;
        }

        try {
            $success = $this->model->createChecklist($tenantId, [
                'checklist_id'     => $checklistId,
                'work_order'       => $workOrder,
                'maintenance_type' => $maintenanceType,
                'interval_days'    => $intervalDays,
                'description'      => $description,
                'tasks'            => $taskTexts
            ]);

            if ($success) {
                $_SESSION['success'] = "✅ Checklist template created successfully!";
                header("Location: /mes/checklist/manage");
            } else {
                throw new Exception("Creation failed.");
            }
        } catch (Exception $e) {
            error_log("Checklist creation failed: " . $e->getMessage());
            $_SESSION['error'] = "❌ Error creating checklist template. Please try again.";
            $_SESSION['old'] = $_POST;
            header("Location: /mes/form_mms/checklist_template");
        }
        exit;
    }

    /**
     * Show edit form
     */
    public function edit() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in");
            exit;
        }

        $tenantId = $_SESSION['tenant']['org_id'];
        $checklistId = $_GET['checklist_id'] ?? null;

        if (!$checklistId) {
            $_SESSION['error'] = "Checklist ID is missing.";
            header("Location: /mes/form_mms/checklists");
            exit;
        }

        $checklist = $this->model->getChecklistById($tenantId, $checklistId);

        if (!$checklist) {
            $_SESSION['error'] = "Checklist not found.";
            header("Location: /mes/form_mms/checklists");
            exit;
        }

        include __DIR__ . '/../views/forms_mms/checklist_edit.php';
    }

    /**
     * Update existing checklist
     */
    public function update() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in");
            exit;
        }

        $tenantId = $_SESSION['tenant']['org_id'];

        $checklistId     = $_POST['checklist_id'] ?? null;
        $maintenanceType = $_POST['maintenance_type'] ?? null;
        $intervalDays    = $_POST['interval_days'] ?? null;

        $taskIds   = $_POST['task_id'] ?? [];
        $taskTexts = $_POST['task_text'] ?? [];
        $taskOrder = $_POST['task_order'] ?? [];

        if (!$checklistId) {
            $_SESSION['error'] = "Missing checklist ID.";
            header("Location: /mes/form_mms/checklists");
            exit;
        }

        $updated = $this->model->updateChecklist($tenantId, $checklistId, [
            'maintenance_type' => $maintenanceType,
            'interval_days'    => $intervalDays,
            'tasks'            => [
                'task_id'   => $taskIds,
                'task_text' => $taskTexts,
                'task_order' => $taskOrder
            ]
        ]);

        if ($updated) {
            $_SESSION['success'] = "Checklist successfully updated!";
        } else {
            $_SESSION['error'] = "No changes were made or update failed.";
        }

        header("Location: /mes/form_mms/checklists");
        exit;
    }
}