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

    public function handleChangeState(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        session_start();

        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=Unauthorized");
            exit;
        }

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security validation failed.";
            header("Location: /dashboard_admin");
            exit;
        }

        $data = [
            'org_id'        => (int) $_SESSION['tenant_id'],
            'group_code'    => (int) ($_POST['group_code'] ?? 0),
            'location_code' => (int) ($_POST['location_code'] ?? 0),
            'col_1'         => trim($_POST['col_1'] ?? ''), // asset_id
            'col_2'         => trim($_POST['col_2'] ?? ''), // entity
            'col_3'         => trim($_POST['col_3'] ?? ''), // stop cause
            'col_4'         => trim($_POST['col_4'] ?? ''), // issue
            'col_5'         => trim($_POST['col_5'] ?? ''), // action
            'col_6'         => trim($_POST['col_6'] ?? ''), // timestamp
            'col_8'         => trim($_POST['col_8'] ?? ''), // posted by
        ];

        if (
            empty($data['col_1']) ||
            empty($data['col_3']) ||
            empty($data['col_8'])
        ) {
            $_SESSION['error'] = "Required fields missing.";
            header("Location: /dashboard_admin");
            exit;
        }

        $this->model->updateToolState($data);

        $_SESSION['success'] = "✅ Tool state updated successfully.";
        header("Location: /dashboard_admin");
        exit;
    }
}
