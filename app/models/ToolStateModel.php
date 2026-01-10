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

    // Step 1: Update main state (all fields except col_7, col_9, col_10)
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
                col_6         = :col_6,  -- start of current state
                col_8         = :col_8
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    // Step 2a: When entering NON-PROD
    public function setDowntimeStart(array $data): void
    {
        $stmt = $this->conn->prepare("
            UPDATE tool_state
            SET
                col_10 = :col_3,  -- preserve original stop cause
                col_7  = NULL      -- not completed yet
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    // Step 2b: When entering PROD
    public function setProductionCompleted(array $data): void
    {
        $stmt = $this->conn->prepare("
            UPDATE tool_state
            SET
                col_9 = :col_8  -- person_completed = person_reported
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    // ✅ LAST STEP: Only called on PROD — logs COMPLETED downtime
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
                col_3,        -- will be 'PROD' (but we want original stop cause!)
                col_4,
                col_5,
                col_6,        -- downtime start ✅
                NOW(),        -- downtime end ✅
                col_8,        -- person reported
                col_8,        -- person completed (use col_8 since col_9 may not be set yet)
                col_10        -- original stop cause ✅
            FROM tool_state
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

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