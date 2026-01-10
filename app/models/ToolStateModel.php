<?php
// app/models/ToolStateModel.php

require_once __DIR__ . '/../config/Database.php';

class ToolStateModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Full update: used when entering NON-PROD state.
     * Includes col_6 (start time) from modal.
     */
    public function updateToolState(array $data): void
    {
        $stmt = $this->conn->prepare("
            UPDATE tool_state
            SET
                group_code    = :group_code,
                location_code = :location_code,
                col_2         = :col_2,
                col_3         = :col_3,
                col_4         = :col_4,
                col_5         = :col_5,
                col_6         = :col_6,  -- Start time (from modal)
                col_8         = :col_8,
                col_9         = :col_9
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    /**
     * Partial update: used when switching TO PROD.
     * DOES NOT update col_6 — preserves original downtime start time.
     */
    public function updateToolStateForPROD(array $data): void
    {
        $stmt = $this->conn->prepare("
            UPDATE tool_state
            SET
                group_code    = :group_code,
                location_code = :location_code,
                col_2         = :col_2,
                col_3         = :col_3,  -- Now 'PROD'
                col_4         = :col_4,
                col_5         = :col_5,
                -- ⚠️ col_6 is NOT updated — keep original start time
                col_8         = :col_8,
                col_9         = :col_9
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    /**
     * When entering NON-PROD, record downtime start:
     * - col_10 = stop cause (copy of col_3)
     * - col_7  = NOW() → downtime start timestamp (can be used for duration calc)
     */
    public function processToolStateData(array $data): void
    {
        $stmt = $this->conn->prepare("
            UPDATE tool_state
            SET
                col_10 = :col_3,  -- Preserve stop cause
                col_7  = NOW()    -- Server timestamp for downtime start
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    /**
     * Save the CURRENT (non-PROD) state to machine_log as a completed downtime event.
     * Called BEFORE updating to PROD.
     * In machine_log:
     *   - col_6 = original start time (from tool_state)
     *   - col_7 = NOW() → completion time
     */
    public function saveHistoryToMachineLog(array $data): void
    {
        $stmt = $this->conn->prepare("
            INSERT INTO machine_log (
                org_id, group_code, location_code,
                col_1, col_2, col_3, col_4, col_5,
                col_6, col_7, col_8, col_9, col_10
            )
            SELECT
                org_id,
                group_code,
                location_code,
                col_1,
                col_2,
                col_3,
                col_4,
                col_5,
                col_6,          -- Original start time
                NOW(),          -- Completion time (end of downtime)
                col_8,
                col_9,
                col_10
            FROM tool_state
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    /**
     * Fetch mode options (e.g., PROD, IDLE, MAINT) for dropdown
     */
    public function getModeColorChoices(string $orgId): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT mode_key, label
                FROM mode_color
                WHERE org_id = ?
                ORDER BY label
            ");
            $stmt->execute([$orgId]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            error_log("Failed to fetch mode_color choices: " . $e->getMessage());
            return [];
        }
    }
}