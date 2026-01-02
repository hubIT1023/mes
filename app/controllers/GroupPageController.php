<?php
// app/controllers/GroupPageController.php

require_once __DIR__ . '/../models/GroupPageModel.php';

class GroupPageController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=" . urlencode("Please log in first"));
            exit;
        }
        $this->model = new GroupPageModel();
    }

    // ... (store and rename methods unchanged) ...

    // -------------------------------
    // POST /mes/delete-page
    // -------------------------------
    public function destroy(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $pageId = (int)($_POST['page_id'] ?? 0);

        if ($pageId <= 0) {
            $_SESSION['error'] = "Invalid page.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Verify page exists
        $pageName = $this->model->getPageName($pageId, $orgId);
        if (!$pageName) {
            $_SESSION['error'] = "Page not found.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $result = $this->model->deletePage($orgId, $pageId);
        
        if ($result) {
            $_SESSION['success'] = "Page deleted successfully.";
            $firstPageId = $this->model->getFirstPageId($orgId);
            if ($firstPageId !== null) {
                header("Location: /mes/dashboard_admin?page_id=" . $firstPageId);
            } else {
                header("Location: /mes/dashboard_admin");
            }
        } else {
            $_SESSION['error'] = "Failed to delete page.";
            header("Location: /mes/dashboard_admin?page_id=" . $pageId);
        }
        exit;
    }
}