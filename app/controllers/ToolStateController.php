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

        // Build base data
        $data = [
            'org_id'        => (int)$_SESSION['tenant_id'],
            'group_code'    => (int)($_POST['group_code'] ?? 0),
            'location_code' => (int)($_POST['location_code'] ?? 0),
            'col_1'         => trim($_POST['col_1']),
            'col_2'         => trim($_POST['col_2'] ?? ''),
            'col_3'         => trim($_POST['col_3']),
            'col_4'         => trim($_POST['col_4']),
            'col_5'         => trim($_POST['col_5']),
            'col_6'         => trim($_POST['col_6']),
            'col_8_new'     => trim($_POST['col_8']), // person from form
        ];

        if (empty($data['col_1']) || empty($data['col_3']) || empty($data['col_8_new'])) {
            $_SESSION['error'] = "Required fields missing.";
            header("Location: /dashboard_admin");
            exit;
        }

        if ($data['col_3'] !== 'PROD') {
            // NON-PROD: col_8 = reporter
            $data['col_8'] = $data['col_8_new'];
            $this->model->updateToolState($data);
            $this->model->setDowntimeStart($data);
        } else {
            // PROD: need to fetch current col_8 (reporter) first
            $currentReporter = $this->model->getCurrentPersonReported($data['org_id'], $data['col_1']);
            $data['col_8_old'] = $currentReporter;      // will go to col_9
            $data['col_8']     = $data['col_8_new'];   // new resolver in col_8

            $this->model->updateToolStateForPROD($data);
            $this->model->saveHistoryToMachineLog($data);
        }

        $_SESSION['success'] = "✅ Tool state updated successfully.";
        header("Location: /dashboard_admin");
        exit;
    }
}