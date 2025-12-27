<?php
require_once __DIR__ . '/../models/UpdateEntityPositionModel.php';

class UpdateEntityPositionController {
    private $model;

    public function __construct() {
        $this->model = new UpdateEntityPositionModel();
    }

    public function handleUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=Unauthorized");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $entityId = (int)($_POST['entity_id'] ?? 0);
        $row_pos = (int)($_POST['row_pos'] ?? 1);
        $col_pos = (int)($_POST['col_pos'] ?? 1);

        if ($col_pos < 1 || $col_pos > 9 || $row_pos < 1) {
            $_SESSION['error'] = "Column must be 1–9, row ≥ 1.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        if ($this->model->updatePosition($orgId, $entityId, $row_pos, $col_pos)) {
            $_SESSION['success'] = "Position updated!";
        } else {
            $_SESSION['error'] = "Failed to update position.";
        }

         header("Location: /mes/dashboard_admin");
        exit;
    }
}