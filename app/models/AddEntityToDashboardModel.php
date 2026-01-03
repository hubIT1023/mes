<?php
// app/models/AddEntityToDashboardModel.php

require_once __DIR__ . '/../config/Database.php';

class AddEntityToDashboardModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * ✅ Return page_id as STRING (matches VARCHAR(10) in DB)
     */
    public function getPageIdByGroupCode(int $group_code, string $orgId): ?string {
        try {
            $stmt = $this->conn->prepare("
                SELECT page_id FROM group_location_map 
                WHERE group_code = ? AND org_id = ?
                LIMIT 1
            ");
            $stmt->execute([$group_code, $orgId]);
            $result = $stmt->fetchColumn();
            return $result !== false ? (string) $result : null;
        } catch (PDOException $e) {
            error_log("getPageIdByGroupCode failed: " . $e->getMessage());
            return null;
        }
    }

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
     * ✅ Insert page_id into registered_tools
     */
    public function addEntity(array $data): bool {
        try {
            [$row_pos, $col_pos] = $this->getNextGridPosition(
                $data['org_id'],
                $data['group_code'],
                $data['location_code']
            );

            $stmt = $this->conn->prepare("
                INSERT INTO registered_tools (
                    org_id, page_id, asset_id, entity, 
                    group_code, location_code,
                    row_pos, col_pos, created_at
                ) VALUES (
                    :org_id, :page_id, :asset_id, :entity, 
                    :group_code, :location_code,
                    :row_pos, :col_pos, CURRENT_TIMESTAMP
                )
            ");

            return $stmt->execute([
                'org_id' => $data['org_id'],
                'page_id' => $data['page_id'],        // STRING
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

    private function getNextGridPosition(string $orgId, int $groupCode, int $locationCode): array {
        $stmt = $this->conn->prepare("
            SELECT row_pos, col_pos
            FROM registered_tools
            WHERE org_id = ? AND group_code = ? AND location_code = ?
            ORDER BY row_pos ASC, col_pos ASC
        ");
        $stmt->execute([$orgId, $groupCode, $locationCode]);
        $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($existing)) {
            return [1, 1];
        }

        $last = end($existing);
        $last_row = (int)$last['row_pos'];
        $last_col = (int)$last['col_pos'];

        if ($last_col >= 9) {
            return [$last_row + 1, 1];
        }
        return [$last_row, $last_col + 1];
    }
}