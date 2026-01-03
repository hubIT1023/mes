this one is working but not fetching the data:<?php
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
            $_SESSION['tenant_id'] = $this->tenant_id;
        }

        $this->model = new CompletedWorkOrdersModel();
    }

    /**
     * List completed work orders
     */
    public function index()
    {
        if (!$this->tenant_id) {
            die("Error: Tenant not set. Please log in.");
        }

        $filters = [
            'work_order_ref' => trim($_GET['work_order_ref'] ?? ''),
            'asset_id'       => trim($_GET['asset_id'] ?? ''),
            'date_from'      => trim($_GET['date_from'] ?? ''),
            'date_to'        => trim($_GET['date_to'] ?? ''),
            'page'           => max(1, (int)($_GET['page'] ?? 1)),
        ];

        $completed_work_orders = [];
        $total_records = 0;
        $items_per_page = 20;
        $total_pages = 1;
        $current_page = $filters['page'];
        $error_message = '';

        try {
            $results = $this->model->getCompletedWorkOrders(
                $this->tenant_id,
                $filters['work_order_ref'],
                $filters['asset_id'],
                $filters['date_from'],
                $filters['date_to'],
                $filters['page'],
                $items_per_page
            );

            $completed_work_orders = $results['data'] ?? [];
            $total_records = $results['total'] ?? 0;
            $total_pages = max(1, ceil($total_records / $items_per_page));

        } catch (Exception $e) {
            error_log("[CompletedWorkOrdersController] index error: " . $e->getMessage());
            $error_message = "Failed to load completed work orders. Please try again later.";
        }

        // Render view
        require __DIR__ . '/../views/completed_work_orders.php';
    }

    /**
     * View single completed work order details
     */
    public function view()
    {
        if (!$this->tenant_id) {
            die("Error: Tenant not set. Please log in.");
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