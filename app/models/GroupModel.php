<?php
// app/models/GroupModel.php

require_once __DIR__ . '/../config/Database.php';

class GroupModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * ✅ Check group existence with STRING page_id
     */
    public function groupExists(int $groupId, string $orgId, string $pageId): bool {
        $stmt = $this->conn->prepare("
            SELECT 1 
            FROM group_location_map 
            WHERE id = ? AND org_id = ? AND page_id = ?
            LIMIT 1
        ");
        $stmt->execute([$groupId, $orgId, $pageId]); // page_id as string
        return (bool) $stmt->fetch();
    }

    /**
     * ✅ Delete group + dependent tools
     */
    public function deleteGroup(int $groupId, string $orgId): bool {
        try {
            $this->conn->beginTransaction();

            // ✅ Delete all tools associated with this group
            $this->conn->prepare("
                DELETE FROM registered_tools 
                WHERE group_code IN (
                    SELECT group_code 
                    FROM group_location_map 
                    WHERE id = ? AND org_id = ?
                ) AND org_id = ?
            ")->execute([$groupId, $orgId, $orgId]);

            // ✅ Delete the group itself
            $this->conn->prepare("
                DELETE FROM group_location_map 
                WHERE id = ? AND org_id = ?
            ")->execute([$groupId, $orgId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Delete group failed: " . $e->getMessage());
            return false;
        }
    }
}