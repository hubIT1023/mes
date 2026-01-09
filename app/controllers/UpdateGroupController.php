<?php
// app/controllers/UpdateGroupController.php

require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../models/DashboardService.php';

class UpdateGroupController {
    private $model;
    private $dashboardService;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin");
            exit;
        }
        $this->model = new GroupModel();
        $this->dashboardService = new DashboardService();
    }

    public function handleUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        // Get orgId early
        $orgId = $_SESSION['tenant_id'];

        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            $safePageId = $this->dashboardService->getValidRedirectPageId($orgId);
            header("Location: /mes/dashboard_admin?page_id=" . $safePageId);
            exit;
        }

        $groupId = (int)($_POST['group_id'] ?? 0);
        $pageId = (int)($_POST['page_id'] ?? 0);
        $groupName = trim($_POST['group_name'] ?? '');
        $locationName = trim($_POST['location_name'] ?? '');
        $seqId = (int)($_POST['seq_id'] ?? 1);

        // ✅ Validate group fields (NOT page fields)
        if (empty($groupName) || empty($locationName) || $groupId <= 0 || $pageId <= 0) {
            $_SESSION['error'] = "Group name, location name, and IDs are required.";
            $safePageId = $this->dashboardService->getValidRedirectPageId($orgId, $pageId);
            header("Location: /mes/dashboard_admin?page_id=" . $safePageId);
            exit;
        }

        // ✅ Verify group exists and belongs to tenant/page
        if (!$this->model->groupExists($groupId, $orgId, $pageId)) {
            $_SESSION['error'] = "Invalid group selection.";
            $safePageId = $this->dashboardService->getValidRedirectPageId($orgId, $pageId);
            header("Location: /mes/dashboard_admin?page_id=" . $safePageId);
            exit;
        }

        // ✅ Ensure seq_id is valid
        if ($seqId < 1) {
            $seqId = 1;
        }

        // ✅ Update group
        $data = [
            'id' => $groupId,
            'group_name' => $groupName,
            'location_name' => $locationName,
            'seq_id' => $seqId
        ];

        if ($this->model->updateGroup($_SESSION['tenant_id'], $data)) {
            $_SESSION['success'] = "Group updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update group.";
        }

        // ✅ Redirect to same page (it still exists)
        $safePageId = $this->dashboardService->getValidRedirectPageId($orgId, $pageId);
        header("Location: /mes/dashboard_admin?page_id=" . $safePageId);
        exit;
    }
}