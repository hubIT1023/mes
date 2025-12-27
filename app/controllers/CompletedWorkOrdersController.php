<?php
// CompletedWorkOrdersController.php

require_once __DIR__ . '/../models/CompletedWorkOrdersModel.php';

class CompletedWorkOrdersController
{
    private $model;

    public function __construct()
    {
        $this->model = new CompletedWorkOrdersModel();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Unified tenant handling
        if (!empty($_SESSION['tenant_id'])) {
            $tenant_id = $_SESSION['tenant_id'];
        } elseif (!empty($_SESSION['tenant']['org_id'])) {
            $tenant_id = $_SESSION['tenant']['org_id'];
            $_SESSION['tenant_id'] = $tenant_id;
        } else {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

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

            $completed_work_orders = $results['data'];
            $total_records = $results['total'];
            $items_per_page = 20;

            $total_pages = max(1, ceil($total_records / $items_per_page));
            $current_page = $filters['page'];

            require __DIR__ . '/../views/completed_work_orders.php';

        } catch (Exception $e) {
            error_log("Completed WO index error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to load completed work orders.';
            header("Location: /mes/mms_admin");
            exit;
        }
    }

    /**
     * View details of a completed work order
     * Uses query string: ?id=123
     */
    public function view()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Unified tenant handling
        if (isset($_SESSION['tenant_id'])) {
            $tenant_id = $_SESSION['tenant_id'];
        } elseif (isset($_SESSION['tenant']['org_id'])) {
            $tenant_id = $_SESSION['tenant']['org_id'];
            $_SESSION['tenant_id'] = $tenant_id;
        } else {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        // ✅ Get ID from query string (not route parameter)
        $archiveId = $_GET['id'] ?? null;

        if (empty($archiveId) || !ctype_digit($archiveId)) {
            $_SESSION['flash_error'] = 'Invalid archive ID.';
            header("Location: /mes/completed_work_orders");
            exit;
        }

        try {
            $work_order_data = $this->model->getCompletedWorkOrderDetails($tenant_id, (int)$archiveId);

            if (!$work_order_data) {
                $_SESSION['flash_error'] = 'Record not found or access denied.';
                header("Location: /mes/completed_work_orders");
                exit;
            }

            $work_order = $work_order_data['master'];
            $tasks = $work_order_data['tasks'];

            require __DIR__ . '/../views/completed_work_order_details.php';

        } catch (Exception $e) {
            error_log("View error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to load work order details.';
            header("Location: /mes/completed_work_orders");
            exit;
        }
    }
}