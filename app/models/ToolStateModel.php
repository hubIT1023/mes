<?php
// app/models/ToolStateModel.php

require_once __DIR__ . '/../config/Database.php';

class ToolStateModel
{
    private PDO $conn;
	
	 
 // tool_state Table -for reference
 /*
	org_id          --tenant id
    group_code      -- group
    location_code   -- location id
    col_1  			-- asset_id
    col_2  			-- entity name
    col_3  			-- stopcause (IDLE, PROD, etc.)
    col_4  			-- reason
    col_5  			-- action
    col_6  			-- dateTime_now(from php)
    col_7  			-- timestamp started
    col_8  			-- person_reported
    col_9  			-- person_start
    col_10 			-- stopcause_start
    col_11 			-- status
	
	*/
	

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function updateToolState(array $data): void
    {
        // === Step 1: Main update ===
        $sql = "
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
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);

        // === Step 2: Mode-specific processing ===
        if ($data['col_3'] !== 'PROD') {
            // Non-PROD: set start metadata
            $nprodSql = "
                UPDATE tool_state 
                SET 
                    col_7  = col_6,
                    col_10 = col_3,
                    col_9  = col_8
                WHERE org_id = :org_id
                  AND col_1  = :col_1
            ";
            $npStmt = $this->conn->prepare($nprodSql);
            // Only need org_id and col_1, but passing full $data is safe
            $npStmt->execute([
                'org_id' => $data['org_id'],
                'col_1'  => $data['col_1']
            ]);
        } else {
            // PROD: log to history
            $historySql = "
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
                    col_11
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
                    col_11
                FROM tool_state
                WHERE org_id = :org_id
                  AND col_1  = :col_1
            ";
            $histStmt = $this->conn->prepare($historySql);
            $histStmt->execute([
                'org_id' => $data['org_id'],
                'col_1'  => $data['col_1']
            ]);
        }
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
