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
            SELECT 1 FROM group_location_map 
            WHERE id = ? AND org_id = ? AND page_id = ?
        ");
        $stmt->execute([$groupId, $orgId, $pageId]);
        return (bool) $stmt->fetch();
    }

    public function updateGroup(array $data): bool {
        $sql = "
            UPDATE group_location_map 
            SET 
                group_name = ?, 
                location_name = ?, 
                seq_id = ?     
            WHERE id = ?
        ";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['group_name'],
            $data['location_name'],
            $data['seq_id'],
            $data['id']
        ]);
    } 

    public function deleteGroup(int $groupId, string $orgId): bool {
        try {
            // First delete associated entities
            $stmt = $this->conn->prepare("
                DELETE FROM registered_tools 
                WHERE org_id = ? AND group_code = (
                    SELECT group_code FROM group_location_map WHERE id = ?
                )
            ");
            $stmt->execute([$orgId, $groupId]);

            // Then delete the group
            $stmt = $this->conn->prepare("
                DELETE FROM group_location_map 
                WHERE id = ? AND org_id = ?
            ");
            return $stmt->execute([$groupId, $orgId]);
        } catch (PDOException $e) {
            error_log("Delete group error: " . $e->getMessage());
            return false;
        }
    }
}