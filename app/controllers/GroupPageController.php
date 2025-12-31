<?php
// app/controllers/GroupPageController.php

require_once __DIR__ . '/../models/GroupPageModel.php';
require_once __DIR__ . '/../config/Database.php';

class GroupPageController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['tenant_id'])) {
            header("Location: /mes/signin");
            exit;
        }
        $this->model = new GroupPageModel();
    }

    public function store() {
        // Validate CSRF
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = "Security check failed.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $orgId = $_SESSION['tenant_id'];
        $pageName = trim($_POST['page_name'] ?? '');

        if (empty($pageName)) {
            $_SESSION['error'] = "Page name is required.";
            header("Location: /mes/dashboard_admin");
            exit;
        }

        $pageId = $this->getNextPageId($orgId);

        $data = [
            'org_id' => $orgId,
            'page_id' => $pageId,
            'page_name' => $pageName,
            'group_code' => 0,
            'location_code' => 0,
            'group_name' => '---',
            'location_name' => '---'
        ];
        
        $result = $this->model->createPage($data);

        if ($result) {
            $_SESSION['success'] = "Page '$pageName' created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create page.";
        }

        header("Location: /mes/dashboard_admin?page_id=" . $pageId);
        exit;
    }

    /**
     * Get next available page_id for the tenant.
     * Does NOT use $this->conn — uses Database singleton directly.
     */
    private function getNextPageId(string $orgId): int {
        // ✅ Always get a fresh connection from the singleton
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare("
            SELECT COALESCE(MAX(page_id::INTEGER), 0) + 1 
            FROM group_location_map 
            WHERE org_id = ?
        ");
        $stmt->execute([$orgId]);
        $result = $stmt->fetchColumn();
        return $result ? (int) $result : 1;
    }
}