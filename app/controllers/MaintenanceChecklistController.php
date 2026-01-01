<?php
// app/controllers/MaintenanceChecklistController.php

require_once __DIR__ . '/../models/MaintenanceChecklistModel.php';
require_once __DIR__ . '/../helpers/logger.php';

class MaintenanceChecklistController
{
    private $model;

    public function __construct()
    {
        $this->model = new MaintenanceChecklistModel();
    }

    /**
     * POST /maintenance_checklist/associate
     */
    public function associate()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant'])) {
            http_response_code(401);
			
            echo json_encode(['error' => 'Unauthorized']);
			
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
			
            echo json_encode(['error' => 'Method Not Allowed']);
			
            exit;
        }

        $tenant_id = trim($_POST['tenant_id'] ?? '');
        $asset_id = trim($_POST['asset_id'] ?? '');
        $checklist_id = trim($_POST['checklist_id'] ?? '');
        $work_order_ref = trim($_POST['work_order_ref'] ?? '');

        if (!$tenant_id || !$asset_id || !$checklist_id || !$work_order_ref) {
            http_response_code(400);
			
            echo json_encode(['error' => 'Missing required fields']);
			
            exit;
        }

        try {
            $res = $this->model->associateChecklist($tenant_id, $asset_id, $checklist_id, $work_order_ref);

            http_response_code(201);
			
            echo json_encode([
                'success' => true,
                'message' => 'Checklist associated',
                // ✅ CORRECT KEY: matches model return
                'maintenance_checklist_id' => $res['maintenance_checklist_id'],
                'inserted_tasks' => $res['inserted_tasks']
            ]);
			header("Location: /mes/dashboard_upcoming_maint");
            exit;

        } catch (Exception $e) {
            log_error($e->getMessage(), 'maintenance_associate_controller');

            if ($e->getMessage() === 'Work order already exists') {
                http_response_code(409);
				
                echo json_encode([
                    'error' => 'Duplicate',
                    'message' => "Record already exists, can't associate"
                ]);
				
                exit;
            }

            http_response_code(500);
			
            echo json_encode([
                'error'   => 'Association failed',
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
                // Removed trace for security in production
            ]);
			
            exit;
        }
    }
}
