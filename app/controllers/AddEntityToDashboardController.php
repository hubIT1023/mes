<?php
// app/controllers/AddEntityToDashboardController.php

require_once __DIR__ . '/../models/AddEntityToDashboardModel.php';

class AddEntityToDashboardController {
    private $model;

    public function __construct() {
        $this->model = new AddEntityToDashboardModel();
    }

    public function handleAddEntity(): void {
        // Only allow POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        // Start session if not active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Ensure user is logged in
        if (!isset($_SESSION['tenant'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        $orgId = $_SESSION['tenant_id'] ?? null;
        if (!$orgId) {
            $_SESSION['error'] = "Unauthorized: Missing tenant ID";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Sanitize inputs
        $group_code = (int)($_POST['group_code'] ?? 0);
        $location_code = (int)($_POST['location_code'] ?? 0);
        $asset_id = trim($_POST['asset_id'] ?? '');
        $entity = trim($_POST['entity'] ?? '');

        // Validate required fields
        if (empty($asset_id) || empty($entity) || $group_code <= 0 || $location_code <= 0) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Verify asset exists in [assets] table and get official asset_name
        $officialAssetName = $this->model->getAssetName($orgId, $asset_id);
        if ($officialAssetName === null) {
            $_SESSION['error'] = "Asset ID does not exist in your inventory.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Use official asset_name for data integrity
        $entity = $officialAssetName;

        // Check if entity is already in this group
        if ($this->model->isEntityUsed($orgId, $group_code, $location_code, $asset_id)) {
            $_SESSION['error'] = "This asset is already added to the group.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        // Prepare data for insertion
        $data = [
            'org_id' => $orgId,
            'group_code' => $group_code,
            'location_code' => $location_code,
            'asset_id' => $asset_id,
            'entity' => $entity
        ];

        // Add to registered_tools
        if ($this->model->addEntity($data)) {
            $_SESSION['success'] = "Entity '$entity' added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add entity. Please try again.";
        }
        
        // ✅ Get page_id and redirect back to the same page
        $pageId = $this->model->getPageIdByGroupCode($group_code, $orgId);
        header("Location: /mes/dashboard_admin?page_id=" . $pageId);
        exit;
    }
}