<?php
// app/models/DashboardService.php

require_once __DIR__ . '/../config/Database.php';

class DashboardService {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Get a safe page ID to redirect to after any action.
     * Prefers the current page if it still exists.
     */
    public function getValidRedirectPageId(string $orgId, ?int $preferredPageId = null): ?int {
        // Try preferred page first
        if ($preferredPageId !== null) {
            $stmt = $this->conn->prepare("
                SELECT 1 FROM group_location_map 
                WHERE org_id = ? AND page_id = ? 
                LIMIT 1
            ");
            $stmt->execute([$orgId, $preferredPageId]);
            if ($stmt->fetch()) {
                return $preferredPageId;
            }
        }

        // Fallback: get first available page
        $stmt = $this->conn->prepare("
            SELECT page_id FROM group_location_map 
            WHERE org_id = ? 
            ORDER BY page_id::INTEGER 
            LIMIT 1
        ");
        $stmt->execute([$orgId]);
        $pageId = $stmt->fetchColumn();
        return $pageId ? (int)$pageId : null;
    }
}