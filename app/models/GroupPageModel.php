<?php
// app/models/GroupPageModel.php

require_once __DIR__ . '/../config/Database.php';

class GroupPageModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Get next page ID as INTEGER (for numeric page IDs)
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

    public function isPageNameUsed(string $orgId, string $pageName): bool {
        $stmt = $this->conn->prepare("
            SELECT 1 FROM group_location_map 
            WHERE org_id = ? AND page_name = ?
            LIMIT 1
        ");
        $stmt->execute([$orgId, $pageName]);
        return (bool) $stmt->fetch();
    }

    /**
     * Create a new page (page_id stored as STRING in DB)
     */
    public function createPage(array $data): bool {
        // Ensure page_id is stored as string to match VARCHAR(10)
        $data['page_id'] = (string) $data['page_id'];
        
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

    /**
     * Rename a page (page_id treated as STRING in WHERE clause)
     */
    public function renamePage(string $orgId, int $pageId, string $newName): bool {
        $pageIdStr = (string) $pageId;
        $stmt = $this->conn->prepare("
            UPDATE group_location_map 
            SET page_name = :page_name
            WHERE org_id = :org_id AND page_id = :page_id
        ");
        return $stmt->execute([
            'org_id' => $orgId,
            'page_id' => $pageIdStr, // VARCHAR comparison
            'page_name' => $newName
        ]);
    }

    /**
     * ✅ FULL PAGE DELETE: Remove page, groups, AND all dependent tools
     */
    public function deletePage(string $orgId, int $pageId): bool {
        $pageIdStr = (string) $pageId;

        // Verify page exists
        $existsStmt = $this->conn->prepare("
            SELECT 1 FROM group_location_map 
            WHERE org_id = ? AND page_id = ? 
            LIMIT 1
        ");
        $existsStmt->execute([$orgId, $pageIdStr]);
        if (!$existsStmt->fetch()) {
            return true; // Already deleted
        }

        try {
            $this->conn->beginTransaction();

            // ✅ Delete all tools associated with this page
            $this->conn->prepare("
                DELETE FROM registered_tools 
                WHERE org_id = ? AND page_id = ?
            ")->execute([$orgId, $pageIdStr]);

            // ✅ Delete the page and its groups
            $this->conn->prepare("
                DELETE FROM group_location_map 
                WHERE org_id = ? AND page_id = ?
            ")->execute([$orgId, $pageIdStr]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Delete page failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get first page ID as INTEGER (for compatibility with existing code)
     */
    public function getFirstPageId(string $orgId): ?int {
        $stmt = $this->conn->prepare("
            SELECT page_id FROM group_location_map 
            WHERE org_id = ? 
            ORDER BY page_id::INTEGER 
            LIMIT 1
        ");
        $stmt->execute([$orgId]);
        $result = $stmt->fetchColumn();
        return $result ? (int)$result : null;
    }

    /**
     * Get page name by page ID (treats page_id as STRING in query)
     */
    public function getPageName(int $pageId, string $orgId): ?string {
        $pageIdStr = (string) $pageId;
        $stmt = $this->conn->prepare("
            SELECT page_name 
            FROM group_location_map 
            WHERE org_id = ? AND page_id = ?
            LIMIT 1
        ");
        $stmt->execute([$orgId, $pageIdStr]);
        return $stmt->fetchColumn() ?: null;
    }
}