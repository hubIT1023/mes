<?php
// app/controllers/UpdateGroupController.php

require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../models/DashboardService.php'; // ← ADD THIS

class UpdateGroupController {
    private $model;
    private $dashboardService; // ← ADD THIS

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin");
            exit;
        }
        $this->model = new GroupModel();
        $this->dashboardService = new DashboardService(); // ← ADD THIS
    }

    public function handleUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        // Get orgId early for error handling
        $orgId = $_SESSION['tenant_id'];
        $pageId = (int)($_POST['page_id'] ?? 0); // ← Declare early

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            $safePageId = $this->dashboardService->getValidRedirectPageId($orgId, $pageId);
            header("Location: /mes/dashboard_admin?page_id=" . $safePageId);
            exit;
        }

        $groupId = (int)($_POST['group_id'] ?? 0);
        $groupName = trim($_POST['group_name'] ?? '');
        $locationName = trim($_POST['location_name'] ?? '');

        if (empty($groupName) || empty($locationName) || $groupId <= 0 || $pageId <= 0) {
            $_SESSION['error'] = "All fields are required.";
            $safePageId = $this->dashboardService->getValidRedirectPageId($orgId, $pageId);
            header("Location: /mes/dashboard_admin?page_id=" . $safePageId);
            exit;
        }

        // Verify group belongs to tenant and page
        if (!$this->model->groupExists($groupId, $orgId, $pageId)) {
            $_SESSION['error'] = "Invalid group selection.";
            $safePageId = $this->dashboardService->getValidRedirectPageId($orgId, $pageId);
            header("Location: /mes/dashboard_admin?page_id=" . $safePageId);
            exit;
        }
		
		$seqId = (int)($_POST['seq_id'] ?? 1);
		if ($seqId < 1) {
			$seqId = 1;
		}

        // Update group
        $data = [
            'id' => $groupId,
            'group_name' => $groupName,
            'location_name' => $locationName,
			'seq_id' => $seqId 
        ];

        if ($this->model->updateGroup($data)) {
            $_SESSION['success'] = "Group updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update group.";
        }

        $safePageId = $this->dashboardService->getValidRedirectPageId($orgId, $pageId);
		header("Location: /mes/dashboard_admin?page_id=" . $safePageId);
        exit; // ← Always exit after redirect
    }
}