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

            // --- STEP 1: Ensure a row exists in tool_state ---
            $check = $this->conn->prepare("
                SELECT 1 FROM tool_state 
                WHERE org_id = ? AND col_1 = ?
            ");
            $check->execute([$orgId, $assetId]);
            $exists = $check->fetch();

            if (!$exists) {
                // INSERT new row with minimal defaults
                $insert = $this->conn->prepare("
                    INSERT INTO tool_state (
                        org_id, group_code, location_code, col_1, col_2,
                        col_3, col_4, col_5, col_6, col_7, col_8, col_9,
                        col_10, col_11, col_12, col_13, col_14, col_15, col_16
                    ) VALUES (
                        ?, ?, ?, ?, ?,
                        '', '', '', ?, ?, '', '',
                        '', '', '', '', '', '', ''
                    )
                ");
                $insert->execute([
                    $orgId,
                    $data['group_code'] ?? 0,
                    $data['location_code'] ?? 0,
                    $assetId,
                    $data['col_2'] ?? '',
                    $dateTimeNow,
                    $dateTimeNow
                ]);
            }
			
			// --- STEP 3: If PROD, log to machine_log ---
if ($data['col_3'] === 'PROD') {
    // 🔍 DEBUG: Fetch the exact row that will be logged
    $debugStmt = $this->conn->prepare("
        SELECT 
            org_id, group_code, location_code,
            col_1, col_2, col_3, col_4, col_5,
            col_6, col_7, col_8, col_9,
            col_10
        FROM tool_state
        WHERE org_id = ? AND col_1 = ?
    ");
    $debugStmt->execute([$orgId, $assetId]);
    $debugRow = $debugStmt->fetch(PDO::FETCH_ASSOC);

    // Prepare debug info
    $debugInfo = [
        'timestamp' => $dateTimeNow,
        'input_data' => $data,
        'fetched_row_from_tool_state' => $debugRow,
        'will_insert_to_machine_log' => $debugRow !== false,
        'org_id' => $orgId,
        'asset_id' => $assetId
    ];

    // 💡 Log as JSON (visible in PHP error log)
    error_log("PROD LOG DEBUG: " . json_encode($debugInfo, JSON_PRETTY_PRINT));

    if (!$debugRow) {
        throw new Exception("No row in tool_state to log for PROD mode. org_id=$orgId, asset_id=$assetId");
    }

    // Now insert into machine_log
    $insertLog = "
        INSERT INTO machine_log (
            org_id, group_code, location_code,
            col_1, col_2, col_3, col_4, col_5,
            col_6, col_7, col_8, col_9,
            col_10, col_11, dateStamp
        ) VALUES (
            :org_id, :group_code, :location_code,
            :col_1, :col_2, :col_3, :col_4, :col_5,
            :col_6, :col_7, :col_8, :col_9,
            :col_10, 'Completed', :dateStamp
        )
    ";
    $logStmt = $this->conn->prepare($insertLog);
    $logged = $logStmt->execute([
        'org_id' => $debugRow['org_id'],
        'group_code' => $debugRow['group_code'],
        'location_code' => $debugRow['location_code'],
        'col_1' => $debugRow['col_1'],
        'col_2' => $debugRow['col_2'],
        'col_3' => $debugRow['col_3'],
        'col_4' => $debugRow['col_4'],
        'col_5' => $debugRow['col_5'],
        'col_6' => $debugRow['col_6'],
        'col_7' => $debugRow['col_7'],
        'col_8' => $debugRow['col_8'],
        'col_9' => $debugRow['col_9'],
        'col_10' => $debugRow['col_10'],
        'dateStamp' => $dateTimeNow
    ]);

    if (!$logged) {
        throw new Exception("Failed to insert into machine_log");
    }
}

            // --- STEP 2: Update the row with new data ---
            if ($data['col_3'] !== 'PROD') {
                // NON-PROD: full update + reset start time
                $updateSql = "
                    UPDATE tool_state SET
                        group_code = ?,
                        location_code = ?,
                        col_2 = ?,
                        col_3 = ?,
                        col_4 = ?,
                        col_5 = ?,
                        col_6 = ?,          -- new start time
                        col_10 = col_3,     -- save previous mode
                        col_8 = ?
                    WHERE org_id = ? AND col_1 = ?
                ";
                $params = [
                    $data['group_code'],
                    $data['location_code'],
                    $data['col_2'],
                    $data['col_3'],
                    $data['col_4'],
                    $data['col_5'],
                    $dateTimeNow,
                    $data['col_8'],
                    $orgId,
                    $assetId
                ];
            } else {
                // PROD: update ALL relevant fields, including col_9
                $updateSql = "
                    UPDATE tool_state SET
                        group_code = ?,
                        location_code = ?,
                        col_2 = ?,
                        col_3 = ?,
                        col_4 = ?,
                        col_5 = ?,
                        col_6 = ?,          -- start time (keep or update?)
                        col_7 = ?,          -- end time = now
                        col_8 = ?,
                        col_9 = ?           -- person completed
                    WHERE org_id = ? AND col_1 = ?
                ";
                $params = [
                    $data['group_code'],
                    $data['location_code'],
                    $data['col_2'],
                    $data['col_3'],
                    $data['col_4'],
                    $data['col_5'],
                    $data['col_6'] ?? $dateTimeNow, // or keep existing?
                    $dateTimeNow,                   // col_7 = end time
                    $data['col_8'],
                    $data['col_9'],
                    $orgId,
                    $assetId
                ];
            }

            $stmt = $this->conn->prepare($updateSql);
            $updated = $stmt->execute($params);

            if (!$updated) {
                throw new Exception("Failed to update tool_state for asset: $assetId");
            }

            // --- STEP 3: If PROD, log to machine_log ---
            if ($data['col_3'] === 'PROD') {
                $insertLog = "
                    INSERT INTO machine_log (
                        org_id, group_code, location_code,
                        col_1, col_2, col_3, col_4, col_5,
                        col_6, col_7, col_8, col_9,
                        col_10, col_11, dateStamp
                    )
                    SELECT 
                        org_id, group_code, location_code,
                        col_1, col_2, col_3, col_4, col_5,
                        col_6, col_7, col_8, col_9,
                        col_10, 'Completed', ?
                    FROM tool_state
                    WHERE org_id = ? AND col_1 = ?
                ";
                $logStmt = $this->conn->prepare($insertLog);
                $logged = $logStmt->execute([$dateTimeNow, $orgId, $assetId]);

                if (!$logged) {
                    throw new Exception("Failed to insert into machine_log");
                }
            }

            $this->conn->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("ToolStateModel error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("ToolStateModel DB error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database operation failed'];
        }
    }

    public function getModeColorChoices(string $orgId): array {
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