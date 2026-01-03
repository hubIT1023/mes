<?php
// app/controllers/AddEntityToDashboardController.php

require_once __DIR__ . '/../models/AddEntityToDashboardModel.php';

class AddEntityToDashboardController {
    private $model;

    public function __construct() {
        $this->model = new AddEntityToDashboardModel();
    }

    public function handleAddEntity(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method not allowed');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin?error=Please+log+in+first");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $group_code = (int)($_POST['group_code'] ?? 0);
        $location_code = (int)($_POST['location_code'] ?? 0);
        $asset_id = trim($_POST['asset_id'] ?? '');
        $entity = trim($_POST['entity'] ?? '');

        if (empty($asset_id) || empty($entity) || $group_code <= 0 || $location_code <= 0) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $officialAssetName = $this->model->getAssetName($orgId, $asset_id);
        if ($officialAssetName === null) {
            $_SESSION['error'] = "Asset ID does not exist in your inventory.";
            header("Location: /mes/dashboard_admin");
            exit;
        }
        $entity = $officialAssetName;

        // ✅ Get page_id as STRING
        $pageId = $this->model->getPageIdByGroupCode($group_code, $orgId);
        if ($pageId === null) {
            $_SESSION['error'] = "Group not found.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        if ($this->model->isEntityUsed($orgId, $group_code, $location_code, $asset_id)) {
            $_SESSION['error'] = "This asset is already added to the group.";
            header("Location: /mes/dashboard_admin?page_id=" . $pageId);
            exit;
        }

        // ✅ Include page_id (string) in data
        $data = [
            'org_id' => $orgId,
            'page_id' => $pageId,          // STRING like '3'
            'group_code' => $group_code,
            'location_code' => $location_code,
            'asset_id' => $asset_id,
            'entity' => $entity
        ];

        if ($this->model->addEntity($data)) {
            $_SESSION['success'] = "Entity '$entity' added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add entity.";
        }

        header("Location: /mes/dashboard_admin?page_id=" . $pageId);
        exit;
    }
}