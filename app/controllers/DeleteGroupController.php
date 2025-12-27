<?php
// app/controllers/DeleteGroupController.php

require_once __DIR__ . '/../models/GroupModel.php';

class DeleteGroupController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin");
            exit;
        }
        $this->model = new GroupModel();
    }

    public function handleDelete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $groupId = (int)($_POST['group_id'] ?? 0);
        $pageId = (int)($_POST['page_id'] ?? 0);

        if ($groupId <= 0 || $pageId <= 0) {
            $_SESSION['error'] = "Invalid request.";
            header("Location: /mes/dashboard_admin?page_id=" . $pageId);
            exit;
        }

        // Verify group belongs to tenant and page
        if (!$this->model->groupExists($groupId, $orgId, $pageId)) {
            $_SESSION['error'] = "Invalid group selection.";
            header("Location: /mes/dashboard_admin?page_id=" . $pageId);
            exit;
        }

        // Delete group and associated entities
        if ($this->model->deleteGroup($groupId, $orgId)) {
            $_SESSION['success'] = "Group deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete group.";
        }

        header("Location: /mes/dashboard_admin?page_id=" . $pageId);
        exit;
    }
}