<?php
// app/models/GroupModel.php

require_once __DIR__ . '/../config/Database.php';

class GroupModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function groupExists(int $groupId, string $orgId, int $pageId): bool {
        $stmt = $this->conn->prepare("
            SELECT 1 
            FROM group_location_map 
            WHERE id = ? AND org_id = ? AND page_id = ?
            LIMIT 1
        ");
        $stmt->execute([$groupId, $orgId, $pageId]);
        return (bool) $stmt->fetch();
    }

    public function deleteGroup(int $groupId, string $orgId): bool {
        try {
            $this->conn->beginTransaction();

            // Delete the group
            $stmt = $this->conn->prepare("
                DELETE FROM group_location_map 
                WHERE id = ? AND org_id = ?
            ");
            $stmt->execute([$groupId, $orgId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Delete group failed: " . $e->getMessage());
            return false;
        }
    }
}