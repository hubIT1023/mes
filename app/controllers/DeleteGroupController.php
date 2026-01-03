<?php
// app/controllers/DeleteGroupController.php

require_once __DIR__ . '/../models/GroupModel.php';

class DeleteGroupController
{
    private $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=" . urlencode("Please log in first"));
            exit;
        }

        $this->model = new GroupModel();
    }

    public function handleDelete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            $pageId = trim($_POST['page_id'] ?? '');
            $redirect = $pageId ? "/mes/dashboard_admin?page_id=" . urlencode($pageId) : "/mes/dashboard_admin";
            header("Location: $redirect");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $groupId = (int)($_POST['group_id'] ?? 0);
        $pageId = trim($_POST['page_id'] ?? ''); // ✅ Keep as STRING

        // Validate inputs (pageId as string)
        if ($groupId <= 0 || empty($pageId)) {
            $_SESSION['error'] = "Invalid request.";
            $redirect = $pageId ? "/mes/dashboard_admin?page_id=" . urlencode($pageId) : "/mes/dashboard_admin";
            header("Location: $redirect");
            exit;
        }

        // ✅ Pass pageId as string to model
        if (!$this->model->groupExists($groupId, $orgId, $pageId)) {
            $_SESSION['error'] = "Group not found or access denied.";
            header("Location: /mes/dashboard_admin?page_id=" . urlencode($pageId));
            exit;
        }

        $result = $this->model->deleteGroup($groupId, $orgId);
        
        if ($result) {
            $_SESSION['success'] = "Group deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete group.";
        }

        header("Location: /mes/dashboard_admin?page_id=" . urlencode($pageId));
        exit;
    }
}