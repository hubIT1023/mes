<?php
// app/controllers/ToolStateController.php

require_once __DIR__ . '/../models/ToolStateModel.php';

class ToolStateController
{
    private ToolStateModel $model;

    public function __construct()
    {
        $this->model = new ToolStateModel();
    }

    public function handleChangeState()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        //session_start();

        // Authentication
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=Unauthorized");
            exit;
        }

        // CSRF Protection
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security validation failed.";
            header("Location: /dashboard_admin");
            exit;
        }

        // Build data from POST (only fields sent by form)
        $data = [
            'org_id'        => (int)$_SESSION['tenant_id'], // enforce tenant isolation
            'group_code'    => (int)($_POST['group_code'] ?? 0),
            'location_code' => (int)($_POST['location_code'] ?? 0),
            'col_1'         => trim($_POST['col_1'] ?? ''),
            'col_2'         => trim($_POST['col_2'] ?? ''),
            'col_3'         => trim($_POST['col_3'] ?? ''),
            'col_4'         => trim($_POST['col_4'] ?? ''),
            'col_5'         => trim($_POST['col_5'] ?? ''),
            'col_6'         => trim($_POST['col_6'] ?? ''),
            'col_8'         => trim($_POST['col_8'] ?? ''),
        ];

        // Validate required fields (as per your form)
        $required = ['col_1', 'col_3', 'col_4', 'col_5', 'col_8'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $_SESSION['error'] = "Required field missing: " . htmlspecialchars($field);
                header("Location: /dashboard_admin");
                exit;
            }
        }

        // ✅ ONLY ACTION: Update the tool_state table
        $this->model->updateToolState($data);

        $_SESSION['success'] = "✅ Tool state updated successfully.";
        header("Location: /dashboard_admin");
        exit;
    }
}