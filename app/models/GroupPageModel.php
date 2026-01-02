<?php
// app/models/GroupPageModel.php

require_once __DIR__ . '/../config/Database.php';

class GroupPageModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Get the next available page_id for a tenant (org_id).
     * Assumes page_id is stored as TEXT but represents integers.
     */
    public function getNextPageId(string $orgId): int {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(MAX(page_id::INTEGER), 0) + 1 
            FROM group_location_map 
            WHERE org_id = ?
        ");
        $stmt->execute([$orgId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Check if a page name already exists for the tenant.
     */
    public function isPageNameUsed(string $orgId, string $pageName): bool {
        $stmt = $this->conn->prepare("
            SELECT 1 
            FROM group_location_map 
            WHERE org_id = ? AND page_name = ?
            LIMIT 1
        ");
        $stmt->execute([$orgId, $pageName]);
        return (bool) $stmt->fetch();
    }

    /**
     * Create a new page with a placeholder group ('---').
     */
    public function createPage(array $data): bool {
        $sql = "
            INSERT INTO group_location_map (
                org_id,
                page_id,
                page_name,
                group_code,
                location_code,
                group_name,
                location_name,
                created_at
            ) VALUES (
                :org_id,
                :page_id,
                :page_name,
                :group_code,
                :location_code,
                :group_name,
                :location_name,
                CURRENT_TIMESTAMP
            )
        ";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'org_id' => $data['org_id'],
            'page_id' => $data['page_id'],
            'page_name' => $data['page_name'],
            'group_code' => $data['group_code'],
            'location_code' => $data['location_code'],
            'group_name' => $data['group_name'],
            'location_name' => $data['location_name']
        ]);
    }

    /**
     * Get placeholder record (group_name = '---') for a given org and page.
     */
    public function getPlaceholderRecord(string $orgId, int $pageId): ?array {
        $stmt = $this->conn->prepare("
            SELECT id, group_code, location_code, group_name, location_name
            FROM group_location_map 
            WHERE org_id = ? AND page_id = ? AND group_name = '---'
        ");
        $stmt->execute([$orgId, $pageId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Update an existing placeholder record (optional, for future use).
     */
    public function updatePlaceholder(array $data): bool {
        $sql = "
            UPDATE group_location_map 
            SET 
                group_code = :group_code,
                location_code = :location_code,
                group_name = :group_name,
                location_name = :location_name,
                page_name = :page_name,
                updated_at = CURRENT_TIMESTAMP
            WHERE org_id = :org_id AND page_id = :page_id AND group_name = '---'
        ";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'org_id' => $data['org_id'],
            'page_id' => $data['page_id'],
            'group_name' => $data['group_name'],
            'location_name' => $data['location_name'],
            'page_name' => $data['page_name'],
            'group_code' => $data['group_code'],
            'location_code' => $data['location_code']
        ]);
    }
}