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

    // -------------------------------
    // POST /mes/create-page
    // -------------------------------
    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); exit;
        }
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $pageName = trim($_POST['page_name'] ?? '');

        if (empty($pageName) || strlen($pageName) > 100) {
            $_SESSION['error'] = "Page name is required (max 100 chars).";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        if ($this->model->isPageNameUsed($orgId, $pageName)) {
            $_SESSION['error'] = "A page with that name already exists.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $pageId = $this->model->getNextPageId($orgId);
        $result = $this->model->createPage([
            'org_id' => $orgId,
            'page_id' => $pageId,
            'page_name' => $pageName,
            'group_code' => 0,
            'location_code' => 0,
            'group_name' => '---',
            'location_name' => '---'
        ]);

        if ($result) {
            $_SESSION['success'] = "Page '$pageName' created!";
            header("Location: /mes/dashboard_admin?page_id=" . $pageId);
        } else {
            $_SESSION['error'] = "Failed to create page.";
            header("Location: /mes/dashboard_admin");
        }
        exit;
    }

    // -------------------------------
    // POST /mes/rename-page
    // -------------------------------
    public function rename(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); exit;
        }
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $pageId = (int)($_POST['page_id'] ?? 0);
        $newName = trim($_POST['page_name'] ?? '');

        if (empty($pageId) || empty($newName) || strlen($newName) > 100) {
            $_SESSION['error'] = "Page ID and valid name required.";
            header("Location: /mes/dashboard_admin?page_id=$pageId");
            exit;
        }

        if ($this->model->isPageNameUsed($orgId, $newName)) {
            $_SESSION['error'] = "A page with that name already exists.";
            header("Location: /mes/dashboard_admin?page_id=$pageId");
            exit;
        }

        $result = $this->model->renamePage($orgId, $pageId, $newName);
        if ($result) {
            $_SESSION['success'] = "Page renamed to '$newName'!";
        } else {
            $_SESSION['error'] = "Failed to rename page.";
        }
        header("Location: /mes/dashboard_admin?page_id=$pageId");
        exit;
    }

    
	
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