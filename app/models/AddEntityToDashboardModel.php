<?php
// app/models/AddEntityToDashboardModel.php

require_once __DIR__ . '/../config/Database.php';

class AddEntityToDashboardModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * ✅ NEW METHOD: Get page_id from group_code
     */
    public function getPageIdByGroupCode(int $group_code, string $orgId): int {
        try {
            $stmt = $this->conn->prepare("
                SELECT page_id FROM group_location_map 
                WHERE group_code = ? AND org_id = ?
            ");
            $stmt->execute([$group_code, $orgId]);
            $result = $stmt->fetchColumn();
            return $result !== false ? (int) $result : 1; // Default to page 1 if not found
        } catch (PDOException $e) {
            error_log("getPageIdByGroupCode failed: " . $e->getMessage());
            return 1; // Safe fallback
        }
    }

    /**
     * Fetch official asset_name from [assets] table
     */
    public function getAssetName(string $orgId, string $assetId): ?string {
        try {
            $stmt = $this->conn->prepare("
                SELECT asset_name FROM assets 
                WHERE tenant_id = ? AND asset_id = ?
            ");
            $stmt->execute([$orgId, $assetId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("getAssetName failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if asset is already in this group/location
     */
    public function isEntityUsed(string $orgId, int $groupCode, int $locationCode, string $assetId): bool {
        try {
            $stmt = $this->conn->prepare("
                SELECT 1 FROM registered_tools 
                WHERE org_id = ? 
                  AND group_code = ? 
                  AND location_code = ? 
                  AND asset_id = ?
            ");
            $stmt->execute([$orgId, $groupCode, $locationCode, $assetId]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("isEntityUsed failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add entity with auto-generated grid position (9 columns per row)
     */
    public function addEntity(array $data): bool {
        try {
            // Get next grid position
            [$row_pos, $col_pos] = $this->getNextGridPosition(
                $data['org_id'],
                $data['group_code'],
                $data['location_code']
            );

            $stmt = $this->conn->prepare("
                INSERT INTO registered_tools (
                    org_id, asset_id, entity, group_code, location_code,
                    row_pos, col_pos, created_at
                ) VALUES (
                    :org_id, :asset_id, :entity, :group_code, :location_code,
                    :row_pos, :col_pos, GETDATE()
                )
            ");

            return $stmt->execute([
                'org_id' => $data['org_id'],
                'asset_id' => $data['asset_id'],
                'entity' => $data['entity'],
                'group_code' => $data['group_code'],
                'location_code' => $data['location_code'],
                'row_pos' => $row_pos,
                'col_pos' => $col_pos
            ]);
        } catch (PDOException $e) {
            error_log("addEntity failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Auto-generate next grid position (max 9 columns per row)
     */
    private function getNextGridPosition(string $orgId, int $groupCode, int $locationCode): array {
        // Fetch all existing entities for this group/location
        $stmt = $this->conn->prepare("
            SELECT row_pos, col_pos
            FROM registered_tools
            WHERE org_id = ? AND group_code = ? AND location_code = ?
            ORDER BY row_pos ASC, col_pos ASC
        ");
        $stmt->execute([$orgId, $groupCode, $locationCode]);
        $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($existing)) {
            return [1, 1]; // First entity → (1,1)
        }

        // Find the last used position
        $last = end($existing);
        $last_row = (int)$last['row_pos'];
        $last_col = (int)$last['col_pos'];

        // If last column is 9, start new row at (last_row + 1, 1)
        if ($last_col >= 9) {
            return [$last_row + 1, 1];
        } else {
            // Else, same row, next column
            return [$last_row, $last_col + 1];
        }
    }
}