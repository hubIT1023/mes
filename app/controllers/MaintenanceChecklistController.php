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
     * Returns JSON only — no redirects
     */
    public function associate()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant'])) {
            http_response_code(401);
            //echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            //echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }

        $tenant_id = trim($_POST['tenant_id'] ?? '');
        $asset_id = trim($_POST['asset_id'] ?? '');
        $checklist_id = trim($_POST['checklist_id'] ?? '');
        $work_order_ref = trim($_POST['work_order_ref'] ?? '');

        if (!$tenant_id || !$asset_id || !$checklist_id || !$work_order_ref) {
            http_response_code(400);
            //echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        try {
            // ✅ Model returns ARRAY with maintenance_checklist_id
            $result = $this->model->associateChecklist(
                $tenant_id,
                $asset_id,
                $checklist_id,
                $work_order_ref
            );

            // ✅ Extract the ID from the array
            $maintenanceId = $result['maintenance_checklist_id'];

            http_response_code(201);
			/*
            echo json_encode([
                'success' => true,
                'message' => 'Checklist associated successfully',
                'maintenance_checklist_id' => $maintenanceId,
                'inserted_tasks' => $result['inserted_tasks'] // Optional: include task count
            ]);
			*/
            exit;

        } catch (Exception $e) {
            log_error($e->getMessage(), 'maintenance_associate_controller');

            if (strpos($e->getMessage(), 'already exists') !== false) {
                http_response_code(409);
                /*
				echo json_encode([
                    'error' => 'Duplicate',
                    'message' => "Checklist already associated for this work order"
                ]);
				*/
                exit;
            }

            http_response_code(500);
            /*
			echo json_encode([
                'error'   => 'Association failed',
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ]);
			*/
            exit;
        }
    }
}