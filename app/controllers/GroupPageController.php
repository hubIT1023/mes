<?php
// app/controllers/GroupPageController.php

require_once __DIR__ . '/../models/GroupPageModel.php';

class GroupPageController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=" . urlencode("Please log in first"));
            exit;
        }
        $this->model = new GroupPageModel();
    }

    /**
     * Handle POST /create-page
     */
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        // CSRF Protection
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $pageName = trim($_POST['page_name'] ?? '');

        // Validate input
        if (empty($pageName)) {
            $_SESSION['error'] = "Page name is required.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        if (strlen($pageName) > 100) {
            $_SESSION['error'] = "Page name is too long (max 100 characters).";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Prevent duplicate page names (per tenant)
        if ($this->model->isPageNameUsed($orgId, $pageName)) {
            $_SESSION['error'] = "A page with that name already exists.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Generate next page ID
        $pageId = $this->model->getNextPageId($orgId);

        // Create initial placeholder group for the new page
        $result = $this->model->createPage([
            'org_id' => $orgId,
            'page_id' => $pageId,
            'page_name' => $pageName,
            'group_code' => 0,
            'location_code' => 0,
            'group_name' => '---',      // Placeholder
            'location_name' => '---'    // Placeholder
        ]);

        if ($result) {
            $_SESSION['success'] = "Page '$pageName' created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create page. Please try again.";
        }

        // Redirect to the new page
        header("Location: /mes/dashboard_admin?page_id=" . $pageId);
        exit;
    }
}