<?php
// app/controllers/ChecklistTemplateController.php
require_once __DIR__ . '/../models/ChecklistTemplateModel.php';

class ChecklistTemplateController {

    private $model;

    public function __construct() {
        $this->model = new ChecklistTemplateModel();
    }

    /**
     * Show the create form
     */
    public function create() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $action = 'create';
        include __DIR__ . '/../views/forms_mms/checklist_template.php';
    }

    /**
     * Handle checklist creation (POST)
     */
    public function store() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $tenant = $_SESSION['tenant'] ?? null;
        $tenantId = $tenant['org_id'] ?? null;

        if (!$tenantId) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        // ✅ Validate CSRF
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['csrf_token']) &&
            hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            // ✅ Validate required fields
            if (empty($_POST['checklist_id']) || empty($_POST['work_order'])) {
                $_SESSION['error'] = "Task No. and Work Order are required.";
                header("Location: /mes/form_mms/checklist_template");
                exit;
            }

            // ✅ Attempt to insert into DB
            if ($this->model->createTemplate($tenantId, $_POST)) {
                $_SESSION['success'] = "✅ Checklist Template created successfully.";

                // 🔁 Redirect back to the same form for new entry
                header("Location: /mes/form_mms/checklist_template");
                exit;
            } else {
                $_SESSION['error'] = "❌ Error creating checklist template. Please try again.";
            }
        } else {
            $_SESSION['error'] = "⚠️ Invalid request or expired CSRF token.";
        }

        // Fallback redirect (stays on form)
        header("Location: /mes/form_mms/checklist_template");
        exit;
    }

    /**
     * Show edit form
     */
    public function edit($checklistId) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $tenant = $_SESSION['tenant'] ?? null;
        $tenantId = $tenant['org_id'] ?? null;

        if (!$tenantId) {
            header("Location: /mes/signin");
            exit;
        }

        $template = $this->model->getTemplate($tenantId, $checklistId);
        if (!$template) {
            $_SESSION['error'] = "Checklist not found.";
            header("Location: /mes/form_mms/checklist_template");
            exit;
        }

        $action = 'edit';
        include __DIR__ . '/../views/forms_mms/checklist_template.php';
    }

    /**
     * Handle checklist update (POST)
     */
    public function update() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $tenant = $_SESSION['tenant'] ?? null;
        $tenantId = $tenant['org_id'] ?? null;

        if (!$tenantId) {
            header("Location: /mes/signin");
            exit;
        }

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['csrf_token']) &&
            hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            // ✅ Validate required fields
            if (empty($_POST['checklist_id']) || empty($_POST['work_order'])) {
                $_SESSION['error'] = "Task No. and Work Order are required.";
                header("Location: /mes/form_mms/checklist_template");
                exit;
            }

            if ($this->model->updateTemplate($tenantId, $_POST)) {
                $_SESSION['success'] = "✅ Checklist Template updated successfully.";
            } else {
                $_SESSION['error'] = "❌ Failed to update checklist template.";
            }
        } else {
            $_SESSION['error'] = "⚠️ Invalid CSRF token or request.";
        }

        // Redirect back to the same edit page
        header("Location: /mes/form_mms/checklist_template");
        exit;
    }
}