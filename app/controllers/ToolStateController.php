<?php
// app/controllers/ToolStateController.php

require_once __DIR__ . '/../models/ToolStateModel.php';

class ToolStateController {
    public function handleChangeState() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }

    // Enable debug mode only during development (e.g., ?debug=1)
    $debug = ($_GET['debug'] ?? null) === '1';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['tenant_id'])) {
        if ($debug) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: tenant_id missing in session'], JSON_PRETTY_PRINT);
            exit;
        }
        header("Location: /mes/signin?error=Unauthorized");
        exit;
    }

    // Verify CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        if ($debug) {
            echo json_encode([
                'error' => 'CSRF validation failed',
                'session_token' => isset($_SESSION['csrf_token']) ? 'exists' : 'missing',
                'post_token' => isset($_POST['csrf_token']) ? 'exists' : 'missing'
            ], JSON_PRETTY_PRINT);
            exit;
        }
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
        'org_id'        => $_SESSION['tenant_id'],
        'group_code'    => (int)($_POST['group_code'] ?? 0),
        'location_code' => (int)($_POST['location_code'] ?? 0),
        'col_1'         => trim($_POST['col_1'] ?? ''), // asset_id
        'col_2'         => trim($_POST['col_2'] ?? ''), // entity name
        'col_3'         => $stopCause,
        'col_4'         => trim($_POST['col_4'] ?? ''),
        'col_5'         => trim($_POST['col_5'] ?? ''),
        'col_8'         => trim($_POST['col_8'] ?? ''),
    ];

    // Identify missing required fields
    $required = ['col_1', 'col_2', 'col_8'];
    $missing = [];

    foreach ($required as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        if ($debug) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Required fields missing',
                'missing_fields' => $missing,
                'received_data' => array_filter($data, fn($v) => !is_int($v) || $v !== 0), // hide zero ints
                'POST_keys' => array_keys($_POST)
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $_SESSION['error'] = "Required fields missing.";
        header("Location: /mes/dashboard_admin");
        exit;
    }

    $model = new ToolStateModel();
    $result = $model->saveToolState($data);

    if ($result['success']) {
        if ($debug) {
            echo json_encode(['success' => true, 'message' => 'State updated successfully'], JSON_PRETTY_PRINT);
            exit;
        }
        $_SESSION['success'] = "State updated successfully!";
    } else {
        if ($debug) {
            http_response_code(500);
            echo json_encode($result, JSON_PRETTY_PRINT);
            exit;
        }
        $_SESSION['error'] = $result['error'];
    }

    if (!$debug) {
        header("Location: /mes/dashboard_admin");
        exit;
    }
}
}