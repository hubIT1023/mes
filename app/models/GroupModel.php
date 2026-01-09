<?php
// app/models/GroupModel.php

require_once __DIR__ . '/../config/Database.php';

class GroupModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function groupExists(int $groupId, string $orgId, string $pageId): bool {
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

            // Get exact group identity
            $groupStmt = $this->conn->prepare("
                SELECT group_code, location_code 
                FROM group_location_map 
                WHERE id = ? AND org_id = ?
            ");
            $groupStmt->execute([$groupId, $orgId]);
            $group = $groupStmt->fetch(PDO::FETCH_ASSOC);

            if (!$group) {
                $this->conn->commit();
                return true; // Already deleted
            }

            // Delete tools using full group key
            $this->conn->prepare("
                DELETE FROM registered_tools 
                WHERE org_id = ? 
                  AND group_code = ? 
                  AND location_code = ?
            ")->execute([$orgId, (int)$group['group_code'], (int)$group['location_code']]);

            // Delete group
            $this->conn->prepare("
                DELETE FROM group_location_map 
                WHERE id = ? AND org_id = ?
            ")->execute([$groupId, $orgId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Delete group failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }
	
	public function updateGroup(string $orgId, array $data): bool {
    if (!isset($data['id'], $data['group_name'], $data['location_name'], $data['seq_id'])) {
        return false;
    }

    $groupId = (int)$data['id'];
    $groupName = trim($data['group_name']);
    $locationName = trim($data['location_name']);
    $seqId = (int)$data['seq_id'];

    if (empty($groupName) || empty($locationName) || $groupId <= 0 || $seqId < 1) {
        return false;
    }

    try {
        $stmt = $this->conn->prepare("
            UPDATE group_location_map 
            SET group_name = ?, location_name = ?, seq_id = ?
            WHERE id = ? AND org_id = ?
        ");
        return $stmt->execute([$groupName, $locationName, $seqId, $groupId, $orgId]);
    } catch (Exception $e) {
        error_log("Update group failed: " . $e->getMessage());
        return false;
    }
}
}