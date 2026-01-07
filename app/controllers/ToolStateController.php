<?php
// app/controllers/ToolStateController.php

require_once __DIR__ . '/../models/ToolStateModel.php';

class ToolStateController {
    public function handleChangeState() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=Unauthorized");
            exit;
        }

        // Verify CSRF
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security validation failed.";
            header("Location: /dashboard_admin");
            exit;
        }

        // Handle custom stop cause
        $stopCause = $_POST['col_3'] ?? '';
        if ($stopCause === 'CUSTOM') {
            $stopCause = trim($_POST['ts_customInput'] ?? '');
        }

        // Sanitize input
        $data = [
            'org_id' => $_SESSION['tenant_id'],
            'group_code' => (int)($_POST['group_code'] ?? 0),
            'location_code' => (int)($_POST['location_code'] ?? 0),
            'col_1' => trim($_POST['col_1'] ?? ''), // asset_id
            'col_2' => trim($_POST['col_2'] ?? ''), // entity name
            'col_3' => $stopCause,
            'col_4' => trim($_POST['col_4'] ?? ''),
            'col_5' => trim($_POST['col_5'] ?? ''),
            'col_8' => trim($_POST['col_8'] ?? ''),
        ];

        // Validate required fields
        if (empty($data['col_1']) || empty($data['col_2']) || empty($data['col_8'])) {
            $_SESSION['error'] = "Required fields missing.";
            header("Location: /dashboard_admin");
            exit;
        }

        $model = new ToolStateModel();
        $result = $model->saveToolState($data);

        if ($result['success']) {
            $_SESSION['success'] = "State updated successfully!";
        } else {
            $_SESSION['error'] = $result['error'];
        }

        header("Location: /mes/dashboard_admin");
        exit;
    }
}