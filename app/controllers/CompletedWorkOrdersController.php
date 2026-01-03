<?php
// app/controllers/CompletedWorkOrdersController.php
require_once __DIR__ . '/../models/CompletedWorkOrdersModel.php';

class CompletedWorkOrdersController
{
    private $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tenant_id']) && isset($_SESSION['tenant']['org_id'])) {
            $_SESSION['tenant_id'] = $_SESSION['tenant']['org_id'];
        }

        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=" . urlencode("Please log in first"));
            exit;
        }

        $this->model = new CompletedWorkOrdersModel();
    }

    public function index()
    {
        $tenant_id = $_SESSION['tenant_id'];

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

            // Data for the view
            $completed_work_orders = $results['data'];
            $total_records = $results['total'];
            $items_per_page = 20;
            $total_pages = max(1, ceil($total_records / $items_per_page));
            $current_page = $filters['page'];

            require __DIR__ . '/../views/completed_work_orders.php';

        } catch (Exception $e) {
            error_log("Completed WO error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to load completed work orders.';
            header("Location: /mes/mms_admin");
            exit;
        }
    }

    public function view()
    {
        $tenant_id = $_SESSION['tenant_id'];
        $archiveId = $_GET['id'] ?? null;

        if (empty($archiveId) || !ctype_digit($archiveId)) {
            $_SESSION['error'] = 'Invalid archive ID.';
            header("Location: /mes/completed_work_orders");
            exit;
        }

        try {
            $work_order_data = $this->model->getCompletedWorkOrderDetails($tenant_id, (int)$archiveId);

            if (!$work_order_data) {
                $_SESSION['error'] = 'Record not found or access denied.';
                header("Location: /mes/completed_work_orders");
                exit;
            }

            $work_order = $work_order_data['master'];
            $tasks = $work_order_data['tasks'];

            require __DIR__ . '/../views/completed_work_order_details.php';

        } catch (Exception $e) {
            error_log("View error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to load work order details.';
            header("Location: /mes/completed_work_orders");
            exit;
        }
    }
}
