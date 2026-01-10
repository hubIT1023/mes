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

    // Get current col_8 (person_reported) before PROD update
    public function getCurrentPersonReported(int $orgId, string $assetId): ?string
    {
        $stmt = $this->conn->prepare("
            SELECT col_8 FROM tool_state 
            WHERE org_id = ? AND col_1 = ?
        ");
        $stmt->execute([$orgId, $assetId]);
        return $stmt->fetchColumn() ?: null;
    }

    // For NON-PROD: full update including col_8 = reporter
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
                col_6         = :col_6,
                col_8         = :col_8
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    // For PROD: update col_8 (resolver) and col_9 (original reporter)
    public function updateToolStateForPROD(array $data): void
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
                col_6         = :col_6,
                col_8         = :col_8,      -- new resolver
                col_9         = :col_8_old   -- original reporter
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    public function setDowntimeStart(array $data): void
    {
        $stmt = $this->conn->prepare("
            UPDATE tool_state
            SET
                col_10 = :col_3,
                col_7  = NULL
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        $stmt->execute($data);
    }

    // Log completed downtime (called only on PROD)
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
                col_10,       -- original stop cause
                col_4,
                col_5,
                col_6,        -- downtime start
                NOW(),        -- downtime end
                :col_8_new,   -- resolver (person who ended it)
                :col_8_old,   -- original reporter
                col_10
            FROM tool_state
            WHERE org_id = :org_id
              AND col_1  = :col_1
        ");
        // Pass extra params for the SELECT list
        $stmt->bindValue(':col_8_new', $data['col_8_new']);
        $stmt->bindValue(':col_8_old', $data['col_8_old'] ?? null);
        $stmt->bindValue(':org_id', $data['org_id']);
        $stmt->bindValue(':col_1', $data['col_1']);
        $stmt->execute();
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