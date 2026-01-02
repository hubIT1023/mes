<?php
// app/models/GroupPageModel.php

require_once __DIR__ . '/../config/Database.php';

class GroupPageModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function getNextPageId(string $orgId): int {
        $stmt = $this->conn->prepare("
            SELECT COALESCE(MAX(page_id::INTEGER), 0) + 1 
            FROM group_location_map 
            WHERE org_id = ?
        ");
        $stmt->execute([$orgId]);
        return (int) $stmt->fetchColumn();
    }

    public function isPageNameUsed(string $orgId, string $pageName): bool {
        $stmt = $this->conn->prepare("
            SELECT 1 FROM group_location_map 
            WHERE org_id = ? AND page_name = ?
            LIMIT 1
        ");
        $stmt->execute([$orgId, $pageName]);
        return (bool) $stmt->fetch();
    }

    public function createPage(array $data): bool {
        $sql = "
            INSERT INTO group_location_map (
                org_id, page_id, page_name,
                group_code, location_code,
                group_name, location_name,
                created_at
            ) VALUES (
                :org_id, :page_id, :page_name,
                :group_code, :location_code,
                :group_name, :location_name,
                CURRENT_TIMESTAMP
            )
        ";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function renamePage(string $orgId, int $pageId, string $newName): bool {
        $stmt = $this->conn->prepare("
            UPDATE group_location_map 
            SET page_name = :page_name, updated_at = CURRENT_TIMESTAMP
            WHERE org_id = :org_id AND page_id = :page_id
        ");
        return $stmt->execute([
            'org_id' => $orgId,
            'page_id' => $pageId,
            'page_name' => $newName
        ]);
    }

    public function deletePage(string $orgId, int $pageId): bool {
        $stmt = $this->conn->prepare("
            DELETE FROM group_location_map 
            WHERE org_id = :org_id AND page_id = :page_id
        ");
        return $stmt->execute([
            'org_id' => $orgId,
            'page_id' => $pageId
        ]);
    }

    // ... (keep your existing getPlaceholderRecord, updatePlaceholder if needed)
}