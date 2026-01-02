<?php
// app/models/GroupModel.php

require_once __DIR__ . '/../config/Database.php';

class GroupModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Check if a group exists for the given tenant, page, and ID
     */
    public function groupExists(int $groupId, string $orgId, int $pageId): bool
    {
        $stmt = $this->conn->prepare("
            SELECT 1
            FROM group_location_map
            WHERE id = ?
              AND org_id = ?
              AND page_id = ?
            LIMIT 1
        ");
        $stmt->execute([$groupId, $orgId, $pageId]);
        return (bool) $stmt->fetch();
    }

    /**
     * Update group name, location, and sequence
     */
    public function updateGroup(array $data): bool
    {
        $sql = "
            UPDATE group_location_map 
            SET 
                group_name = ?, 
                location_name = ?, 
                seq_id = ?     
            WHERE id = ?
        ";
        $stmt = $this->conn->prepare($sql);
        return (bool) $stmt->execute([
            $data['group_name'],
            $data['location_name'],
            $data['seq_id'],
            $data['id']
        ]);
    }

    /**
     * Delete a group AND all its associated data:
     * - registered_tools (entities)
     * - tool_state (real-time states)
     * Uses the full composite key: (org_id, group_code, location_code)
     */
    public function deleteGroup(int $groupId, string $orgId): bool
    {
        try {
            // 🔍 Fetch group_code and location_code for this group
            $stmt = $this->conn->prepare("
                SELECT group_code, location_code 
                FROM group_location_map 
                WHERE id = ? AND org_id = ?
                LIMIT 1
            ");
            $stmt->execute([$groupId, $orgId]);
            $groupData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$groupData) {
                // Group doesn't exist — idempotent success
                return true;
            }

            $groupCode = (int)$groupData['group_code'];
            $locationCode = (int)$groupData['location_code'];

            // 🧾 Begin transaction for atomicity
            $this->conn->beginTransaction();

            // 🗑️ 1. Delete associated entities
            $stmt = $this->conn->prepare("
                DELETE FROM registered_tools 
                WHERE org_id = ? AND group_code = ? AND location_code = ?
            ");
            $stmt->execute([$orgId, $groupCode, $locationCode]);

            // 🗑️ 2. Delete tool states
            $stmt = $this->conn->prepare("
                DELETE FROM tool_state 
                WHERE org_id = ? AND group_code = ? AND location_code = ?
            ");
            $stmt->execute([$orgId, $groupCode, $locationCode]);

            // 🗑️ 3. Delete the group itself
            $stmt = $this->conn->prepare("
                DELETE FROM group_location_map 
                WHERE id = ? AND org_id = ?
            ");
            $stmt->execute([$groupId, $orgId]);

            // ✅ Commit all changes
            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            // 🔁 Rollback on error
            $this->conn->rollback();
            error_log("GroupModel::deleteGroup() failed for group_id=$groupId, org_id=$orgId: " . $e->getMessage());
            return false;
        }
    }
}