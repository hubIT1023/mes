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

        header('Content-Type: application/json');

        try {
            $model = new ToolStateModel();

            // 🔥 STEP 1: Always update current state (handles insert/update)
            $model->updateToolState($data);

            // 🔥 STEP 2: If switching TO PROD or PRODUCTION, save completed event to history (Maintenance Logs)
            $stopCauseUpper = strtoupper(trim($data['col_3']));
            $isProdMode = ($stopCauseUpper === 'PROD' || $stopCauseUpper === 'PRODUCTION' || $stopCause === 'PROD' || $stopCause === 'PRODUCTION');
            
            $savedToLog = false;
            if ($isProdMode) {
                $model->saveToMachineLog($data);
                $savedToLog = true;
            }

            echo json_encode([
                'success' => true,
                'message' => '✅ State updated successfully!',
                'data' => $data,
                'is_prod_mode' => $isProdMode,
                'saved_to_log' => $savedToLog
            ], JSON_PRETTY_PRINT);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ], JSON_PRETTY_PRINT);
            exit;
        }
    }
}
