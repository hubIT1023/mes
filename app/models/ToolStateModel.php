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
     * Update tool_state and handle PROD / NON-PROD transitions
     */
    public function updateToolState(array $data): void
    {
        try {
            // Ensure atomic updates
            $this->conn->beginTransaction();

            // =====================================================
            // 1️⃣ MAIN STATE UPDATE (always)
            // =====================================================
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
            $stmt->execute([
                'org_id'        => $data['org_id'],        // UUID
                'group_code'    => $data['group_code'],    // VARCHAR
                'location_code' => $data['location_code'], // VARCHAR
                'col_1'         => $data['col_1'],         // asset_id
                'col_2'         => $data['col_2'],         // entity
                'col_3'         => $data['col_3'],         // stopcause
                'col_4'         => $data['col_4'],         // reason
                'col_5'         => $data['col_5'],         // action
                'col_6'         => $data['col_6'],         // datetime
                'col_8'         => $data['col_8'],         // person_reported
            ]);

            // =====================================================
            // 2️⃣ NON-PROD → capture start metadata
            // =====================================================
            if ($data['col_3'] !== 'PROD') {
                $nprodSql = "
                    UPDATE tool_state
                    SET
                        col_7  = col_6,  -- timestamp started
                        col_9  = col_8,  -- person_start
                        col_10 = col_3   -- stopcause_start
                    WHERE org_id = :org_id
                      AND col_1  = :col_1
                ";

                $npStmt = $this->conn->prepare($nprodSql);
                $npStmt->execute([
                    'org_id' => $data['org_id'],
                    'col_1'  => $data['col_1'],
                ]);
            }

            // =====================================================
            // 3️⃣ PROD → UPSERT into machine_log
            // =====================================================
            if ($data['col_3'] === 'PROD') {
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
                    ON CONFLICT (org_id, col_1)
                    DO UPDATE SET
                        group_code    = EXCLUDED.group_code,
                        location_code = EXCLUDED.location_code,
                        col_2         = EXCLUDED.col_2,
                        col_3         = EXCLUDED.col_3,
                        col_4         = EXCLUDED.col_4,
                        col_5         = EXCLUDED.col_5,
                        col_6         = EXCLUDED.col_6,
                        col_7         = EXCLUDED.col_7,
                        col_8         = EXCLUDED.col_8,
                        col_9         = EXCLUDED.col_9,
                        col_10        = EXCLUDED.col_10,
                        col_11        = EXCLUDED.col_11
                    
                ";

                $histStmt = $this->conn->prepare($historySql);
                $histStmt->execute([
                    'org_id' => $data['org_id'],
                    'col_1'  => $data['col_1'],
                ]);
            }

            $this->conn->commit();

        } catch (PDOException $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Fetch stop-mode choices
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

