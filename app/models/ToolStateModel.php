<?php
// app/models/ToolStateModel.php

require_once __DIR__ . '/../config/Database.php';

class ToolStateModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Upsert current state into tool_state.
     * Creates row if missing, updates if exists.
     */
    public function updateToolState(array $data): void
    {
        $sql = "
            INSERT INTO tool_state (
                org_id,
                group_code,
                location_code,
                col_1,
                col_2,
                col_3,
                col_4,
                col_5,
                col_6,
                col_8
            )
            VALUES (
                :org_id,
                :group_code,
                :location_code,
                :col_1,
                :col_2,
                :col_3,
                :col_4,
                :col_5,
                :col_6,
                :col_8
            )
            ON CONFLICT (org_id, col_1) DO UPDATE SET
                group_code    = EXCLUDED.group_code,
                location_code = EXCLUDED.location_code,
                col_2         = EXCLUDED.col_2,
                col_3         = EXCLUDED.col_3,
                col_4         = EXCLUDED.col_4,
                col_5         = EXCLUDED.col_5,
                col_6         = EXCLUDED.col_6,
                col_8         = EXCLUDED.col_8
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
    }

    /**
     * Save completed downtime event to machine_log.
     * Called ONLY when state changes to 'PROD'.
     */
    public function saveToMachineLog(array $data): void
    {
        // Insert full current state as a historical record
        $sql = "
            INSERT INTO machine_log (
                org_id,
                group_code,
                location_code,
                col_1,
                col_2,
                col_3,
                col_4,
                col_5,
                col_6,
                col_7,
                col_8,
                col_9,
                col_10,
                col_11,
                col_12,
                col_13,
                col_14,
                col_15,
                col_16,
                col_17,
                col_18,
                col_19,
                col_20,
                col_21,
                col_22,
                col_23,
                col_24
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
                col_6,
                col_7,
                col_8,
                col_9,
                col_10,
                col_11,
                col_12,
                col_13,
                col_14,
                col_15,
                col_16,
                col_17,
                col_18,
                col_19,
                col_20,
                col_21,
                col_22,
                col_23,
                col_24
            FROM tool_state
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'org_id' => $data['org_id'],
            'col_1'  => $data['col_1']
        ]);
    }

    /**
     * Fetch mode options for dropdown (e.g., PROD, IDLE, MAINT)
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