<?php
// app/models/ToolStateModel.php

require_once __DIR__ . '/../config/Database.php';

class ToolStateModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function saveToolState(array $data): array {
        if (empty($data['org_id']) || empty($data['col_1'])) {
            return ['success' => false, 'error' => 'Missing org_id or asset_id'];
        }

        $dateTimeNow = date('Y-m-d H:i:s');
        $orgId = $data['org_id'];
        $assetId = $data['col_1'];

        try {
            $this->conn->beginTransaction();

            if ($data['col_3'] !== 'PROD') {
                // ─── NON-PROD: Update state with new start time ─────────────
                $stmt = $this->conn->prepare("
                    UPDATE tool_state 
                    SET 
                        col_3 = ?,          -- new stop cause
                        col_4 = ?,          -- issue
                        col_5 = ?,          -- action
                        col_6 = ?,          -- timestamp started (reset)
                        col_10 = col_3,     -- save previous stop cause
                        col_8 = ?,          -- person reported
                        group_code = ?,
                        location_code = ?
                    WHERE org_id = ? AND col_1 = ?
                ");
                $updated = $stmt->execute([
                    $data['col_3'],
                    $data['col_4'],
                    $data['col_5'],
                    $dateTimeNow,
                    $data['col_8'],
                    $data['group_code'],
                    $data['location_code'],
                    $orgId,
                    $assetId
                ]);

                if (!$updated) {
                    throw new Exception("No record found to update for asset: $assetId");
                }

            } else {
                // ─── PROD: Mark as completed and log to history ─────────────
                // 1. Update col_9 (person completed)
                $stmt = $this->conn->prepare("
                    UPDATE tool_state 
                    SET col_9 = ?
                    WHERE org_id = ? AND col_1 = ?
                ");
                $updated = $stmt->execute([$data['col_9'], $orgId, $assetId]);

                if (!$updated) {
                    throw new Exception("No active state found for asset: $assetId");
                }

                // 2. Insert into machine_log
                // Columns in machine_log (match your spec):
                // col_2, col_4, col_5, col_11, dateStamp, 
                // col_6, col_7, location, col_10, col_8, col_9
                $insert = "
                    INSERT INTO machine_log (
                        col_2, col_4, col_5, col_11, dateStamp,
                        col_6, col_7, location, col_10, col_8, col_9
                    )
                    SELECT 
                        col_2, 
                        col_4, 
                        col_5, 
                        'Completed' AS col_11, 
                        ? AS dateStamp,
                        col_6, 
                        ? AS col_7,          -- end timestamp = now
                        location_code AS location, 
                        col_10, 
                        col_8, 
                        col_9
                    FROM tool_state
                    WHERE org_id = ? AND col_1 = ?
                ";
                $logStmt = $this->conn->prepare($insert);
                $logged = $logStmt->execute([
                    $dateTimeNow,   // dateStamp
                    $dateTimeNow,   // col_7 (end timestamp)
                    $orgId,
                    $assetId
                ]);

                if (!$logged) {
                    throw new Exception("Failed to log to maintWorkLog");
                }
            }

            $this->conn->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("ToolStateModel custom error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("ToolStateModel DB error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database operation failed'];
        }
    }
	
		// select mode_key this is equivalent stopcause
	public function getModeColorChoices(string $orgId): array {
		try {
			$stmt = $this->conn->prepare("
				SELECT mode_key,label
				FROM mode_color
				WHERE org_id = ?
				ORDER BY label
			");
			$stmt->execute([$orgId]);
			return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [mode_key => label]
		} catch (PDOException $e) {
			error_log("Failed to fetch mode_color choices: " . $e->getMessage());
			return [];
		}
	}
}