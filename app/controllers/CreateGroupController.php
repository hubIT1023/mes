<?php
// app/controllers/CreateGroupController.php

require_once __DIR__ . '/../models/CreateGroupModel.php';

class CreateGroupController {
    private $model;
	private $dashboardService; // ← ADD THIS


    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=" . urlencode("Please log in first"));
            exit;
        }
        
        $this->model = new CreateGroupModel();
		$this->dashboardService = new DashboardService(); // ← ADD THIS
    }

    public function handleCreateGroup(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        // Validate CSRF
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $pageId = (int)($_POST['page_id'] ?? 0);
        $group_name = trim($_POST['group_name'] ?? '');
        $location_name = trim($_POST['location_name'] ?? '');

        // Validate required fields
        if (empty($pageId)) {
            $_SESSION['error'] = "Page selection is required.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        if (empty($group_name) || empty($location_name)) {
            $_SESSION['error'] = "Group and Location names are required.";
            header("Location: /mes/dashboard_admin?page_id=" . $pageId);
            exit;
        }

        // Verify page belongs to current tenant
        $pageName = $this->model->getPageName($pageId, $orgId);
        if (!$pageName) {
            $_SESSION['error'] = "Invalid page selection.";
            header("Location: /mes/dashboard_admin?page_id=" . $pageId);
            exit;
        }

        // Generate unique codes (per tenant)
        $attempts = 0;
        do {
            $group_code = random_int(1000, 9999);
            $location_code = random_int(1000, 9999);
            $attempts++;
        } while (
            ($this->model->isGroupCodeUsed($group_code, $orgId) ||
             $this->model->isLocationCodeUsed($location_code, $orgId)) &&
            $attempts < 10
        );

        if ($attempts >= 10) {
            $_SESSION['error'] = "Could not generate unique codes. Please try again.";
            header("Location: /mes/dashboard_admin?page_id=" . $pageId);
            exit;
        }

        // ✅ Calculate next sequence ID for this page
        $seqId = $this->model->getNextSequenceId($orgId, $pageId);

        // ✅ Check if placeholder record exists for this page
        $placeholder = $this->model->getPlaceholderRecord($orgId, $pageId);

        if ($placeholder) {
        // ✅ UPDATE placeholder record (first group on page)
				$result = $this->model->updatePlaceholder([
					'id' => $placeholder['id'],
					'group_code' => $group_code,
					'location_code' => $location_code,
					'group_name' => $group_name,
					'location_name' => $location_name,
					'page_name' => $pageName,
					'seq_id' => $seqId  // ✅ Add seq_id
				]);
			} else {
				// ✅ CREATE new group record (additional groups on page)
				$result = $this->model->createGroup([
					'org_id' => $orgId,
					'page_id' => $pageId,
					'page_name' => $pageName,
					'group_name' => $group_name,
					'location_name' => $location_name,
					'group_code' => $group_code,
					'location_code' => $location_code,
					'seq_id' => $seqId  // ✅ Add seq_id
				]);
			}

         if ($result) {
            $_SESSION['success'] = "Group '$group_name' created in page '$pageName'!";
        } else {
            $_SESSION['error'] = "Failed to create group.";
        }

        // Redirect to the SAME page (it still exists)
        $safePageId = $this->dashboardService->getValidRedirectPageId($orgId, $pageId);
        $url = $safePageId 
            ? "/mes/dashboard_admin?page_id=$safePageId" 
            : "/mes/dashboard_admin";
        header("Location: $url");
        exit;
    }
}