<?php
// app/controllers/DeleteGroupController.php

require_once __DIR__ . '/../models/GroupModel.php';

class DeleteGroupController
{
    private $model;
	private $dashboardService; // ← ADD THIS


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

        // CSRF protection
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            $pageId = (int)($_POST['page_id'] ?? 0);
            $redirect = $pageId ? "/mes/dashboard_admin?page_id=$pageId" : "/mes/dashboard_admin";
            header("Location: $redirect");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $groupId = (int)($_POST['group_id'] ?? 0);
        $pageId = (int)($_POST['page_id'] ?? 0);

        // Validate input
        if ($groupId <= 0 || $pageId <= 0) {
            $_SESSION['error'] = "Invalid request.";
            $redirect = $pageId ? "/mes/dashboard_admin?page_id=$pageId" : "/mes/dashboard_admin";
            header("Location: $redirect");
            exit;
        }

        // Verify group ownership and existence
        if (!$this->model->groupExists($groupId, $orgId, $pageId)) {
            $_SESSION['error'] = "Group not found or access denied.";
            header("Location: /mes/dashboard_admin?page_id=$pageId");
            exit;
        }

        // Perform deletion (cascades to entities and states)
        $result = $this->model->deleteGroup($groupId, $orgId);
        
        if ($result) {
            $_SESSION['success'] = "Group deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete group.";
        }

        // Always redirect to a VALID page
        $safePageId = $this->dashboardService->getValidRedirectPageId($orgId, $pageId);
        if ($safePageId) {
            header("Location: /mes/dashboard_admin?page_id=" . $safePageId);
        } else {
            header("Location: /mes/dashboard_admin");
        }
        exit;
    }
}