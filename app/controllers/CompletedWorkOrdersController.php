<?php
// app/controllers/CompletedWorkOrdersController.php

require_once __DIR__ . '/../models/CompletedWorkOrdersModel.php';

class CompletedWorkOrdersController
{
    private CompletedWorkOrdersModel $model;
    private ?string $tenant_id = null;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Unified tenant handling
        if (isset($_SESSION['tenant_id'])) {
            $this->tenant_id = $_SESSION['tenant_id'];
        } elseif (isset($_SESSION['tenant']['org_id'])) {
            $this->tenant_id = $_SESSION['tenant']['org_id'];
            $_SESSION['tenant_id'] = $this->tenant_id; // ✅ Ensure it's set
        }

        $this->model = new CompletedWorkOrdersModel();
    }

    /**
     * List completed work orders
     */
   public function index()
{
    // Start output buffering to prevent partial HTML
    ob_clean();
    header('Content-Type: application/json');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Debug session
    $session_debug = [
        'tenant_id' => $_SESSION['tenant_id'] ?? null,
        'tenant_org_id' => $_SESSION['tenant']['org_id'] ?? null,
        'all_session_keys' => array_keys($_SESSION)
    ];

    if (!isset($_SESSION['tenant_id']) && !isset($_SESSION['tenant']['org_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Session missing tenant info',
            'session' => $session_debug,
            'redirect_to' => '/mes/signin'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $tenant_id = $_SESSION['tenant_id'] ?? $_SESSION['tenant']['org_id'];
    $filters = [
        'work_order_ref' => trim($_GET['work_order_ref'] ?? ''),
        'asset_id'       => trim($_GET['asset_id'] ?? ''),
        'date_from'      => trim($_GET['date_from'] ?? ''),
        'date_to'        => trim($_GET['date_to'] ?? ''),
        'page'           => max(1, (int)($_GET['page'] ?? 1)),
    ];

    try {
        $results = $this->model->getCompletedWorkOrders(
            $tenant_id,
            $filters['work_order_ref'],
            $filters['asset_id'],
            $filters['date_from'],
            $filters['date_to'],
            $filters['page'],
            20
        );

        echo json_encode([
            'status' => 'success',
            'tenant_id' => $tenant_id,
            'filters' => $filters,
            'total_records' => $results['total'] ?? 0,
            'returned_rows' => count($results['data'] ?? []),
            'sample_row' => !empty($results['data']) ? $results['data'][0] : null,
            'all_data' => $results['data'] ?? []
        ], JSON_PRETTY_PRINT);

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'exception',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'tenant_id' => $tenant_id,
            'filters' => $filters
        ], JSON_PRETTY_PRINT);
    }
    exit;
}

    /**
     * View single completed work order details
     */
    public function view()
    {
        if (!$this->tenant_id) {
            header("Location: /mes/signin?error=" . urlencode("Please log in first"));
            exit;
        }

        $archiveId = $_GET['id'] ?? null;
        if (empty($archiveId) || !ctype_digit($archiveId)) {
            $error_message = 'Invalid archive ID.';
            $work_order = null;
            $tasks = [];
            require __DIR__ . '/../views/completed_work_order_details.php';
            return;
        }

        $work_order = null;
        $tasks = [];
        $error_message = '';

        try {
            $data = $this->model->getCompletedWorkOrderDetails($this->tenant_id, (int)$archiveId);

            if (!$data) {
                $error_message = 'Record not found or access denied.';
            } else {
                $work_order = $data['master'];
                $tasks = $data['tasks'];
            }

        } catch (Exception $e) {
            error_log("[CompletedWorkOrdersController] view error: " . $e->getMessage());
            $error_message = "Failed to load work order details.";
        }

        require __DIR__ . '/../views/completed_work_order_details.php';
    }
}