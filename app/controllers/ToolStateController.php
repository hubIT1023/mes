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

        // CSRF protection
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security validation failed.";
            header("Location: /dashboard_admin");
            exit;
        }

        // Handle custom stop cause (if enabled)
        $stopCause = $_POST['col_3'] ?? '';
        if ($stopCause === 'CUSTOM') {
            $stopCause = trim($_POST['ts_customInput'] ?? '');
        }

        // Generate high-precision timestamp
        $timestamp = date('Y-m-d H:i:s.u'); // e.g., "2025-04-05 10:30:00.123456"

        // Build data array
        $data = [
            'org_id' => $_SESSION['tenant_id'],
            'group_code' => (int)($_POST['group_code'] ?? 0),
            'location_code' => (int)($_POST['location_code'] ?? 0),
            'col_1' => trim($_POST['col_1'] ?? ''), // asset_id
            'col_2' => trim($_POST['col_2'] ?? ''), // entity name
            'col_3' => $stopCause,
            'col_4' => trim($_POST['col_4'] ?? ''),
            'col_5' => trim($_POST['col_5'] ?? ''),
            'col_6' => $timestamp, // ⚠️ High-precision timestamp
            'col_8' => trim($_POST['col_8'] ?? ''),
        ];

        // Validate required fields
        if (empty($data['col_1']) || empty($data['col_2']) || empty($data['col_8'])) {
            $_SESSION['error'] = "Required fields missing.";
            header("Location: /dashboard_admin");
            exit;
        }

        try {
            $model = new ToolStateModel();

            // 🔥 STEP 1: Always update current state (handles insert/update)
            $model->updateToolState($data);

            // 🔥 STEP 2: If switching TO PROD, save completed event to history
            if ($data['col_3'] === 'PROD') {
                $model->saveToMachineLog($data);
            }

            $_SESSION['success'] = "✅ State updated successfully!";
        } catch (Exception $e) {
            error_log("ToolState update error: " . $e->getMessage());
            $_SESSION['error'] = "❌ Failed to update state. Please try again.";
        }

        header("Location: /mes/dashboard_admin");
        exit;
    }
}
