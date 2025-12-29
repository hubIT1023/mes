<?php
// app/models/GroupModel0.php → corrected to GroupModel.php

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
     * Delete group and all associated tools (registered_tools)
     */
    public function deleteGroup(int $groupId, string $orgId): bool
    {
        try {
            // 🔍 Step 1: Safely fetch group_code for this group (defensive)
            $stmt = $this->conn->prepare("
                SELECT group_code 
                FROM group_location_map 
                WHERE id = ? AND org_id = ?
                LIMIT 1
            ");
            $stmt->execute([$groupId, $orgId]);
            $groupCode = $stmt->fetchColumn();

            if ($groupCode === false) {
                // Group doesn't exist — treat as successful (idempotent)
                return true;
            }

            // 🗑️ Step 2: Delete associated tools
            $stmt = $this->conn->prepare("
                DELETE FROM registered_tools 
                WHERE org_id = ? AND group_code = ?
            ");
            $stmt->execute([$orgId, $groupCode]);

            // 🗑️ Step 3: Delete the group itself
            $stmt = $this->conn->prepare("
                DELETE FROM group_location_map 
                WHERE id = ? AND org_id = ?
            ");
            $stmt->execute([$groupId, $orgId]);

            return true;
        } catch (PDOException $e) {
            error_log("GroupModel::deleteGroup() failed: " . $e->getMessage());
            return false;
        }
    }
}