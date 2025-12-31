<?php
// app/controllers/GroupPageController.php

require_once __DIR__ . '/../models/GroupPageModel.php';
require_once __DIR__ . '/../config/Database.php';

class GroupPageController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
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

        // Check: Is there already a placeholder (0,0) record for this tenant?
        // (Optional pre-check for better UX — model also checks, but this gives clearer context)
        $existingPlaceholder = $this->model->getPlaceholderRecord($orgId, 0); // page_id=0 or any
        // Actually, getPlaceholderRecord filters by page_id, so better to check differently:
        // We'll rely on the model's createPage() return value instead.

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
            header("Location: /mes/dashboard_admin?page_id=" . $pageId);
        } else {
            // 🔍 Specific error for duplicate (org_id, 0, 0)
            $_SESSION['error'] = "A default placeholder page already exists for this tenant. Only one blank page is allowed.";
            header("Location: /mes/dashboard_admin");
        }
        exit;
    }

    private function getNextPageId(string $orgId): int {
        $conn = Database::getInstance()->getConnection();
        
        // ✅ PostgreSQL-compatible: cast page_id to integer, use COALESCE
        $stmt = $conn->prepare("
            SELECT COALESCE(MAX(page_id::INTEGER), 0) + 1 
            FROM group_location_map 
            WHERE org_id = ?
        ");
        $stmt->execute([$orgId]);
        return (int) $stmt->fetchColumn();
    }
}